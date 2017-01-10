<?php
namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;


/**
 * Helper class for imap access
 *
 * Copyright (C) 2016-2017  SSilence
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package    protocols
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */

class ImapClient
{
    /*
     * Use the Secure Socket Layer to encrypt the session
     */
    const ENCRYPT_SSL = 'ssl';
    /*
     * Force use of start-TLS to encrypt the session, and reject connection to servers that do not support it
     */
    const ENCRYPT_TLS = 'tls';
    const CONNECT_ADVANCED = 'connectAdvanced';
    const CONNECT_DEFAULT = 'connectDefault';

    /*
     * Connect status or advanced or default
     *
     * @var string
     */
    public static $connect;

    /*
     * Config for advanced connect
     *
     * @var array
     */
    public static $connectConfig;

    /**
     * Imap connection
     * @var bool
     */
    protected $imap = false;

    /**
     * Mailbox url
     * @var string
     */
    protected $mailbox = "";

    /**
     * Parts for saveEmail()
     *
     * @var bool
     */
    protected $parts = false;

    /**
     * Parts for saveEmail()
     *
     * @var string
     */
    protected $saveFile = "";

    /**
     * Current folder
     * @var string
     */
    protected $folder = "INBOX";

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
     * Initialize imap helper
     *
     * @param string $mailbox
     * @param string $username
     * @param string $password
     * @param string $encryption use ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS
     * @return void
     */
    public function __construct($mailbox = null, $username = null, $password = null, $encryption = null)
    {
        if(isset($mailbox) && is_string($mailbox)){
            $this->setConnectDefault();
        };
        if(isset($mailbox) && is_array($mailbox)){
            $this->setConnectAdvanced();
            $this->setConnectConfig($mailbox);
        };

        if(!isset(self::$connect) || self::$connect === self::CONNECT_DEFAULT){
            $this->connectDefault($mailbox, $username, $password, $encryption);
        };
        if(self::$connect === self::CONNECT_ADVANCED){
            $this->connectAdvanced(self::$connectConfig);
        };
    }

    public static function setConnectAdvanced()
    {
        static::$connect = self::CONNECT_ADVANCED;
    }

    public static function setConnectDefault()
    {
        static::$connect = self::CONNECT_DEFAULT;
    }

    public static function setConnectConfig(array $config)
    {
        static::$connectConfig = $config;
    }

    /*
     * The default connection.
     * Not used a lot of imap connection options.
     * Use only ENCRYPT_SSL and VALIDATE_CERT.
     *
     * If you need a more advanced connection settings,
     * use connectAdvanced() method.
     *
     * @param string $mailbox
     * @param string $username
     * @param string $password
     * @param string $encryption use ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS
     * @return void
     */
    public function connectDefault($mailbox, $username, $password, $encryption = null)
    {
        $connect = new ImapConnect();
        if($encryption === ImapClient::ENCRYPT_SSL){
            $connect->prepareFlags(ImapConnect::SERVICE_IMAP, ImapConnect::ENCRYPT_SSL, ImapConnect::NOVALIDATE_CERT);
        };
        if($encryption === ImapClient::ENCRYPT_TLS){
            $connect->prepareFlags(ImapConnect::SERVICE_IMAP, ImapConnect::ENCRYPT_TLS, ImapConnect::NOVALIDATE_CERT);
        };
        $connect->prepareMailbox($mailbox);
        $connect->connect(null, $username, $password);
        $this->imap = $connect->getImap();
        $this->mailbox = $connect->getMailbox();
    }

    /*
     * Advanced connect
     *
     * @param array $config
     * @return void
     */
    public function connectAdvanced(array $config)
    {
        if(!isset($config['flags'])){$config['flags'] = null;};
        if(!isset($config['mailbox'])){$config['mailbox'] = null;};
        if(!isset($config['connect'])){
            throw new ImapClientException('Option connect must be installed');
        };
        $connect = new ImapConnect();
        $connect->prepareFlags($config['flags']);
        $connect->prepareMailbox($config['mailbox']);
        $connect->connect($config['connect']);
        $this->imap = $connect->getImap();
        $this->mailbox = $connect->getMailbox();
    }

