<?php

/**
 * Helper class for imap access
 *
 * @package    protocols
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class Imap {

	/**
	 * imap connection
	 */
	private $imap = FALSE;

	/**
	 * mailbox url string
	 */
	private $mailbox = "";

	/**
	 * currentfolder
	 */
	private $folder = "Inbox";


	/**
	 * Imap constructor
	 * Initialize connection
	 *
	 * @param string $mailbox    Server name
	 * @param string $username   Account username
	 * @param string $password   Account password
	 * @param string $encryption Use ssl or tls
	 */
	public function __construct($mailbox = '', $username = '', $password = '', $encryption = '')
	{
		$enc = '';
		if ($encryption != NULL && isset($encryption) && $encryption == 'ssl')
		{
			$enc = '/imap/ssl/novalidate-cert';
		}
		else if ($encryption != NULL && isset($encryption) && $encryption == 'tls')
		{
			$enc = '/imap/tls/novalidate-cert';
		}
		$this->mailbox = "{" . $mailbox . $enc . "}";
		$this->imap = @imap_open($this->mailbox, $username, $password);
	}


	/**
	 * Close connection
	 */
	public function __destruct()
	{
		if ($this->imap !== FALSE)
		{
			imap_close($this->imap);
		}
	}


	/**
	 * Returns true after successfull connection
	 *
	 * @return bool True on success
	 */
	public function is_connected()
	{
		return $this->imap !== FALSE;
	}


	/**
	 * Returns last imap error
	 *
	 * @return string Error message
	 */
	public function get_error()
	{
		return imap_last_error();
	}


	/**
	 * Select given folder
	 *
	 * @param string $folder Folder name
	 * @return bool True if open folder
	 */
	public function select_folder($folder = '')
	{
		$result = imap_reopen($this->imap, $this->mailbox . $folder);
		if ($result === TRUE)
		{
			$this->folder = $folder;
		}

		return $result;
	}


	/**
	 * Returns all available folders
	 *
	 * @return array Folder names
	 */
	public function get_folders()
	{
		$folders = imap_list($this->imap, $this->mailbox, "*");

		return str_replace($this->mailbox, "", $folders);
	}


	/**
	 * Returns the number of messages in the current folder
	 *
	 * @return int Message count
	 */
	public function count_messages()
	{
		return imap_num_msg($this->imap);
	}


	/**
	 * Returns the number of unread messages in the current folder
	 *
	 * @return int Message count
	 */
	public function count_unread_messages()
	{
		$result = imap_search($this->imap, 'UNSEEN');
		if ($result === FALSE)
		{
			return 0;
		}

		return count($result);
	}


	/**
	 * Returns unseen emails in the current folder
	 *
	 * @param bool $withbody False if you want without body
	 * @return array Messages
	 */
	public function get_unread_messages($withbody = TRUE)
	{
		$emails = [];
		$result = imap_search($this->imap, 'UNSEEN');
		if ($result)
		{
			foreach ($result as $k => $i)
			{
				$emails[] = $this->format_message($i, $withbody);
			}
		}

		return $emails;
	}


	/**
	 * Returns all emails in the current folder
	 *
	 * @param bool $withbody False if you want without body
	 * @return array Messages
	 */
	public function get_messages($withbody = TRUE)
	{
		$count = $this->count_messages();
		$emails = array();
		for ($i = 1; $i <= $count; $i++)
		{
			$emails[] = $this->format_message($i, $withbody);
		}

		// sort emails descending by date
		// usort($emails, function($a, $b) {
		// try {
		// $datea = new \DateTime($a['date']);
		// $dateb = new \DateTime($b['date']);
		// } catch(\Exception $e) {
		// return 0;
		// }
		// if ($datea == $dateb)
		// return 0;
		// return $datea < $dateb ? 1 : -1;
		// });

		return $emails;
	}


	/**
	 * Returns one email by given id
	 *
	 * @param int  $id       Message id
	 * @param bool $withbody False if you want without body
	 * @return array Messages
	 */
	public function get_message($id = 0, $withbody = TRUE)
	{
		return $this->format_message($id, $withbody);
	}


	/**
	 * Format message output
	 *
	 * @param int  $id       Message id
	 * @param bool $withbody False if you want without body
	 * @return array Formated message
	 */
	protected function format_message($id = 0, $withbody = TRUE)
	{
		$header = imap_headerinfo($this->imap, $id);

		// fetch unique uid
		$uid = imap_uid($this->imap, $id);

		// get email data
		$subject = '';
		if (isset($header->subject) && strlen($header->subject) > 0)
		{
			foreach (imap_mime_header_decode($header->subject) as $obj)
			{
				$subject .= $obj->text;
			}
		}
		$subject = $this->convert_to_utf8($subject);
		$email = array(
			'to'       => isset($header->to) ? $this->array_to_address($header->to) : '',
			'from'     => $this->to_address($header->from[0]),
			'date'     => $header->date,
			'subject'  => $subject,
			'uid'      => $uid,
			'unread'   => strlen(trim($header->Unseen)) > 0,
			'answered' => strlen(trim($header->Answered)) > 0
		);
		if (isset($header->cc))
		{
			$email['cc'] = $this->array_to_address($header->cc);
		}

		// get email body
		if ($withbody === TRUE)
		{
			$body = $this->get_body($uid);
			$email['body'] = $body['body'];
			$email['html'] = $body['html'];
		}

		// get attachments
		$mailStruct = imap_fetchstructure($this->imap, $id);
		$attachments = $this->attachments_to_name($this->get_attachments($this->imap, $id, $mailStruct, ''));
		if (count($attachments) > 0)
		{
			foreach ($attachments as $val)
			{
				$arr = array();
				foreach ($val as $k => $t)
				{
					if ($k == 'name')
					{
						$decodedName = imap_mime_header_decode($t);
						$t = $this->convert_to_utf8($decodedName[0]->text);
					}
					$arr[$k] = $t;
				}
				$email['attachments'][] = $arr;
			}
		}

		return $email;
	}


	/**
	 * Delete given message
	 *
	 * @param int $id Message id
	 * @return bool True on success
	 */
	public function delete_message($id = 0)
	{
		return $this->delete_messages(array($id));
	}


	/**
	 * Delete messages
	 *
	 * @param array $ids Array of ids
	 * @return bool True on success
	 */
	public function delete_messages($ids = array())
	{
		if (imap_mail_move($this->imap, implode(",", $ids), $this->get_trash(), CP_UID) == FALSE)
		{
			return FALSE;
		}

		return imap_expunge($this->imap);
	}


	/**
	 * Move given message in new folder
	 *
	 * @param int    $id     Message id
	 * @param string $target New folder
	 * @return bool True on success
	 */
	public function move_message($id = 0, $target = '')
	{
		return $this->move_messages(array($id), $target);
	}


	/**
	 * Move given messages in new folder
	 *
	 * @param array  $ids    Messages ids
	 * @param string $target New folder
	 * @return bool True on success
	 */
	public function move_messages($ids = array(), $target = '')
	{
		if (imap_mail_move($this->imap, implode(",", $ids), $target, CP_UID) === FALSE)
		{
			return FALSE;
		}

		return imap_expunge($this->imap);
	}


	/**
	 * Mark message as read or unread
	 *
	 * @param int  $id   Message id
	 * @param bool $seen True if message is read, false if message is unread
	 * @return bool True on success
	 */
	public function set_unseen_message($id = 0, $seen = TRUE)
	{
		$header = $this->get_message_header($id);
		if ($header == FALSE)
		{
			return FALSE;
		}

		$flags = "";
		$flags .= (strlen(trim($header->Answered)) > 0 ? "\\Answered " : '');
		$flags .= (strlen(trim($header->Flagged)) > 0 ? "\\Flagged " : '');
		$flags .= (strlen(trim($header->Deleted)) > 0 ? "\\Deleted " : '');
		$flags .= (strlen(trim($header->Draft)) > 0 ? "\\Draft " : '');

		$flags .= (($seen == TRUE) ? '\\Seen ' : ' ');
		//echo "\n<br />".$id.": ".$flags;
		imap_clearflag_full($this->imap, $id, '\\Seen', ST_UID);

		return imap_setflag_full($this->imap, $id, trim($flags), ST_UID);
	}


	/**
	 * Return content of messages attachment
	 * Save the attachment in a optional path or get the binary code in the content index
	 *
	 * @param int    $id       Message id
	 * @param int    $index    Index of the attachment - 0 to the first attachment
	 * @param string $tmp_path Optional tmp path, if not set the code will be get in the output
	 * @return array|bool False if attachement could not be get
	 */
	public function get_attachment($id = 0, $index = 0, $tmp_path = '')
	{
		// find message
		$messageIndex = imap_msgno($this->imap, imap_uid($this->imap, $id));
		//$header = imap_headerinfo($this->imap, $messageIndex);
		$mailStruct = imap_fetchstructure($this->imap, $messageIndex);
		$attachments = $this->get_attachments($this->imap, $messageIndex, $mailStruct, '');

		if ($attachments == FALSE)
		{
			return FALSE;
		}

		// find attachment
		if ($index > count($attachments))
		{
			return FALSE;
		}

		$attachment = $attachments[$index];

		// get attachment body
		$partStruct = imap_bodystruct($this->imap, $messageIndex, $attachment['partNum']);

		$filename = $partStruct->dparameters[0]->value;

		$message = imap_fetchbody($this->imap, $id, $attachment['partNum']);

		switch ($attachment['enc'])
		{
			case 0:
			case 1:
				$message = imap_8bit($message);
				break;
			case 2:
				$message = imap_binary($message);
				break;
			case 3:
				$message = imap_base64($message);
				break;
			case 4:
				$message = quoted_printable_decode($message);
				break;
		}

		$file = array(
			"name" => $filename,
			"size" => $attachment['size'],
		);

		if ($tmp_path != '')
		{
			$file['content'] = $tmp_path . $filename;
			$fp = fopen($file['content'], "wb");
			fwrite($fp, $message);
			fclose($fp);
		}
		else
		{
			$file['content'] = $message;
		}

		return $file;
	}


	/**
	 * Add new folder
	 *
	 * @param string $name Folder name
	 * @return bool True on success
	 */
	public function add_folder($name = '')
	{
		return imap_createmailbox($this->imap, $this->mailbox . $name);
	}


	/**
	 * Remove folder
	 *
	 * @param string $name Folder name
	 * @return bool True on success
	 */
	public function remove_folder($name = '')
	{
		return imap_deletemailbox($this->imap, $this->mailbox . $name);
	}


	/**
	 * Rename folder
	 *
	 * @param string $name    Current Folder name
	 * @param string $newname New Folder name
	 * @return bool True on success
	 */
	public function rename_folder($name = '', $newname = '')
	{
		return imap_renamemailbox($this->imap, $this->mailbox . $name, $this->mailbox . $newname);
	}


	/**
	 * Clean folder content of selected folder
	 *
	 * @return bool True on success
	 */
	public function purge()
	{
		// delete trash and spam
		if ($this->folder == $this->get_trash() || strtolower($this->folder) == "spam")
		{
			if (imap_delete($this->imap, '1:*') === FALSE)
			{
				return FALSE;
			}

			return imap_expunge($this->imap);

			// move others to trash
		}
		else
		{
			if (imap_mail_move($this->imap, '1:*', $this->get_trash()) == FALSE)
			{
				return FALSE;
			}


			return imap_expunge($this->imap);
		}
	}


	/**
	 * Returns all email addresses
	 *
	 * @return array|bool Array with all email addresses or false on error
	 */
	public function get_all_email_addresses()
	{
		$saveCurrentFolder = $this->folder;
		$emails = array();
		foreach ($this->get_folders() as $folder)
		{
			$this->select_folder($folder);
			foreach ($this->get_messages(FALSE) as $message)
			{
				$emails[] = $message['from'];
				$emails = array_merge($emails, $message['to']);
				if (isset($message['cc']))
				{
					$emails = array_merge($emails, $message['cc']);
				}
			}
		}
		$this->select_folder($saveCurrentFolder);

		return array_unique($emails);
	}


	/**
	 * Save email in sent
	 *
	 * @param string $header Message header
	 * @param string $body   Message body
	 * @return bool True on success
	 */
	public function save_message_in_sent($header = '', $body = '')
	{
		return imap_append($this->imap, $this->mailbox . $this->get_sent(), $header . "\r\n" . $body . "\r\n", "\\Seen");
	}


	/**
	 * Explicitly close imap connection
	 */
	public function close()
	{
		if ($this->imap !== FALSE)
		{
			imap_close($this->imap);
		}
	}



	// private helpers


	/**
	 * Get trash folder name or create new trash folder
	 *
	 * @return string Trash folder name
	 */
	private function get_trash()
	{
		foreach ($this->get_folders() as $folder)
		{
			if (strtolower($folder) === "trash" || strtolower($folder) === "papierkorb")
			{
				return $folder;
			}
		}

		// no trash folder found? create one
		$this->add_folder('Trash');

		return 'Trash';
	}


	/**
	 * Get sent folder name or create new sent folder
	 *
	 * @return string Sent folder name
	 */
	private function get_sent()
	{
		foreach ($this->get_folders() as $folder)
		{
			if (strtolower($folder) === "sent" || strtolower($folder) === "gesendet")
			{
				return $folder;
			}
		}

		// no sent folder found? create one
		$this->add_folder('Sent');

		return 'Sent';
	}


	/**
	 * Fetch header by message id
	 *
	 * @param int $id Message id
	 * @return bool|object Message header on success
	 */
	private function get_message_header($id = 0)
	{
		$count = $this->count_messages();
		for ($i = 1; $i <= $count; $i++)
		{
			$uid = imap_uid($this->imap, $i);
			if ($uid == $id)
			{
				$header = imap_headerinfo($this->imap, $i);

				return $header;
			}
		}

		return FALSE;
	}


	/**
	 * Convert attachment in array
	 *
	 * @param array $attachments Attachment with name and size
	 * @return array Name and size of the attachement
	 */
	private function attachments_to_name($attachments = array())
	{
		$names = array();
		foreach ($attachments as $attachment)
		{
			$names[] = array(
				'name' => $attachment['name'],
				'size' => $attachment['size']
			);
		}

		return $names;
	}


	/**
	 * Convert imap given address in string
	 *
	 * @param array $headerinfos The infos given by imap
	 * @return string In format "Name <username@domain.tld>"
	 */
	private function to_address($headerinfos = array())
	{
		$email = "";

		if (isset($headerinfos->mailbox) && isset($headerinfos->host))
		{
			$email = $headerinfos->mailbox . "@" . $headerinfos->host;
		}

		if ( ! empty($headerinfos->personal))
		{
			$name = imap_mime_header_decode($headerinfos->personal);
			$name = $name[0]->text;
		}
		else
		{
			$name = $email;
		}

		$name = $this->convert_to_utf8($name);

		return $name . " <" . $email . ">";
	}


	/**
	 * Converts imap given array of addresses in strings
	 *
	 * @param array $addresses Imap given addresses as array
	 * @return array With strings (e.g. ["Name <username@domain.tld>", "Name2 <username2@domain.tld>"]
	 */
	private function array_to_address($addresses = array())
	{
		$addressesAsString = array();
		foreach ($addresses as $address)
		{
			$addressesAsString[] = $this->to_address($address);
		}

		return $addressesAsString;
	}


	/**
	 * Returns body of the email. First search for html version of the email, then the plain part
	 *
	 * @param int $uid Message id
	 * @return array Body and html
	 */
	private function get_body($uid = 0)
	{
		$body = $this->get_part($this->imap, $uid, "TEXT/HTML");
		$html = TRUE;
		// if HTML body is empty, try getting text body
		if ($body == "")
		{
			$body = $this->get_part($this->imap, $uid, "TEXT/PLAIN");
			$html = FALSE;
		}
		$body = $this->convert_to_utf8($body);

		return array(
			'body' => $body,
			'html' => $html
		);
	}


	/**
	 * Convert to utf8 if necessary
	 *
	 * @param string $str Utf8 encoded string
	 * @return string The converted string or false
	 */
	function convert_to_utf8($str = '')
	{
		if (mb_detect_encoding($str, "UTF-8, ISO-8859-1, GBK") != "UTF-8")
		{
			$str = utf8_encode($str);
		}
		$str = iconv('UTF-8', 'UTF-8//IGNORE', $str);

		return $str;
	}


	/**
	 * Returns a part with a given mimetype
	 * Taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
	 *
	 * @param resource $imap       Imap stream
	 * @param int      $uid        Message id
	 * @param string   $mimetype   Mime type
	 * @param bool     $structure  Object structure
	 * @param bool     $partNumber Part number
	 * @return bool|string Formated string on success
	 */
	private function get_part($imap, $uid = 0, $mimetype = '', $structure = FALSE, $partNumber = FALSE)
	{
		if ( ! $structure)
		{
			$structure = imap_fetchstructure($imap, $uid, FT_UID);
		}
		if ($structure)
		{
			if ($mimetype == $this->get_mime_type($structure))
			{
				if ( ! $partNumber)
				{
					$partNumber = 1;
				}
				$text = imap_fetchbody($imap, $uid, $partNumber, FT_UID | FT_PEEK);
				switch ($structure->encoding)
				{
					case 3:
						return imap_base64($text);
					case 4:
						return imap_qprint($text);
					default:
						return $text;
				}
			}

			// multipart
			if ($structure->type == 1)
			{
				foreach ($structure->parts as $index => $subStruct)
				{
					$prefix = "";
					if ($partNumber)
					{
						$prefix = $partNumber . ".";
					}
					$data = $this->get_part($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
					if ($data)
					{
						return $data;
					}
				}
			}
		}

		return FALSE;
	}


	/**
	 * Extract mimetype
	 * Taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
	 *
	 * @param object $structure
	 * @return string Mime type
	 */
	private function get_mime_type($structure)
	{
		$primaryMimetype = array(
			"TEXT",
			"MULTIPART",
			"MESSAGE",
			"APPLICATION",
			"AUDIO",
			"IMAGE",
			"VIDEO",
			"OTHER"
		);

		if ($structure->subtype)
		{
			return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
		}

		return "TEXT/PLAIN";
	}


	/**
	 * Get attachments of given email
	 * Taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
	 *
	 * @param resource $imap    Imap stream
	 * @param int      $mailNum Message number
	 * @param object   $part    Imap fetch structure
	 * @param string   $partNum Part number
	 * @return array Array of attachments
	 */
	private function get_attachments($imap, $mailNum, $part, $partNum = '')
	{
		$attachments = array();

		if (isset($part->parts))
		{
			foreach ($part->parts as $key => $subpart)
			{
				if ($partNum != "")
				{
					$newPartNum = $partNum . "." . ($key + 1);
				}
				else
				{
					$newPartNum = ($key + 1);
				}
				$result = $this->get_attachments($imap, $mailNum, $subpart, $newPartNum);
				if (count($result) != 0)
				{
					array_push($attachments, $result);
				}
			}
		}
		else if (isset($part->disposition))
		{
			if (strtolower($part->disposition) == "attachment")
			{
				$partStruct = imap_bodystruct($imap, $mailNum, $partNum);
				$attachmentDetails = array(
					"name"    => $part->dparameters[0]->value,
					"partNum" => $partNum,
					"enc"     => $partStruct->encoding,
					"size"    => $part->bytes
				);

				return $attachmentDetails;
			}
		}

		return $attachments;
	}

}
