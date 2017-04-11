# Usage
#### After install prep
After you install this library ensure you have added the required classes.
A basic connection may look like this:
```php
$mailbox = 'my.imapserver.com';
$username = 'myuser';
$password = 'secret';
$encryption = Imap::ENCRYPT_SSL; // or ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS or null

try{

$imap = new Imap($mailbox, $username, $password, $encryption);
# ... and further code ...

}catch (ImapClientException $error){
    echo $error->getInfo();
};              
```
The above code connects you to a mail server and makes sure it connected. Change the variables to your information
#### After connection
There are many things you can do after the code above.
For example you can get and echo all folders
```php
$folders = $imap->getFolders();
var_dump($folders);
# and
foreach($folders as $folder) {
    echo $folder;
}
# or 
foreach($folders as $folder => $subFolder) {
    echo $folder.PHP_EOL;
    echo $subFolder.PHP_EOL;
}
```
See [getFolders()](Methods.md) method settings.
You can also select folders

```php
$imap->selectFolder("Inbox");
```
Once you selected a folder you can count the number of messages in the folder:

```php
$overallMessages = $imap->countMessages();
$unreadMessages = $imap->countUnreadMessages();
```

To get a brief summary of all messages in the current folder, including the message ID you can use this
```php
$imap->getBriefInfoMessages()
```

Get the message with ID 82
```php
$imap->getMessage(82)
```

Save all of the attachmets in this email.
```php
$imap->saveAttachments();
```
or
```php
$imap->saveAttachments(['dir'=>'dir/to/save']);
```
or save the attachment(s) like this
```php
$message = $imap->getMessage(82);
$imap->saveAttachments(['dir' => "your/path/", 'incomingMessage' => $message]);
```

It is also possible to save all attachments for messages with a special word in the message subject.
```php
$imap->saveAttachmetsMessagesBySubject('Special text', 'path/to/save/attach');
```

Get the header info like cc and bcc
```php
var_dump(getHeaderInfo(1));
```

Get all unread messages.
```php
$imap->getUnreadMessages()
```

Okay, now lets fetch all emails in the currently selected folder (in our example the "Inbox"):
```php
$emails = $imap->getMessages();
```
Now $emails it is array object.

The structure of a single message when it is received by the method getMessage() or getMessages()
it by the look here [Incoming Message](IncomingMessage.md)

For example get subject and simple text messages
```php
foreach($emails as $email){
    echo $email->header->subject.PHP_EOL;
    echo $email->message->plain.PHP_EOL;
};
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

You can use the method of sending messages.
```php
$imap->sendMail();
```
But for this you need to take several steps.
[Adapter for outgoing message. Use in 3 steps.](AdapterForOutgoingMessage.md)

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
 or go [Advanced connecting](AdvancedConnecting.md)
 or you can see code ImapConnect class.
