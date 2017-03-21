# Usage
#### After install prep
After you install this library, however you did it make sure the file you want to do the connecting with includes the required classes
A basic connection may look like this:
```php
$mailbox = 'my.imapserver.com';
$username = 'myuser';
$password = 'secret';
$encryption = Imap::ENCRYPT_SSL; // or ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS or null
$imap = new Imap($mailbox, $username, $password, $encryption);

if($imap->isConnected()===false) {
    die($imap->getError());
}                     
```
The above code connects you to a mail server and makes sure it connected. Change the variables to your informatin
#### After connection
There are many things you can do after the code above.
For example you can get and echo all folders
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
WARNING!!!!: getMessages() will not mark emails as read! It will return the following structure without changing the emails. In this example two emails are in the Inbox.

```
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

We also can save emails
```php
// Note: for slower web servers will less ram use saveEmailSafe()
$imap->saveEmail('archive/users/johndoe/email_1.eml', 1);
```

For a full list of methods you can do check [current list of methods](Methods.md).

#### Advanced connecting

You can also use the below code to add some more options while connecting

```php
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP, # ImapConnect::SERVICE_IMAP ,ImapConnect::SERVICE_POP3, ImapConnect::SERVICE_NNTP
        'encrypt' => ImapConnect::ENCRYPT_SSL, # ImapConnect::ENCRYPT_SSL, ImapConnect::ENCRYPT_TLS, ImapConnect::ENCRYPT_NOTLS
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT, # ImapConnect::VALIDATE_CERT, ImapConnect::NOVALIDATE_CERT
        # ... and other
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
        'port' => '431',
        'mailbox_name' => 'INBOX.Send',
        # ... and other
    ],
    'connect' => [
        'username' => 'user',
        'password' => 'pass',
        # ... and other
    ]
]);
```
 All connecting options you can see in example-connect.php file
 or go [Advanced connecting](docs/guide-en/AdvancedConnecting.md)
 or you can see code ImapConnect class.
