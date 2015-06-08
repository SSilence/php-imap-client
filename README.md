PHP Imap Client
===============

Copyright (c) 2013 Tobias Zeising, tobias.zeising@aditu.de  
http://www.aditu.de  
Licensed under the MIT license  


Features
--------

This PHP IMAP Client is a simple class for IMAP Email access. 
It base on the [PHP IMAP extension][1] and offers a simple interface for handling emails. In my opinion the PHP IMAP functions are not very intuitive.

* simple interface
* get emails and folders
* move, delete, count emails
* rename, delete and add folders
* get attachments


How to use
----------

Instantiating the class will open the imap connection.

```php
$mailbox = 'my.imapserver.com';
$username = 'myuser';
$password = 'secret';
$encryption = 'tls'; // or ssl or '';
$imap = new Imap($mailbox, $username, $password, $encryption);

if($imap->isConnected()===false) {
    die($imap->getError());
}
```

Now you can fetch all available folders:

```php
$folders = $imap->getFolders(); // returns array of strings
foreach($folders as $folder) {
    echo $folder;
}
```

Now you can select a folder:

```php
$imap->selectFolder("Inbox");
```

Once you selected a folder you can count the messages in this folder:

```php
$overallMessages = $imap->countMessages();
$unreadMessages = $imap->countUnreadMessages();
```

Okay, now lets fetch all emails in the currently selected folder (in our example the "Inbox"):

```php
$emails = $imap->getMessages();
var_dump($emails);
```

getMessages() will not mark emails as read! It will return the following structure without changing the emails. In this example two emails are in the Inbox.

```php
array(2) {
  [0]=>
  array(8) {
    ["to"]=>
    array(1) {
      [0]=>
      string(30) "Tobias Zeising <tobias.zeising@aditu.de>"
    }
    ["from"]=>
    string(30) "Karl Mustermann <karl.mustermann@aditu.de>"
    ["date"]=>
    string(31) "Fri, 27 Dec 2013 18:44:52 +0100"
    ["subject"]=>
    string(12) "Test Subject"
    ["id"]=>
    int(15)
    ["unread"]=>
    bool(true)
    ["answered"]=>
    bool(false)
    ["body"]=>
    string(240) "<p>This is a test body.</p>

    <p>With a bit <em><u>html</u></em>.</p>

    <p>and without <span style="color:#008000"><span style="font-size:14px"><span style="font-family:arial,helvetica,sans-serif">attachment</span></span></span></p>
    "
  }
  [1]=>
  array(9) {
    ["to"]=>
    array(1) {
      [0]=>
      string(29) "tobias.zeising@aditu.de <tobias.zeising@aditu.de>"
    }
    ["from"]=>
    string(40) "Karl Ruediger <karl.ruediger@aditu.de>"
    ["date"]=>
    string(31) "Thu, 19 Dec 2013 17:45:37 +0100"
    ["subject"]=>
    string(19) "Test mit Attachment"
    ["id"]=>
    int(14)
    ["unread"]=>
    bool(false)
    ["answered"]=>
    bool(false)
    ["body"]=>
    string(18) "Anbei eine Datei"
    ["attachments"]=>
    array(1) {
      [0]=>
      array(2) {
        ["name"]=>
        string(24) "640 x 960 (iPhone 4).jpg"
        ["size"]=>
        int(571284)
      }
    }
  }
}
```

You can also add/rename/delete folders. Lets add a new folder:

```php
$imap->addFolder('archive');
```

Now we move the first email into this folder

```php
$imap->moveMessage($emails[0]['id'], 'archive');
```

And we delete the second email from inbox

```php
$imap->deleteMessage($emails[1]['id']);
```


Methods
-------

Following methods are available.

* ``__construct($mailbox, $username, $password, $encryption)`` open new imap connection
* ``isConnected()`` check whether the imap connection could be opened successfully
* ``getError()`` returns the last error message
* ``selectFolder($folder)`` select current folder
* ``getFolders()`` get all available folders
* ``countMessages()`` count messages in current folder
* ``countUnreadMessages()`` count unread messages in current folder
* ``getMessages($withbody = true)`` get emails in current folder
* ``getMessage($id, $withbody = true)`` get email by given id
* ``deleteMessage($id)`` delete message with given id
* ``deleteMessages($ids)`` delete messages with given ids (as array)
* ``moveMessage($id, $target)`` move message with given id in new folder
* ``moveMessages($ids, $target)`` move messages with given ids (as array) in new folder
* ``setUnseenMessage($id, $seen = true)`` set unseen state of the message with given id
* ``getAttachment($id, $index = 0)`` get attachment of the message with given id (getMessages returns all available attachments)
* ``addFolder($name)`` add new folder with given name
* ``removeFolder($name)`` delete folder with fiven name
* ``renameFolder($name, $newname)`` rename folder with given name
* ``purge()`` move all emails in the current folder into trash. emails in trash and spam will be deleted
* ``getAllEmailAddresses()`` returns all email addresses of all emails (for auto suggestion list)
* ``saveMessageInSent($header, $body)`` save a sent message in sent folder


Feedback
--------

Feel free to improve this class. You can use the pull request function of github for contributing improvments. The inner structure of this class is simple and easy. Don't hesitate and check it out ;)

  [1]: http://at1.php.net/imap