    /**
     * Close connection
     */
    public function __destruct() {
        if (is_resource($this->imap))
        {
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
     * Saves the email into a file
     * Note: If your server does not have alot of RAM, this may break
     *
     * @return bool true on sucesss
     */
     public function saveEmail($file = null, $id = null, $part = null)
     {

         // Null checks
         if($file === null)
         {
             throw new ImapClientException('File must be specified for saveEmail()');
         }
         if($id === null)
         {
             throw new ImapClientException('Email id must be specified for saveEmail()');
         }
         if($part === null)
         {
             $parts = false;
         }
         else {
             $parts = true;
         }
         // Is type checks
         if(!is_string($file))
         {
             throw new ImapClientException('File must be a string for saveEmail()');
         }
         if(!is_int($id))
         {
             throw new ImapClientException('$id must be a integer for saveEmail()');
         }
         $saveFile = fopen($file,'w');
         if($parts)
         {
             imap_savebody($this->imap, $savefile, $id, $part);
         }
         else {
             imap_savebody($this->imap, $savefile, $id);
         }
     }

     /**
      * Saves the email into a file
      * Note: This is safer then saveEmail for slower servers
      *
      * @return bool true on sucesss
      */
      public function saveEmailSafe($file = null, $id = null, $part = null, $streamFilter = 'convert.base64-decode')
      {

          // Null checks
          if($file === null)
          {
              throw new ImapClientException('File must be specified for saveEmailSafe()');
          }
          if($id === null)
          {
              throw new ImapClientException('Email id must be specified for saveEmailSafe()');
          }
          if($part === null)
          {
              $parts = false;
          }
          else {
              $parts = true;
          }
          // Is type checks
          if(!is_string($file))
          {
              throw new ImapClientException('File must be a string for saveEmailSafe()');
          }
          if(!is_int($id))
          {
              throw new ImapClientException('$id must be a integer for saveEmailSafe()');
          }
          $saveFile = fopen($file,'w');
          stream_filter_append($savefile, $streamFilter, STREAM_FILTER_WRITE);
          if($parts)
          {
              imap_savebody($this->imap, $savefile, $id, $part);
          }
          else {
              imap_savebody($this->imap, $savefile, $id);
          }
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
     * Returns all available folders
     *
     * @param string $separator. Default is '.'
     * @param int $type. Has two meanings 0 and 1.
     * If 0 return nested array, if 1 return an array of strings.
     * @return array with folder names
     */
    public function getFolders($separator = null, $type = 0) {
        $folders = imap_list($this->imap, $this->mailbox, "*");
        if($type == 1){
            return str_replace($this->mailbox, "", $folders);
        };
        if($type == 0){
            $array = str_replace($this->mailbox, "", $folders);
            if(!isset($separator)){ $separator = '.'; };
            $outArray = [];
            foreach ($array as $folders) {
                $subFolders = explode($separator, $folders);
                $countSubFolders = count($subFolders);
                if($countSubFolders > 1){
                    $arrMake = $this->makeArrayFolders($subFolders);
                    $kv = each($arrMake);
                    if(!isset($outArray[$kv['key']])){
                        $outArray[$kv['key']] = $kv['value'];
                    }else{
                        $outArray[$kv['key']] = array_merge($outArray[$kv['key']], $kv['value']);
                    };
                }else{
                    $outArray[$subFolders[0]] = [];
                };
            };
            return $outArray;
        };
        return null;
    }

    /**
    * Set embeded or not
    *
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
     * Get Messages by Criteria
     *
     * @see http://php.net/manual/en/function.imap-search.php
     *
     * @param string $criteria ALL, UNSEEN, FLAGGED, UNANSWERED, DELETED, UNDELETED, etc (e.g. FROM "joey smith")
     * @param int    $number
     * @param int    $start
     * @param string $order
     * @param bool   $withbody
     * @param bool   $embed_images
     *
     * @return array
     */
    public function getMessagesByCriteria($criteria = '', $number = 0, $start = 0, $order = 'DESC', $withbody = FALSE, $embed_images = FALSE)
    {
        $emails = array();
        $result = imap_search($this->imap, $criteria);
        if ($number == 0)
        {
            $number = count($result);
        }
        if ($result)
        {
            $ids = array();
            foreach ($result as $k => $i)
            {
                $ids[] = $i;
            }
            $ids = array_chunk($ids, $number);
            $ids = array_slice($ids[0], $start, $number);

            $emails = array();
            foreach ($ids as $id)
            {
                $emails[] = $this->formatMessage($id, $withbody, $embed_images);
            }
        }
        if ($order == 'DESC')
        {
            $emails = array_reverse($emails);
        }

        return $emails;
    }

    /**
     * Get messages
     *
     * @param int    $number       Number of messages. 0 to get all
     * @param int    $start        Starting message number
     * @param string $order        ASC or DESC
     * @param bool   $withbody     Get message body
     * @param bool   $embed_images Get embed images in message body
     *
     * @return array
     */
    public function getMessages($withbody = true, $number = 0, $start = 0, $order = 'DESC', $embed_images = false) {
        if ($number == 0)
        {
            $number = $this->countMessages();
        }
        $emails = array();
        $result = imap_search($this->imap, 'ALL');
        if ($result)
        {
            $ids = array();
            foreach ($result as $k => $i)
            {
                $ids[] = $i;
            }

            if ($order == 'DESC')
            {
                $ids = array_reverse($ids);
            }
            $ids = array_chunk($ids, $number);
            $ids = $ids[$start];

            foreach ($ids as $id)
            {
                $emails[] = $this->formatMessage($id, $withbody, $embed_images);
            }
        }

        return $emails;
    }

    /**
     * Returns one email by given id
     *
     * @param int  $id           Message id
     * @param bool $withbody     False if you want without body
     * @param bool $embed_images If use $withbody TRUE and you want body embed images, set TRUE
     *
     * @return array
     */
    public function getMessage($id, $withbody = true, $embed_images = false) {
        return $this->formatMessage($id, $withbody, $embed_images);
    }

    /**
     * Create the final message array
     *
     * @param int  $id           Message uid
     * @param bool $withbody     Define if the output will get the message body
     * @param bool $embed_images Define if message body will show embeded images
     *
     * @return array
     */
    protected function formatMessage($id, $withbody = true, $embed_images = true) {
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
            'udate'     => $header->udate,
            'subject'   => $subject,
            'priority'  => $priority,
            'id'        => $id,
            'uid'       => $uid,
            'flagged'   => strlen(trim($header->Flagged))>0,
            'unread'    => strlen(trim($header->Unseen))>0,
            'answered'  => strlen(trim($header->Answered))>0,
            'deleted'   => strlen(trim($header->Deleted))>0,
            'size'     => $header->Size,
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
        $attachments = $this->attachments2name($this->getAttachments($id, $mailStruct, ""));
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

        // Modify HTML to embed images inline
        if ((count(@$email['attachments']) > 0) and (@$email['html'] == TRUE) and ($embed_images == TRUE))
        {
            $email['body'] = $this->embedImages($email);
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
     * @return bool success or not
     * @param $ids array of ids
     */
    public function deleteMessages($ids) {
        foreach ($ids as $id) {
            imap_delete($this->imap, $id, FT_UID);
        }
        /*
        if( imap_mail_move($this->imap, implode(",", $ids), $this->getTrash(), CP_UID) == false)
            return false;
        */
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
     * Return content of messages attachment
     * Save the attachment in a optional path or get the binary code in the content index
     *
     * @param int    $id       Message id
     * @param int    $index    Index of the attachment - 0 to the first attachment
     * @param string $tmp_path Optional tmp path, if not set the code will be get in the output
     *
     * @return array|bool False if attachment could not be get
     */
    public function getAttachment($id, $index = 0, $tmp_path = '') {

        $messageIndex = imap_msgno($this->imap, imap_uid($this->imap, $id));
        $mailStruct = imap_fetchstructure($this->imap, $messageIndex);
        $attachments = $this->getAttachments($messageIndex, $mailStruct, "");

        if ($attachments == false) {
            return false;
        }

        // find attachment
        if ($index > count($attachments)) {
            return false;
        }
        $attachment = $attachments[$index];

        // get attachment body
        $message = imap_fetchbody($this->imap, $id, $attachment['partNum']);

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

        $file = array(
            "name"        => $attachment['name'],
            "size"        => $attachment['size'],
            "disposition" => $attachment['disposition'],
            "reference"   => $attachment['reference'],
            "type"        => $attachment['type'],
            "content"     => $message,
        );

        if ($tmp_path != '')
        {
            $file['content'] = $tmp_path . $attachment['name'];
            $fp = fopen($file['content'], "wb");
            fwrite($fp, $message);
            fclose($fp);
        }

        return $file;
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
     * clean trash and spam folder
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
                throw new ImapClientException('Your attachments do not have a name. This should NOT be happening');
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
     * @param string $mimetype Mime Type
     * @param bool|false $structure Structure?
     * @param bool|false $partNumber part number
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
     * @param int    $mailNum The message number
     * @param object $part    Message structure. See imap_fetchstructure()
     * @param string $partNum Message structure section
     *
     * @return array          Array of attachments
     */
    protected function getAttachments($mailNum, $part, $partNum) {
        $attachments = array();

        if (!isset($part->parts)
            && !isset($part->disposition)
            && (!isset($part->subtype) || !in_array($part->subtype, array('JPEG', 'GIF', 'PNG')))
        ) {
            $part->disposition = 'attachment';
        }

        if (isset($part->parts)) {
            foreach ($part->parts as $key => $subpart) {
                if ($partNum != "") {
                    $newPartNum = $partNum . "." . ($key + 1);
                } else {
                    $newPartNum = ($key+1);
                }
                $result = $this->getAttachments($mailNum, $subpart, $newPartNum);
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
                $partStruct = imap_bodystruct($this->imap, $mailNum, $partNum);
                $reference = isset($partStruct->id) ? $partStruct->id : "";
                $attachmentDetails = array();
                $parameters = array();
                if (isset($part->dparameters[0])) {
                    $parameters = $part->dparameters[0];
                } else if ($part->parameters[0]) {
                    $parameters = $part->parameters[0];
                }

                if ($parameters) {
                    $attachmentDetails = array(
                        "name"        => $parameters->value,
                        "partNum"     => $partNum,
                        "enc"         => @$partStruct->encoding,
                        "size"        => $part->bytes,
                        "reference"   => $reference,
                        "disposition" => $part->disposition,
                        "type"        => $part->subtype,
                    );
                }

                return $attachmentDetails;
            }
        } else if (isset($part->subtype) && in_array($part->subtype, array('JPEG', 'GIF', 'PNG'))) {

            $partStruct = imap_bodystruct($this->imap, $mailNum, $partNum);
            $reference = isset($partStruct->id) ? $partStruct->id : "";
            $disposition = empty($reference) ? 'attachment' : 'inline';
            //if ($disposition == "inline") { $this->inline = true; }
            if (isset($part->dparameters[0]->value)){
                $name = $part->dparameters[0]->value;
            } elseif ($part->parameters[0]->value) {
                $name = $part->parameters[0]->value;
            } else {
                $name = "unknown";
            }

            $attachmentDetails = array();
            if (isset($part->dparameters[0]))
            {
                $attachmentDetails = array(
                    "name"        => $name,
                    "partNum"     => $partNum,
                    "enc"         => $partStruct->encoding,
                    "size"        => $part->bytes,
                    "reference"   => $reference,
                    "disposition" => $disposition,
                    "type"        => $part->subtype,
                );
            }

            return $attachmentDetails;
        }
        return $attachments;
    }

    /**
    * Identify encoding by charset attribute in header
    *
    * @param $id
    * @return string
    */
    protected function setEncoding($id)
    {
        $header = imap_fetchstructure($this->imap, $id);
        $params = $header->parameters ?: [];

            foreach ($params as $k => $v) {
                if (stristr($v->attribute, 'charset')) {
                    return $v->value;
                }
            }

        return 'utf-8';
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
            if (strtolower($attachment['disposition']) == 'inline' && !empty($attachment['reference'])){
                $file = $this->getAttachment($email['id'] , $key);

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
        if (imap_unsubscribe($this->imap, $this->mailbox)) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Retrieve the quota level settings, and usage statics per mailbox.
     *
     * @param string $mailbox
     *
     * @return array
     */
    public function getQuota($mailbox)
    {
        $quota = imap_get_quota($this->imap, "user.".$mailbox);
        return $quota;
    }


    /**
     * Retrieve the quota level settings, and usage statics per mailbox.
     *
     * @param string $mailbox
     *
     * @return array
     */
    public function getQuotaRoot($mailbox)
    {
        $quota = imap_get_quotaroot($this->imap, "user.".$mailbox);
        return $quota;
    }

    /**
     * Make an array of strings, a nested array.
     * From
     * ['one', 'two', 'three', 'four', 'five' ...]
     * to
     * ['one'=>['two'=>['three'=>['four'=>['five'=>[...] ]]]]]
     *
     * @param array $subFolders
     * @return array
     */
    protected function makeArrayFolders(array $subFolders)
    {
        $count = count($subFolders);
        $array = $subFolders;
        $out = [];
        for($i = $count; $i >= 2; $i--){
            if(empty($out)){
                $out[$array[$i-2]] = [$array[$i-1]=>[]];
            }else{
                $out[$array[$i-2]] = $out;
                unset($out[$array[$i-1]]);
            };
        };
        return $out;
    }
}
