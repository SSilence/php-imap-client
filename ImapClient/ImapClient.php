<?php

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;

/**
 * Helper class for imap access
 *
 * @package    protocols
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    Apache2.0 (https://www.apache.org/licenses/LICENSE-2.0)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class ImapClient {

    /**
     * imap connection
     * @var bool
     */
    protected $imap = false;

    /**
     * mailbox url
     * @var string
     */
    protected $mailbox = "";

    /**
     * currentfolder
     * @var string
     */
    protected $folder = "Inbox";

    /**
     * Have inline files
     * @var bool
     */
    protected $inline = false;

    /**
     * Images Embed in HTML
     * @var bool
     */
    protected $embed = false;


    /**
     * initialize imap helper
     *
     * @param string $mailbox imap_open string
     * @param string $username
     * @param string $password
     * @param bool|false $encryption SSL or TLS
     */
    public function __construct($mailbox, $username, $password, $encryption = false, $ignoreinvalidvert = false) {
        if (!function_exists('imap_open')) {
            throw new ImapClientException('Imap function not available');
        };
        $enc = '';
        if($ignoreinvalidvert != null && $ignoreinvalidvert = true) {
            $enc = '/novalidate-cert';
        }
        if ($encryption != null && isset($encryption) && $encryption == 'ssl') {
            $enc = '/imap/ssl/novalidate-cert';
        }
        else if ($encryption != null && isset($encryption) && $encryption == 'tls') {
            $enc = '/imap/tls/novalidate-cert';
        }
        $this->mailbox = "{" . $mailbox . $enc . "}";
        $this->imap = @imap_open($this->mailbox, $username, $password);
        if ($this->imap === false) {
            throw new ImapClientException('Failed to connect to: '.$mailbox);
        };
    }


    /**
     * close connection
     */
    public function __destruct() {
        if ($this->imap!==false) {
            imap_close($this->imap);
        }
    }


    /**
     * returns true after successfull connection
     *
     * @return bool true on success
     */
    public function isConnected() {
        return $this->imap !== false;
    }


    /**
     * returns last imap error
     *
     * @return string error message
     */
    public function getError() {
        return imap_last_error();
    }


    /**
     * select given folder
     *
     * @param string $folder name
     * @return bool successfull opened folder
     */
    public function selectFolder($folder) {
        $result = imap_reopen($this->imap, $this->mailbox . $folder);
        if ($result === true) {
            $this->folder = $folder;
        }
        return $result;
    }


    /**
     * returns all available folders
     *
     * @return array with foldernames
     */
    public function getFolders() {
        $folders = imap_list($this->imap, $this->mailbox, "*");
        return str_replace($this->mailbox, "", $folders);
    }


    /**
     * @param bool|false $val
     */
    public function setEmbed($val = false) {
        $this->embed = boolval($val);
    }


    /**
     * returns the number of messages in the current folder
     *
     * @return int message count
     */
    public function countMessages() {
        return imap_num_msg($this->imap);
    }


    /**
     * returns the number of unread messages in the current folder
     *
     * @return int message count
     */
    public function countUnreadMessages() {
        $result = imap_search($this->imap, 'UNSEEN');
        if ($result === false) {
            return 0;
        }
        return count($result);
    }


    /**
     * returns unseen emails in the current folder
     *
     * @return array messages
     * @param bool|true $withbody without body
     * @param string|UNSEEN set what will be used to find unread emails
     */
    public function getUnreadMessages($withbody = true, $standard = "UNSEEN") {
        $emails = array();
        $result = imap_search($this->imap, $standard);
        if ($result) {
            foreach($result as $k=>$i) {
                $emails[]= $this->formatMessage($i, $withbody);
            }
        }
        return $emails;
    }


    /**
     * returns all emails in the current folder
     *
     * @return array messages
     * @param bool|true $withbody without body
     * @return array
     */
    public function getMessages($withbody = true) {
        $count = $this->countMessages();
        $emails = array();
        for ($i=1;$i<=$count;$i++) {
            $emails[]= $this->formatMessage($i, $withbody);
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
     * returns email by given id
     *
     * @return array messages
     * @param int $id
     * @param bool|true $withbody without body
     */
    public function getMessage($id, $withbody = true) {
        return $this->formatMessage($id, $withbody);
    }


    /**
     * @param int $id
     * @param bool $withbody
     * @return array
     */
    protected function formatMessage($id, $withbody=true) {
        $header = imap_headerinfo($this->imap, $id);

        // fetch unique uid
        $uid = imap_uid($this->imap, $id);

        // Check Priority
        preg_match('/X-Priority: ([\d])/mi', imap_fetchheader($this->imap, $id), $matches);
        $priority = isset($matches[1]) ? $matches[1] : 3;


        // get email data
        $subject = '';
        if ( isset($header->subject) && strlen($header->subject) > 0 ) {
            foreach (imap_mime_header_decode($header->subject) as $obj) {
                $subject .= $obj->text;
            }
        }
        $subject = $this->convertToUtf8($subject);
        $email = array(
            'to'        => isset($header->to) ? $this->arrayToAddress($header->to) : '',
            'from'      => $this->toAddress($header->from[0]),
            'date'      => $header->date,
            'subject'   => $subject,
            'priority'  => $priority,
            'uid'       => $uid,
            'flagged'   => strlen(trim($header->Flagged))>0,
            'unread'    => strlen(trim($header->Unseen))>0,
            'answered'  => strlen(trim($header->Answered))>0,
            'deleted'   => strlen(trim($header->Deleted))>0
        );

        if (isset($header->cc)) {
            $email['cc'] = $this->arrayToAddress($header->cc);
        }

        // get email body
        if ($withbody === true) {
            $body = $this->getBody($uid);
            $email['body'] = $body['body'];
            $email['html'] = $body['html'];
        }

        // get attachments
        $mailStruct = imap_fetchstructure($this->imap, $id);
        $attachments = $this->attachments2name($this->getAttachments($this->imap, $id, $mailStruct, ""));
        $email['inline'] = $this->inline;

        if (count($attachments)>0) {
            foreach ($attachments as $val) {
                $arr = array();
                foreach ($val as $k => $t) {
                    if ($k == 'name') {
                        $decodedName = imap_mime_header_decode($t);
                        $t = $this->convertToUtf8($decodedName[0]->text);
                    }
                    $arr[$k] = $t;
                }
                $email['attachments'][] = $arr;
            }
        }

        // Modify HTML to embed images inline
        if ($this->embed == true && $this->inline == true && $email['html'] == true) {
            $email['body_embed'] = $this->embedImages($email);
        }

        return $email;
    }


    /**
     * delete given message
     *
     * @param int $id of the message
     * @return bool success or not
     */
    public function deleteMessage($id) {
        return $this->deleteMessages(array($id));
    }


    /**
     * delete messages
     *
     * @param array $ids array of ids
     * @return bool success or not
     */
    public function deleteMessages($ids) {
        if ( imap_mail_move($this->imap, implode(",", $ids), $this->getTrash(), CP_UID) == false) {
            return false;
        }
        return imap_expunge($this->imap);
    }


    /**
     * move given message in new folder
     *
     * @param int $id of the message
     * @param string $target new folder
     * @return bool success or not
     */
    public function moveMessage($id, $target) {
        return $this->moveMessages(array($id), $target);
    }


    /**
     * move given message in new folder
     *
     * @param array $ids array of message ids
     * @param string $target new folder
     * @return bool success or not
     */
    public function moveMessages($ids, $target) {
        if (imap_mail_move($this->imap, implode(",", $ids), $target, CP_UID) === false)
            return false;
        return imap_expunge($this->imap);
    }


    /**
     * mark message as read
     *
     * @param int $id of the message
     * @param bool|true $seen true = message is read, false = message is unread
     * @return bool success or not
     */
    public function setUnseenMessage($id, $seen = true) {
        $header = $this->getMessageHeader($id);
        if ($header == false) {
            return false;
        }

        $flags = "";
        $flags .= (strlen(trim($header->Answered))>0 ? "\\Answered " : '');
        $flags .= (strlen(trim($header->Flagged))>0 ? "\\Flagged " : '');
        $flags .= (strlen(trim($header->Deleted))>0 ? "\\Deleted " : '');
        $flags .= (strlen(trim($header->Draft))>0 ? "\\Draft " : '');
        $flags .= (($seen == true) ? '\\Seen ' : ' ');

        //echo "\n<br />".$id.": ".$flags;
        imap_clearflag_full($this->imap, $id, '\\Seen', ST_UID);
        return imap_setflag_full($this->imap, $id, trim($flags), ST_UID);
    }


    /**
     * return content of messages attachment
     *
     * @param int $id of the message
     * @param int $index of the attachment (default: first attachment)
     * @return binary attachment
     */
    public function getAttachment($id, $index = 0) {
        // find message
        $attachments = false;
        $messageIndex = imap_msgno($this->imap, $id);
        $header = imap_headerinfo($this->imap, $messageIndex); // @TODO this is not used
        $mailStruct = imap_fetchstructure($this->imap, $messageIndex);
        $attachments = $this->getAttachments($this->imap, $messageIndex, $mailStruct, "");

        if ($attachments == false) {
            return false;
        }

        // find attachment
        if ($index > count($attachments)) {
            return false;
        }
        $attachment = $attachments[$index];

        // get attachment body
        $partStruct = imap_bodystruct($this->imap, imap_msgno($this->imap, $id), $attachment['partNum']);
        $filename = $partStruct->dparameters[0]->value; // @TODO this is not used
        $message = imap_fetchbody($this->imap, $id, $attachment['partNum'], FT_UID);

        switch ($attachment['enc']) {
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

        return array(
            "name"          => $attachment['name'],
            "size"          => $attachment['size'],
            "disposition"   => $attachment['disposition'],
            "reference"     => $attachment['reference'],
            "type"          => $attachment['type'],
            "content"       => $message
        );
    }


    /**
     * add new folder
     *
     * @param string $name of the folder
     * @param bool|false $subscribe immediately subscribe to folder
     * @return bool success or not
     */
    public function addFolder($name, $subscribe = false) {
        $success = imap_createmailbox($this->imap, $this->mailbox . $name);

        if ($success && $subscribe) {
            $success = imap_subscribe($this->imap, $this->mailbox . $name);
        }

        return $success;
    }


    /**
     * remove folder
     *
     * @param string $name of the folder
     * @return bool success or not
     */
    public function removeFolder($name) {
        return imap_deletemailbox($this->imap, $this->mailbox . $name);
    }


    /**
     * rename folder
     *
     * @param string $name of the folder
     * @param string $newname of the folder
     * @return bool success or not
     */
    public function renameFolder($name, $newname) {
        return imap_renamemailbox($this->imap, $this->mailbox . $name, $this->mailbox . $newname);
    }


    /**
     * clean folder content of selected folder
     *
     * @return bool success or not
     */
    public function purge() {
        // delete trash and spam
        if ($this->folder==$this->getTrash() || strtolower($this->folder)=="spam") {
            if (imap_delete($this->imap,'1:*') === false) {
                return false;
            }
            return imap_expunge($this->imap);

            // move others to trash
        } else {
            if (imap_mail_move($this->imap,'1:*', $this->getTrash()) == false) {
                return false;
            }
            return imap_expunge($this->imap);
        }
    }


    /**
     * returns all email addresses
     *
     * @return array with all email addresses or false on error
     */
    public function getAllEmailAddresses() {
        $saveCurrentFolder = $this->folder;
        $emails = array();
        foreach($this->getFolders() as $folder) {
            $this->selectFolder($folder);
            foreach($this->getMessages(false) as $message) {
                $emails[] = $message['from'];
                $emails = array_merge($emails, $message['to']);
                if (isset($message['cc'])) {
                    $emails = array_merge($emails, $message['cc']);
                }
            }
        }
        $this->selectFolder($saveCurrentFolder);
        return array_unique($emails);
    }


    /**
     * save email in sent
     *
     * @param string $header
     * @param string $body
     * @return bool
     */
    public function saveMessageInSent($header, $body) {
        return imap_append($this->imap, $this->mailbox . $this->getSent(), $header . "\r\n" . $body . "\r\n", "\\Seen");
    }


    /**
     * explicitly close imap connection
     */
    public function close() {
        if ($this->imap !== false) {
            imap_close($this->imap);
        }
    }



    // protected helpers


    /**
     * get trash folder name or create new trash folder
     *
     * @return string trash folder name
     */
    protected function getTrash() {

        foreach ($this->getFolders() as $folder) {
            if (in_array(strtolower($folder), array('trash', 'inbox.trash', 'papierkorb'))) {
                return $folder;
            }
        }

        // no trash folder found? create one
        $this->addFolder('Trash');
        return 'Trash';
    }


    /**
     * get sent folder name or create new sent folder
     *
     * @return string sent folder name
     */
    protected function getSent() {
        foreach ($this->getFolders() as $folder) {
            if (in_array(strtolower($folder), array('sent', 'gesendet', 'inbox.gesendet'))) {
                return $folder;
            }
        }

        // no sent folder found? create one
        $this->addFolder('Sent');
        return 'Sent';
    }


    /**
     * fetch message by id
     *
     * @param int $id of the message
     * @return false|object header
     */
    protected function getMessageHeader($id) {
        $count = $this->countMessages();
        for ($i=1;$i<=$count;$i++) {
            $uid = imap_uid($this->imap, $i);
            if ($uid==$id) {
                $header = imap_headerinfo($this->imap, $i);
                return $header;
            }
        }
        return false;
    }


    /**
     * convert attachment in array(name => ..., size => ...).
     *
     * @param array $attachments with name and size
     * @return array
     */
    protected function attachments2name($attachments) {
        $names = array();
        foreach ($attachments as $attachment) {
            if (isset($attachment[0]['name'])) {
                $names[] = array(
                    'name' => $attachment[0]['name'],
                    'size' => $attachment[0]['size'],
                    "disposition" => $attachment['disposition'],
                    "reference" => $attachment['reference']
                );
            } else {
                $names[] = array(
                    'name' => $attachment['name'],
                    'size' => $attachment['size'],
                    "disposition" => $attachment['disposition'],
                    "reference" => $attachment['reference']
                );
            }
        }
        return $names;
    }


    /**
     * convert imap given address in string
     *
     * @param object $headerinfos the infos given by imap
     * @return string in format "Name <email@bla.de>"
     */
    protected function toAddress($headerinfos) {
        $email = "";
        $name = "";
        if (isset($headerinfos->mailbox) && isset($headerinfos->host)) {
            $email = $headerinfos->mailbox . "@" . $headerinfos->host;
        }

        if (!empty($headerinfos->personal)) {
            $name = imap_mime_header_decode($headerinfos->personal);
            $name = $name[0]->text;
        } else {
            $name = $email;
        }

        $name = $this->convertToUtf8($name);

        return $name . " <" . $email . ">";
    }


    /**
     * converts imap given array of addresses in strings
     *
     * @param $addresses imap given addresses as array
     * @return array with strings (e.g. ["Name <email@bla.de>", "Name2 <email2@bla.de>"]
     */
    protected function arrayToAddress($addresses) {
        $addressesAsString = array();
        foreach ($addresses as $address) {
            $addressesAsString[] = $this->toAddress($address);
        }
        return $addressesAsString;
    }


    /**
     * returns body of the email. First search for html version of the email, then the plain part.
     *
     * @param int $uid message id
     * @return string email body
     */
    protected function getBody($uid) {
        $body = $this->get_part($this->imap, $uid, "TEXT/HTML");
        $html = true;
        // if HTML body is empty, try getting text body
        if ($body == "") {
            $body = $this->get_part($this->imap, $uid, "TEXT/PLAIN");
            $html = false;
        }
        $body = $this->convertToUtf8($body);
        return array( 'body' => $body, 'html' => $html);
    }


    /**
     * convert to utf8 if necessary.
     *
     * @param string $str utf8 encoded string
     * @return bool
     */
    public function convertToUtf8($str) {
        if (mb_detect_encoding($str, "UTF-8, ISO-8859-1, GBK")!="UTF-8") {
            $str = utf8_encode($str);
        }
        $str = iconv('UTF-8', 'UTF-8//IGNORE', $str);
        return $str;
    }


    /**
     * returns a part with a given mimetype
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @param false|resource $imap imap stream
     * @param int $uid id
     * @param string $mimetype
     * @param bool|false $structure
     * @param bool|false $partNumber
     * @return bool|string email body
     */
    protected function get_part($imap, $uid, $mimetype, $structure = false, $partNumber = false) {
        if (!$structure) {
            $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == $this->get_mime_type($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($imap, $uid, $partNumber, FT_UID | FT_PEEK);
                switch ($structure->encoding) {
                    case 3: return imap_base64($text);
                    case 4: return imap_qprint($text);
                    default: return $text;
                }
            }

            // multipart
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    $data = $this->get_part($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }


    /**
     * extract mimetype
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @param object $structure
     * @return string mimetype
     */
    protected function get_mime_type($structure) {
        $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

        if ($structure->subtype) {
            return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }


    /**
     * get attachments of given email
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @param false|resource $imap stream
     * @param int $mailNum email
     * @param object $part
     * @param string $partNum
     * @return array of attachments
     */
    protected function getAttachments($imap, $mailNum, $part, $partNum) {
        $attachments = array();

        if (isset($part->parts)) {
            foreach ($part->parts as $key => $subpart) {
                if ($partNum != "") {
                    $newPartNum = $partNum . "." . ($key + 1);
                } else {
                    $newPartNum = ($key+1);
                }
                $result = $this->getAttachments($imap, $mailNum, $subpart, $newPartNum);
                if (count($result) != 0) {
                    if (isset($result[0]['name'])) {
                        foreach($result as $inline) {
                            array_push($attachments, $inline);
                        }
                    } else {
                        array_push($attachments, $result);
                    }
                }
            }
        } else if (isset($part->disposition)) {
            if (in_array(strtolower($part->disposition), array('attachment', 'inline'))) {
                $partStruct = imap_bodystruct($imap, $mailNum, $partNum);
                $reference = isset($partStruct->id) ? $partStruct->id : "";
                if (strtolower($part->disposition) == 'inline') {
                    $this->inline = true;
                }

                $attachmentDetails = array(
                    "name"          => $part->parameters[0]->value,
                    "partNum"       => $partNum,
                    "enc"           => $partStruct->encoding,
                    "size"          => $part->bytes,
                    "reference"     => $reference,
                    "disposition"   => $part->disposition,
                    "type"          => $part->subtype
                );
                return $attachmentDetails;
            }
        } else if (isset($part->subtype) && in_array($part->subtype, array('JPEG', 'GIF', 'PNG'))) {

            $partStruct = imap_bodystruct($imap, $mailNum, $partNum);
            $reference = isset($partStruct->id) ? $partStruct->id : "";
            $disposition = empty($reference) ? 'attachment' : 'inline';
            if ($disposition == "inline") { $this->inline = true; }
            if (isset($part->dparameters[0]->value)){
                $name = $part->dparameters[0]->value;
            } elseif ($part->parameters[0]->value) {
                $name = $part->parameters[0]->value;
            } else {
                $name = "unknown";
            }

            $attachmentDetails = array(
                "name"          => $name,
                "partNum"       => $partNum,
                "enc"           => $partStruct->encoding,
                "size"          => $part->bytes,
                "reference"     => $reference,
                "disposition"   => $disposition,
                "type"          => $part->subtype
            );
            return $attachmentDetails;
        }
        return $attachments;
    }


    /**
     * HTML embed inline images
     *
     * @param array $email
     * @return string
     */
    protected function embedImages($email) {

        $html_embed = $email['body'];

        foreach ($email['attachments'] as $key => $attachment) {
            if ($attachment['disposition'] == 'inline' && !empty($attachment['reference'])){
                $file = $this->getAttachment($email['uid'] , $key);

                $reference = str_replace(array("<", ">"), "", $attachment['reference']);
                $img_embed = "data:image/" . strtolower($file['type']) . ";base64," . base64_encode($file['content']);

                $html_embed = str_replace("cid:" . $reference, $img_embed, $html_embed);
            }
        }
        return $html_embed;
    }


    /**
     * Return general mailbox statistics
     *
     * @return bool|resource object
     */
    public function getMailboxStatistics() {
        return $this->isConnected() ? imap_mailboxmsginfo($this->imap) : false ;
    }

    /**
    * Unsubscribe from a mail box
    * @return bool
    */
    public function unSubscribe()
    {
        if (imap_unsubscribe($this->iamp, $this->mailbox)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
    * Retrieve the quota level settings, and usage statics per mailbox.
    * @return array
    */
    public function getQuota($user)
    {
        $quota = imap_get_quota($this->mailbox, "user.".$user);
        return $quota;
    }

    /**
    * Retrieve the quota level settings, and usage statics per mailbox.
    * @return array
    */
    public function getQuotaRoot($user)
    {
        $quota = imap_get_quotaroot($this->mailbox, "user.".$user);
        return $quota;
    }
}
