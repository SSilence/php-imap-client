# Methods

The following methods are currently available.

* ``__construct($mailbox, $username, $password, $encryption)`` open new imap connection
* ``isConnected()`` check whether the imap connection was opened successfully
* ``getError()`` returns the last error message.
* ``getFolders($separator, $type)`` @param string $separator. Default is '.' @param int $type. Has three meanings 0,1,2. If 0 returns a nested array, if 1 it returns an array of strings, if 2 returns raw data from imap_list().
* ``getMessage($id)`` get email by given id.
* ``getMessages($number, $start, $order)`` get emails in current folder.
* ``getUnreadMessages($read)`` get unread messages in current folder and mark them read. Use $read = false marks them unread.
* ``getQuota($user)`` Retrieve the quota level settings, and usage statics per mailbox.
* ``getQuotaRoot($user)`` Retrieve the quota level settings, and usage statics per mailbox.
* ``getAllEmailAddresses()`` returns all email addresses of all emails (for auto suggestion list).
* ``getMailboxStatistics()`` returns statistics, see [imap_mailboxmsginfo](http://php.net/manual/de/function.imap-mailboxmsginfo.php).
* ``getHeaderInfo($msgNumber)`` Get the header info via the message number. http://php.net/manual/en/function.imap-headerinfo.php#refsect1-function.imap-headerinfo-returnvalues
* ``getMessagesByCriteria($criteria, $number, $start, $order)`` Get messages by criteria like 'FROM uncle'.
* ``getBriefInfoMessages()`` Get a short information about the messages in the current folder.
* ``getSection($id, $section)`` Get the section of the specified message.
* ``getUid($id)`` Get uid through id.
* ``getId($uid)`` Get id through uid.
* ``setUnseenMessage($id, $seen = true)`` set unseen state of the message with given id.
* ``setEmbed($val)`` If true, embed all 'inline' images into body HTML, accesible in 'body_embed'.
* ``setEncoding()`` Identify encoding by charset attribute in header.
* ``selectFolder($folder)`` select the provided folder.
* ``countMessages()`` count the messages in current folder.
* ``countUnreadMessages()`` count the unread messages in current folder.
* ``deleteMessage($id)`` delete message with given id.
* ``deleteMessages($ids)`` delete messages with given ids (as array).
* ``moveMessage($id, $target)`` move message with given id in new folder
* ``moveMessages($ids, $target)`` move messages with given ids (as array) in new folder
* ``addFolder($name)`` add new folder with given name
* ``removeFolder($name)`` delete folder with fiven name
* ``renameFolder($name, $newname)`` rename folder with given name
* ``purge()`` move all emails in the current folder into trash. emails in trash and spam will be deleted.
* ``convertToUtf8()`` Apply encoding defined in header
* ``saveMessageInSent($header, $body)`` save a sent message in sent folder
* ``saveEmail($file , $id, $part)`` saves an email to the $file file
* ``saveEmailSafe($file , $id, $part, $streamFilter)`` saves an email to the $file file. This is recommended for servers with low amounts of RAM. Stream filter is set to convert.base64-decode by default.
* ``saveAttachments($options)`` Save attachments one incoming message. You can set any of the options: ``$options['dir'=>null, 'incomingMessage'=>null]``.
* ``saveAttachmentsMessagesBySubject($subject, $dir = null, $charset = null)`` Save Attachmets Messages By Subject
* ``sendMail()`` Send a message using the adapter.
