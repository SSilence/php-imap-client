<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/***
 * !!!WARNING!!!
 * Hello, just a quick tip,
 * this is the original code for php-imap-client
 * this code is very old and really REALLY should
 * not be used. Whatever floats your boat.
 * I warned you...
 * !!!WARNING!!!
 */
namespace SSilence\ImapClient;

/**
 * Class ImapClient is helper class for imap access
 *
 * @package    protocols
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 * @deprecated 2.0 Old one class example
 */
class ImapClientArchive
{
    /**
     * Use the Secure Socket Layer to encrypt the session
     */
    const ENCRYPT_SSL = 'ssl';
    /**
     * Force use of start-TLS to encrypt the session, and reject connection to servers that do not support it
     */
    const ENCRYPT_TLS = 'tls';
    const CONNECT_ADVANCED = 'connectAdvanced';
    const CONNECT_DEFAULT = 'connectDefault';

    /**
     * Connect status or advanced or default
     *
     * @var string
     */
    public static $connect;

    /**
     * Config for advanced connect
     *
     * @var array
     */
    public static $connectConfig;

    /**
     * Incoming message
     *
     * @var IncomingMessage
     */
    public $incomingMessage;

    /**
     * Imap connection
     *
     * @var resource ImapConnect
     */
    protected $imap;

    /**
     * Mailbox url
     *
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
     *
     * @var string
     */
    protected $folder = "INBOX";

    /**
     * Have inline files
     *
     * @var bool
     */
    protected $inline = false;

    /**
     * Images Embed in HTML
     *
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

    /**
     * Set connection to advanced
     *
     * @return void
     */
    public static function setConnectAdvanced()
    {
        static::$connect = self::CONNECT_ADVANCED;
    }

	/**
	 * Set connection to default
	 *
	 * @return void
	 */
    public static function setConnectDefault()
    {
        static::$connect = self::CONNECT_DEFAULT;
    }

    /**
     * Set connection config
     *
     * @param array $config
     * @return void
     */
    public static function setConnectConfig(array $config)
    {
        static::$connectConfig = $config;
    }

    /**
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

    /**
     * Advanced connect
     *
     * @param array $config
     * @return void
     * @throws ImapClientException
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
     *
     * Also called during garbage collection
     *
     * @return void
     */
    public function __destruct() {
        if (is_resource($this->imap))
        {
            imap_close($this->imap);
        }
    }

    /**
     * Returns true after successful connection
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
     * @param string $file
     * @param integer $id
     * @param string $part
     * @return bool true on success
     * @throws ImapClientException
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
             imap_savebody($this->imap, $saveFile, $id, $part);
         }
         else {
             imap_savebody($this->imap, $saveFile, $id);
         }
         fclose($saveFile);
     }

     /**
      * Saves the email into a file
      * Note: This is safer then saveEmail for slower servers
      *
      * @param string $file
      * @param integer $id
      * @param string $part
      * @param string $streamFilter
      * @return bool true on success
      * @throws ImapClientException
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
          stream_filter_append($saveFile, $streamFilter, STREAM_FILTER_WRITE);
          if($parts)
          {
              imap_savebody($this->imap, $saveFile, $id, $part);
          }
          else {
              imap_savebody($this->imap, $saveFile, $id);
          };
          fclose($saveFile);
      }

    /**
     * Returns the last imap error
     *
     * @return string error message
     */
    public function getError() {
        return imap_last_error();
    }

