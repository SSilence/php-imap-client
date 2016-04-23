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
	public function isConnected()
	{
		return $this->imap !== FALSE;
	}


	/**
	 * Returns last imap error
	 *
	 * @return string Error message
	 */
	public function getError()
	{
		return imap_last_error();
	}


	/**
	 * Select given folder
	 *
	 * @param string $folder Folder name
	 * @return bool True if open folder
	 */
	public function selectFolder($folder = '')
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
	public function getFolders()
	{
		$folders = imap_list($this->imap, $this->mailbox, "*");

		return str_replace($this->mailbox, "", $folders);
	}


	/**
	 * Returns the number of messages in the current folder
	 *
	 * @return int Message count
	 */
	public function countMessages()
	{
		return imap_num_msg($this->imap);
	}


	/**
	 * Returns the number of unread messages in the current folder
	 *
	 * @return int Message count
	 */
	public function countUnreadMessages()
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
	public function getUnreadMessages($withbody = TRUE)
	{
		$emails = [];
		$result = imap_search($this->imap, 'UNSEEN');
		if ($result)
		{
			foreach ($result as $k => $i)
			{
				$emails[] = $this->formatMessage($i, $withbody);
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
	public function getMessages($withbody = TRUE)
	{
		$count = $this->countMessages();
		$emails = array();
		for ($i = 1; $i <= $count; $i++)
		{
			$emails[] = $this->formatMessage($i, $withbody);
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
	public function getMessage($id = 0, $withbody = TRUE)
	{
		return $this->formatMessage($id, $withbody);
	}


	/**
	 * Format message output
	 *
	 * @param int  $id       Message id
	 * @param bool $withbody False if you want without body
	 * @return array Formated message
	 */
	protected function formatMessage($id = 0, $withbody = TRUE)
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
		$subject = $this->convertToUtf8($subject);
		$email = array(
			'to'       => isset($header->to) ? $this->arrayToAddress($header->to) : '',
			'from'     => $this->toAddress($header->from[0]),
			'date'     => $header->date,
			'subject'  => $subject,
			'uid'      => $uid,
			'unread'   => strlen(trim($header->Unseen)) > 0,
			'answered' => strlen(trim($header->Answered)) > 0
		);
		if (isset($header->cc))
		{
			$email['cc'] = $this->arrayToAddress($header->cc);
		}

		// get email body
		if ($withbody === TRUE)
		{
			$body = $this->getBody($uid);
			$email['body'] = $body['body'];
			$email['html'] = $body['html'];
		}

		// get attachments
		$mailStruct = imap_fetchstructure($this->imap, $id);
		$attachments = $this->attachments2name($this->getAttachments($this->imap, $id, $mailStruct, ''));
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
						$t = $this->convertToUtf8($decodedName[0]->text);
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
	public function deleteMessage($id = 0)
	{
		return $this->deleteMessages(array($id));
	}


	/**
	 * Delete messages
	 *
	 * @param array $ids Array of ids
	 * @return bool True on success
	 */
	public function deleteMessages($ids = array())
	{
		if (imap_mail_move($this->imap, implode(",", $ids), $this->getTrash(), CP_UID) == FALSE)
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
	public function moveMessage($id = 0, $target = '')
	{
		return $this->moveMessages(array($id), $target);
	}


	/**
	 * Move given messages in new folder
	 *
	 * @param array  $ids    Messages ids
	 * @param string $target New folder
	 * @return bool True on success
	 */
	public function moveMessages($ids = array(), $target = '')
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
	public function setUnseenMessage($id = 0, $seen = TRUE)
	{
		$header = $this->getMessageHeader($id);
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
	public function getAttachment($id = 0, $index = 0, $tmp_path = '')
	{
		// find message
		$messageIndex = imap_msgno($this->imap, imap_uid($this->imap, $id));
		//$header = imap_headerinfo($this->imap, $messageIndex);
		$mailStruct = imap_fetchstructure($this->imap, $messageIndex);
		$attachments = $this->getAttachments($this->imap, $messageIndex, $mailStruct, '');

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
	public function addFolder($name = '')
	{
		return imap_createmailbox($this->imap, $this->mailbox . $name);
	}


	/**
	 * Remove folder
	 *
	 * @param string $name Folder name
	 * @return bool True on success
	 */
	public function removeFolder($name = '')
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
	public function renameFolder($name = '', $newname = '')
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
		if ($this->folder == $this->getTrash() || strtolower($this->folder) == "spam")
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
			if (imap_mail_move($this->imap, '1:*', $this->getTrash()) == FALSE)
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
	public function getAllEmailAddresses()
	{
		$saveCurrentFolder = $this->folder;
		$emails = array();
		foreach ($this->getFolders() as $folder)
		{
			$this->selectFolder($folder);
			foreach ($this->getMessages(FALSE) as $message)
			{
				$emails[] = $message['from'];
				$emails = array_merge($emails, $message['to']);
				if (isset($message['cc']))
				{
					$emails = array_merge($emails, $message['cc']);
				}
			}
		}
		$this->selectFolder($saveCurrentFolder);

		return array_unique($emails);
	}


	/**
	 * Save email in sent
	 *
	 * @param string $header Message header
	 * @param string $body   Message body
	 * @return bool True on success
	 */
	public function saveMessageInSent($header = '', $body = '')
	{
		return imap_append($this->imap, $this->mailbox . $this->getSent(), $header . "\r\n" . $body . "\r\n", "\\Seen");
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
	private function getTrash()
	{
		foreach ($this->getFolders() as $folder)
		{
			if (strtolower($folder) === "trash" || strtolower($folder) === "papierkorb")
			{
				return $folder;
			}
		}

		// no trash folder found? create one
		$this->addFolder('Trash');

		return 'Trash';
	}


	/**
	 * Get sent folder name or create new sent folder
	 *
	 * @return string Sent folder name
	 */
	private function getSent()
	{
		foreach ($this->getFolders() as $folder)
		{
			if (strtolower($folder) === "sent" || strtolower($folder) === "gesendet")
			{
				return $folder;
			}
		}

		// no sent folder found? create one
		$this->addFolder('Sent');

		return 'Sent';
	}


	/**
	 * Fetch header by message id
	 *
	 * @param int $id Message id
	 * @return bool|object Message header on success
	 */
	private function getMessageHeader($id = 0)
	{
		$count = $this->countMessages();
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
	private function attachments2name($attachments = array())
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
	private function toAddress($headerinfos = array())
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

		$name = $this->convertToUtf8($name);

		return $name . " <" . $email . ">";
	}


	/**
	 * Converts imap given array of addresses in strings
	 *
	 * @param array $addresses Imap given addresses as array
	 * @return array With strings (e.g. ["Name <username@domain.tld>", "Name2 <username2@domain.tld>"]
	 */
	private function arrayToAddress($addresses = array())
	{
		$addressesAsString = array();
		foreach ($addresses as $address)
		{
			$addressesAsString[] = $this->toAddress($address);
		}

		return $addressesAsString;
	}


	/**
	 * Returns body of the email. First search for html version of the email, then the plain part
	 *
	 * @param int $uid Message id
	 * @return array Body and html
	 */
	private function getBody($uid = 0)
	{
		$body = $this->get_part($this->imap, $uid, "TEXT/HTML");
		$html = TRUE;
		// if HTML body is empty, try getting text body
		if ($body == "")
		{
			$body = $this->get_part($this->imap, $uid, "TEXT/PLAIN");
			$html = FALSE;
		}
		$body = $this->convertToUtf8($body);

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
	function convertToUtf8($str = '')
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
	private function getAttachments($imap, $mailNum, $part, $partNum = '')
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
				$result = $this->getAttachments($imap, $mailNum, $subpart, $newPartNum);
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
