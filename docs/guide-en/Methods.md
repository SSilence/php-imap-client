# Methods

The following methods are currently available.

* ``__construct($mailbox, $username, $password, $encryption)`` open new imap connection
* ``isConnected()`` check whether the imap connection could be opened successfully

* ``getError()`` returns the last error message.
* ``getFolders($seperator, $type)`` @param string $separator. Default is '.' @param int $type. Has three meanings 0,1,2.If 0 return nested array, if 1 return an array of strings, if 2 return raw imap_list().
* ``getMessage($id)`` get email by given id.
* ``getMessages($number, $start, $order)`` get emails in current folder.
* ``getUnreadMessages($read)`` get unread messages in current folder and marks them like read. Use $read = false marks them like unread.
* ``getAttachment($id, $index = 0)`` get attachment of the message with given id (getMessages returns all available attachments).
* ``getQuota($user)`` Retrieve the quota level settings, and usage statics per mailbox.
* ``getQuotaRoot($user)`` Retrieve the quota level settings, and usage statics per mailbox.
* ``getAllEmailAddresses()`` returns all email addresses of all emails (for auto suggestion list).
* ``getMailboxStatistics()`` returns statistics, see [imap_mailboxmsginfo](http://php.net/manual/de/function.imap-mailboxmsginfo.php).
* ``getHeaderInfo($msgNumber)`` Get the header info via the message number. http://php.net/manual/en/function.imap-headerinfo.php#refsect1-function.imap-headerinfo-returnvalues
* ``getMessagesByCriteria($criteria, $number, $start, $order)`` Get messages by criteria like 'FROM uncle'.
* ``getBriefInfoMessages()`` Get a short information about the messages in the current folder.

* ``setUnseenMessage($id, $seen = true)`` set unseen state of the message with given id.
* ``setEmbed($val)`` If true, embed all 'inline' images into body HTML, accesible in 'body_embed'.
* ``setEncoding()`` Identify encoding by charset attribute in header.

* ``selectFolder($folder)`` select current folder.

* ``countMessages()`` count messages in current folder.
* ``countUnreadMessages()`` count unread messages in current folder.

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
* ``saveEmailSafe($file , $id, $part, $streamFilter)`` saves an email to the $file file. This is recommended for servers with low amounts of RAM. Stream filter is set to convert.base64-decode by default
* ``saveAttachmetsMessagesBySubject($subject, $dir = null, $charset = null)`` Save Attachmets Messages By Subject

* ``sendMail()`` Send a message via the adapter.