    /**
     * Select the given folder folder
     * and set $this->folder
     *
     * @param string $folder name
     * @return bool successful opened folder
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
     * @param int $type. Has three meanings 0,1,2.
     * If 0 return nested array, if 1 return an array of strings, if 2 return raw imap_list()
     * @return array with folder names
     */
    public function getFolders($separator = null, $type = 0)
    {
        if(preg_match( '/^{.+}/', $this->mailbox, $matches)){
            $mailbox = $matches[0];
        }else{
            $mailbox = $this->mailbox;
        };

        $folders = imap_list($this->imap, $mailbox, "*");

        if ($type == 2) {
            return $folders;
        };
        if ($type == 1) {
            return str_replace($mailbox, "", $folders);
        };
        if ($type == 0) {
            $arrayRaw = str_replace($mailbox, "", $folders);
            if (!isset($separator)) {
                $separator = '.';
            };
            $arrayNew = [];
            foreach ($arrayRaw as $string) {
                $array = explode($separator, $string);
                $count = count($array);
                $count = $count-1;
                $cache = false;
                for($i=$count; $i>=0; $i--){
                    if($i == $count){
                        $cache = [$array[$i]=>[]];
                    }else{
                        $cache = [$array[$i] => $cache];
                    };
                };
                $arrayNew = array_merge_recursive($arrayNew, $cache);
            };
            return $arrayNew;
        }
        return null;
    }

    /**
     * Set embeded or not
     *
     * @param bool $val
     */
    public function setEmbed($val = false) {
        $this->embed = boolval($val);
    }

    /**
     * Returns the number of messages in the current folder
     *
     * @return int message count
     */
    public function countMessages() {
        return imap_num_msg($this->imap);
    }

    /**
     * Returns an array of brief information about each message in the current mailbox.
     *
     * Structure return array arrays
     * $array = [
     *     [ 'id'=>4, 'info'=>'brief info' ]
     *     [ 'id'=>5, 'info'=>'brief info' ]
     * ]
     *
     * @return array
     */
    public function getBriefInfoMessages()
    {
        $array = imap_headers($this->imap);
        $newArray = [];
        foreach ($array as $key => $string) {
            $newArray[] = ['id'=>$key+1, 'info' => $string];
        };
        return $newArray;
    }

    /**
     * Returns the number of unread messages in the current folder
     *
     * @return integer Message count
     */
    public function countUnreadMessages() {
        $result = imap_search($this->imap, 'UNSEEN');
        if ($result === false) {
            return 0;
        }
        return count($result);
    }

    /**
     * Returns unseen emails in the current folder
     *
     * @param bool $read Mark message like SEEN or no.
     * @return array objects IncomingMessage
     * @throws ImapClientException
     */
    public function getUnreadMessages($read = true) {
        $emails = [];
        $result = imap_search($this->imap, 'UNSEEN');
        if(!$result){
            throw new ImapClientException('No read messages were found.');
        };
        $ids = ''; $countId = count($result);
        foreach($result as $key=>$id) {
            $emails[]= $this->getMessage($id);
            if(($countId-1) == $key){
                $ids .= $id;
            }else{
                $ids .= $id.',';
            };
        }
        /* Set flag UNSEEN */
        if(!$read){
            $this->setUnseenMessage($ids);
        };
        return $emails;
    }

