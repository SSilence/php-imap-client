# Getting started
In order to get started, we first need to get php-imap-client to your code base.    
There are currently two ways of doing this.   
###1) Composer
`composer require ssilence/php-imap-client dev-master`
```php
require_once "vendor/autoload.php";
```
###2) Manually
1) Download the files from github or the releases page    
2) Extract the files into the folder you wish    
3) In the file that will call methods add    
```php
require_once "path/to/ImapClientException.php";
require_once "path/to/ImapConnect.php";
require_once "path/to/ImapClient.php";
require_once "path/to/IncomingMessage.php";
require_once "path/to/TypeAttachments.php";
require_once "path/to/TypeBody.php";
```
After this, we need to let php we need these classes:
```php
use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient as Imap;
```
Next, we need to declare out variables:
```php
$mailbox = 'my.imapserver.com';
$username = 'username';
$password = 'secret';
$encryption = Imap::ENCRYPT_SSL; // TLS OR NULL accepted
```
Next, we need to open the connection:
```php
// Open connection
try{
    $imap = new Imap($mailbox, $username, $password, $encryption);
    // You can also check out example-connect.php for more connection options

}catch (ImapClientException $error){
    echo $error->getMessage().PHP_EOL; // You know the rule, no errors in production ...
    die(); // Oh no :( we failed
}
```
After that we can do all this fun stuff :)
```php
// Get all folders as array of strings
$folders = $imap->getFolders();
foreach($folders as $folder) {
    echo $folder;
}

// Select the folder INBOX
$imap->selectFolder('INBOX');

// Count the messages in current folder
$overallMessages = $imap->countMessages();
$unreadMessages = $imap->countUnreadMessages();

// Fetch all the messages in the current folder
$emails = $imap->getMessages();
var_dump($emails);

// Create a new folder named "archive"
$imap->addFolder('archive');

// Move the first email to our new folder
$imap->moveMessage($emails[0]['uid'], 'archive');

// Delete the second message
$imap->deleteMessage($emails[1]['uid']);
```

