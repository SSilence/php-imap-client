<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * For the full license, please see LICENSE. 
 */

namespace SSilence\ImapClient;

/**
 * Class ImapClient is helper class for imap access.
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class ImapClient
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
     * Current folder
     *
     * @var string
     */
    protected $folder = "INBOX";

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
     * Get the imap connection
     *
     * $return imap
     */
    public function getImapConnection() {
        return $this->imap;   
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
    public function __destruct()
    {
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
    public function isConnected()
    {
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
    public function getError()
    {
        return imap_last_error();
    }

    /**
     * Select the given folder folder
     * and set $this->folder
     *
     * @param string $folder name
     * @return bool successful opened folder
     */
    public function selectFolder($folder)
    {
        $result = imap_reopen($this->imap, $this->mailbox . $folder);
        if ($result === true) {
            $this->folder = $folder;
        }
        return $result;
    }

    /**
     * Returns all available folders
     *
     * @param string $separator default is '.'
     * @param integer $type has three meanings 0,1,2.
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
     * Returns the number of messages in the current folder
     *
     * @return int message count
     */
    public function countMessages()
    {
        return imap_num_msg($this->imap);
    }

    /**
     * Returns an array of brief information about each message in the current mailbox.
     *
     * Returns the following structure of the array of arrays.
     * ```php
     * $array = [
     *     [ 'id'=>4, 'info'=>'brief info' ]
     *     [ 'id'=>5, 'info'=>'brief info' ]
     * ]
     *```
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
     * @return array IncomingMessage of objects
     */
    public function getMessages($number = 0, $start = 0, $order = 'DESC')
    {
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
     * @param array $options have next parameters
     * ```php
     * # it is directory for save attachments
     * $options['dir']
     * # it is incomingMessage object
     * $options['incomingMessage']
     * ```
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
     * Delete the given message
     *
     * @param int $id of the message
     * @return bool success or not
     */
    public function deleteMessage($id)
    {
        return $this->deleteMessages(array($id));
    }

    /**
     * Delete messages
     *
     * @return bool success or not
     * @param $ids array of ids
     */
    public function deleteMessages($ids)
    {
        foreach ($ids as $id) {
            imap_delete($this->imap, $id, FT_UID);
        };
        return imap_expunge($this->imap);
    }

    /**
     * Move given message in new folder
     *
     * @param int $id of the message
     * @param string $target new folder
     * @return bool success or not
     */
    public function moveMessage($id, $target)
    {
        return $this->moveMessages(array($id), $target);
    }

    /**
     * Move given message in new folder
     *
     * @param array $ids array of message ids
     * @param string $target new folder
     * @return bool success or not
     */
    public function moveMessages($ids, $target)
    {
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
        // We need better docs for this
        return imap_clearflag_full($this->imap, $ids, "\\Seen");   
    }

    /**
     * Add a new folder
     *
     * @param string $name of the folder
     * @param bool|false $subscribe immediately subscribe to folder
     * @return bool success or not
     */
    public function addFolder($name, $subscribe = false)
    {
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
    public function removeFolder($name)
    {
        return imap_deletemailbox($this->imap, $this->mailbox . $name);
    }

    /**
     * Rename a folder
     *
     * @param string $name of the folder
     * @param string $newname of the folder
     * @return bool success or not
     */
    public function renameFolder($name, $newname)
    {
        return imap_renamemailbox($this->imap, $this->mailbox . $name, $this->mailbox . $newname);
    }

    /**
     * Clean up trash AND spam folder
     *
     * @return bool success or not
     */
    public function purge()
    {
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
     * Returns all email addresses in all folders
     *
     * If you have a lot of folders and letters, it can take a long time.
     * And mark all the letters as read.
     *
     * @param array|null $options have options:
     * ```php
     * $options['getFolders']['separator']
     * $options['getFolders']['type']
     * $options['mark'] = SEEN and $options['mark'] = UNSEEN
     * ```
     * @return array with all email addresses or false on error
     */
    public function getAllEmailAddresses(array $options = null)
    {
        /* Check Options */
        if(!isset($options['getFolders']['separator'])){
            $options['getFolders']['separator'] = '.';
        };
        if(!isset($options['getFolders']['type'])){
            $options['getFolders']['type'] = 1;
        };
        if(!isset($options['mark'])){
            $options['mark'] = 'SEEN';
        };

        $saveCurrentFolder = $this->folder;
        $emails = array();
        foreach($this->getFolders($options['getFolders']['separator'], $options['getFolders']['type']) as $folder) {
            $this->selectFolder($folder);
            /**
             * @var $message IncomingMessage
             */
            foreach($this->getMessages() as $message) {
                $emails[] = $message->header->from;
                $emails = array_merge($emails, $message->header->to);
                if (isset($message->header->details->cc)) {
                    $emails = array_merge($emails, $message->header->details->cc);
                };
                if(isset($options['mark']) && $options['mark'] == 'UNSEEN'){
                    $this->setUnseenMessage($message->header->msgno);
                };
            }
        }
        $this->selectFolder($saveCurrentFolder);
        return array_unique($emails);
    }

    /**
     * Returns email addresses in the specified folder
     *
     * @param string $folder Specified folder
     * @param array|null $options have option
     * ```php
     * $options['mark'] = SEEN and $options['mark'] = UNSEEN
     * ```
     * @return array addresses
     */
    public function getEmailAddressesInFolder($folder, array $options = null)
    {
        if(!isset($options['mark'])){
            $options['mark'] = 'SEEN';
        };
        $saveCurrentFolder = $this->folder;
        $this->selectFolder($folder);
        $emails = array();
        /**
         * @var $message IncomingMessage
         */
        foreach($this->getMessages() as $message) {
            $emails[] = $message->header->from;
            $emails = array_merge($emails, $message->header->to);
            if (isset($message->header->details->cc)) {
                $emails = array_merge($emails, $message->header->details->cc);
            };
            if(isset($options['mark']) && $options['mark'] == 'UNSEEN'){
                $this->setUnseenMessage($message->header->msgno);
            };
        };
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
    protected function getTrash()
    {
        foreach ($this->getFolders(null, 1) as $folder) {
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
    protected function getSent()
    {
        foreach ($this->getFolders(null, 1) as $folder) {
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
     * @param integer $id of the message
     * @return object|false header
     */
    public function getMessageHeader($id)
    {
        return $this->imapHeaderInfo($id);
    }

    /**
     * Get message overview
     *
     * @see ImapClient::imapFetchOverview()
     * @param integer $id
     * @param null $options
     * @return object
     */
    public function getMessageOverview($id, $options = null)
    {
        $array = $this->imapFetchOverview($id, $options);
        return $array[0];
    }

    /**
     * Get messages overview
     *
     * @param string $id
     * @param null $options
     * @return array
     */
    public function getMessagesOverview($id, $options = null)
    {
        return $this->imapFetchOverview($id, $options);
    }

    /**
     * Wrapper for php imap_fetch_overview()
     *
     * @see http://php.net/manual/ru/function.imap-fetch-overview.php
     * @param string $sequence a message sequence description,
     * you can enumerate desired messages with the X,Y syntax,
     * or retrieve all messages within an interval with the X:Y syntax
     * @param int $options sequence will contain a sequence of message indices or UIDs,
     * if this parameter is set to FT_UID.
     * @return array
     */
    public function imapFetchOverview($sequence, $options = null)
    {
        return imap_fetch_overview($this->imap, $sequence, $options);
    }

    /**
     * Wrapper for php imap_headerinfo()
     *
     * @see http://php.net/manual/ru/function.imap-headerinfo.php
     * @param integer $id
     * @return object|false
     */
    public function imapHeaderInfo($id)
    {
        return imap_headerinfo($this->imap, $id);
    }

    /**
     * Wrapper for imap_fetchstructure()
     *
     * @see http://php.net/manual/ru/function.imap-fetchstructure.php
     * @param integer $id
     * @return object
     */
    public function imapFetchStructure($id)
    {
        return imap_fetchstructure($this->imap, $id);
    }

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
    * Identify encoding by charset attribute in header
    *
    * @param $id
    * @return string
    */
    protected function getEncoding($id)
    {
        $header = $this->imapFetchStructure($id);
        $params = $header->parameters ?: [];
            foreach ($params as $k => $v) {
                if (stristr($v->attribute, 'charset')) {
                    return $v->value;
                }
            }
        return 'utf-8';
    }

    /**
     * Return general mailbox statistics
     *
     * @return bool|resource object
     */
    public function getMailboxStatistics()
    {
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
     * @param integer $id
     * @return integer
     */
    public function getUid($id)
    {
        return imap_uid($this->imap, $id);
    }

    /**
     * Get id from uid
     *
     * @param int $uid
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
            throw new ImapClientException('$id must be an integer!');
        };
        if($id <= 0){
            throw new ImapClientException('$id must be greater then 0!');
        };
        if($id > $this->countMessages()){
            throw new ImapClientException('$id does not exist');
        }
    }
}