    /**
     * Get Messages by Criteria
     *
     * @see http://php.net/manual/en/function.imap-search.php
     * @param string $criteria ALL, UNSEEN, FLAGGED, UNANSWERED, DELETED, UNDELETED, etc (e.g. FROM "joey smith")
     * @param int    $number
     * @param int    $start
     * @param string $order
     * @return array
     * @throws ImapClientException
     */
    public function getMessagesByCriteria($criteria = '', $number = 0, $start = 0, $order = 'DESC')
    {
        $emails = array();
        $result = imap_search($this->imap, $criteria);
        if(!$result){
            throw new ImapClientException('Messages not found. Or this criteria not supported on your email server.');
        };
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
                $emails[] = $this->getMessage($id);
            }
        }
        if ($order == 'DESC')
        {
            $emails = array_reverse($emails);
        }

        return $emails;
    }

    /**
     * Save Attachments Messages By Subject
     *
     * @param string $subject
     * @param string $dir for save attachments
     * @param string $charset for search
     * @return void
     * @throws ImapClientException
     */
    public function saveAttachmentsMessagesBySubject($subject, $dir = null, $charset = null)
    {
        $criteria = 'SUBJECT "'.$subject.'"';
        $ids = imap_search($this->imap, $criteria, null, $charset);
        if(!$ids){
            throw new ImapClientException('Messages not found. Or this criteria not supported on your email server.');
        };
        foreach ($ids as $id) {
            $this->getMessage($id);
            if(isset($dir)){
                $dir = ['dir'=>$dir];
            };
            $this->saveAttachments($dir);
        };
    }

    /**
     * Get messages
     *
     * @param int    $number       Number of messages. 0 to get all
     * @param int    $start        Starting message number
     * @param string $order        ASC or DESC
     * @return array
     */
    public function getMessages($number = 0, $start = 0, $order = 'DESC') {
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
                $emails[] = $this->getMessage($id);
            }
        }
        return $emails;
    }

    /**
     * Returns one email by given id
     *
     * Examples:
     *
     * 1. Structure
     *  ```php
     *  $imap = new ImapClient();
     *  $imap->getMessage(5);
     *  ```
     *
     *  You can see all structure that
     *  ```php
     *  var_dump($imap->incomingMessage)
     *  ```
     *
     *  But use like this
     *  ```php
     *  $imap->incomingMessage->header->subject
     *  $imap->incomingMessage->header->from
     *  $imap->incomingMessage->header->to
     *  # cc or bcc
     *  $imap->incomingMessage->header->details->cc
     *  $imap->incomingMessage->header->details->bcc
     *  # and other ...
     *  var_dump($imap->incomingMessage->header)
     *  ```
     *
     *  Next Text or Html body
     *  ```php
     *  $imap->incomingMessage->message->html
     *  $imap->incomingMessage->message->plain
     *  # below is array
     *  $imap->incomingMessage->message->info
     *  ```
     *
     *  Array attachments
     *  ```php
     *  $imap->incomingMessage->attachment
     *  ```
     *  Attachment have structure and body
     *  ```php
     *  $imap->incomingMessage->attachment[0]
     *  $imap->incomingMessage->attachment[0]->structure
     *  $imap->incomingMessage->attachment[0]->body
     *  ```
     *
     *  Count section
     *  ```php
     *  $imap->incomingMessage->section
     *  ```
     *
     *  And structure all message
     *  ```php
     *  $imap->incomingMessage->structure
     *  ```
     *
     * 2. Save all attachments
     *  ```php
     *  $imap->getMessage(5);
     *  $imap->saveAttachments();
     *  ```
     * @see IncomingMessage
     * @param integer $id
     * @param string $decode IncomingMessage::DECODE or IncomingMessage::NOT_DECODE
     * @return object IncomingMessage
     */
    public function getMessage($id, $decode = IncomingMessage::DECODE)
    {
        $this->checkMessageId($id);
        $this->incomingMessage = new IncomingMessage($this->imap, $id, $decode);
        return $this->incomingMessage;
    }

    /**
     * Get a section of the message
     *
     * @param integer $id
     * @param string $section
     * @return object
     */
    public function getSection($id, $section)
    {
        $incomingMessage = new IncomingMessage($this->imap, $id);
        return $incomingMessage->getSection($section);
    }

    /**
     * Save attachments one incoming message
     *
     * The allowed types are TypeAttachments
     * You can add your own
     *
     * @param array $options.
     * $options['dir'] it is directory for save attachments.
     * $options['incomingMessage'] it is incomingMessage object
     * @return void
     */
    public function saveAttachments($options = null)
    {
        if(!isset($options['dir'])){
            $dir = __DIR__.DIRECTORY_SEPARATOR;
        }else{
            $dir = $options['dir'];
        };
        if(!isset($options['incomingMessage'])){
            $incomingMessage = $this->incomingMessage;
        }else{
            $incomingMessage = $options['incomingMessage'];
        };
        foreach ($incomingMessage->attachments as $key => $attachment) {
            $newFileName = $attachment->name;
            file_put_contents($dir.DIRECTORY_SEPARATOR.$newFileName, $attachment->body);
        };
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
    /*
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
    */

    /**
     * Delete the given message
     *
     * @param int $id of the message
     * @return bool success or not
     */
    public function deleteMessage($id) {
        return $this->deleteMessages(array($id));
    }

    /**
     * Delete messages
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
     * Move given message in new folder
     *
     * @param int $id of the message
     * @param string $target new folder
     * @return bool success or not
     */
    public function moveMessage($id, $target) {
        return $this->moveMessages(array($id), $target);
    }

    /**
     * Move given message in new folder
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
     * Delete flag message SEEN
     *
     * @param int $ids or string like 1,2,3,4,5 or string like 1:5
     * @return bool
     */
    public function setUnseenMessage($ids)
    {
        return imap_clearflag_full($this->imap, $ids, "\\Seen");
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
    /*
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
    */

    /**
     * Add a new folder
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
     * Remove a folder
     *
     * @param string $name of the folder
     * @return bool success or not
     */
    public function removeFolder($name) {
        return imap_deletemailbox($this->imap, $this->mailbox . $name);
    }

    /**
     * Rename a folder
     *
     * @param string $name of the folder
     * @param string $newname of the folder
     * @return bool success or not
     */
    public function renameFolder($name, $newname) {
        return imap_renamemailbox($this->imap, $this->mailbox . $name, $this->mailbox . $newname);
    }

    /**
     * Clean up trash and spam folder
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
     * Returns all email addresses
     *
     * !!! NEED FIX IT !!!
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
     * Save email in sent
     *
     * @param string $header
     * @param string $body
     * @return bool
     */
    public function saveMessageInSent($header, $body) {
        return imap_append($this->imap, $this->mailbox . $this->getSent(), $header . "\r\n" . $body . "\r\n", "\\Seen");
    }

    /**
     * Explicitly close imap connection
     */
    public function close() {
        if ($this->imap !== false) {
            imap_close($this->imap);
        }
    }

    /**
     * Get trash folder
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
     * Get sent
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
     * Fetch message by id
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
     * Convert attachment in array(name => ..., size => ...).
     *
     * @param array $attachments with name and size
     * @return array
     */
    /*
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
    */

    /**
     * Convert imap given address into string
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
     * Converts imap given array of addresses as strings
     *
     * @param array $addresses imap given addresses as array
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
     * Returns the body of the email. First search for html version of the email, then the plain part.
     *
     * @param int $uid message id
     * @return string email body
     */
    /*
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
   */

    /**
     * Convert to utf8 if necessary.
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
     * Returns a part with a given mimetype
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @param false|resource $imap imap stream
     * @param int $uid id
     * @param string $mimetype Mime Type
     * @param bool|false $structure Structure?
     * @param bool|false $partNumber part number
     * @return bool|string email body
     */
    /*
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
    */

    /**
     * Extract mimetype
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @param object $structure
     * @return string mimetype
     */
    /*
    protected function get_mime_type($structure) {
        $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

        if ($structure->subtype) {
            return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }
    */

    /**
     * Get attachments of given email
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @param int    $mailNum The message number
     * @param object $part    Message structure. See imap_fetchstructure()
     * @param string $partNum Message structure section
     *
     * @return array          Array of attachments
     */
    /*
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
    */

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
     * !!! NEED FIX IT !!!
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
     * Get uid from id
     *
     * @var integer $id
     * @return integer
     */
    public function getUid($id)
    {
        return imap_uid($this->imap, $id);
    }

    /**
     * Get id from uid
     *
     * @var int $uid
     * @return integer
     */
    public function getId($uid)
    {
        return imap_msgno($this->imap, $uid);
    }

	/**
	 * Send an email
	 *
	 * @return void
	 */
    public function sendMail()
    {
        $outMessage = new AdapterForOutgoingMessage(self::$connectConfig);
        $outMessage->send();
    }

    /**
     * Check message id
     *
     * @param integer $id
     * @return void
     * @throws ImapClientException
     */
    private function checkMessageId($id)
    {
        if(!is_int($id)){
            throw new ImapClientException('Bad message number');
        };
        if($id <= 0){
            throw new ImapClientException('Bad message number');
        };
        if($id > $this->countMessages()){
            throw new ImapClientException('Bad message number');
        }
    }
}
