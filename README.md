Welcome to php-imap-client
===================

Hello! This is php-imap-client. This is a simple and easy to use class for connecting to imap servers and working with the emails inside. Our well-known features are:
 - simple interface
 - Get emails and folders
 - Move, delete, count emails
 - Rename, delete and
 - Get attachments
 - Many more

Click to open [documentation](https://ssilence.github.io/php-imap-client/)

# Documentation

1. [Getting started](#getting-started)
2. [Connecting](#connecting)
3. [Errors](#errors)
4. [Installing](#installing)
5. [Methods](#methods)
6. [Usage](#usage)
7. [Examples](#examples)
8. [Incoming Message](#incoming-message)
9. [Sending Emails](#sending-emails)
10. [License](#license)

## Getting started
In order to get started, we first need to get php-imap-client to your code base.    
There are currently two ways of doing this.   

### 1) Composer
`composer require ssilence/php-imap-client dev-master`
```php
require_once "vendor/autoload.php";
```

### 2) Manually
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

## Connecting

### Default connnection
A default connection is the easy and most uncommon way to connect to an imap sever. Use the code
below to use our default connection method.
```php

// Encryption
$imap = new ImapClient($mailbox, $username, $password, $encryption);
// No Encryption
$imap = new ImapClient($mailbox, $username, $password);
```
This code does not check for errors and assumeing everything is right. If you are using this in production be sure to check for errors

### Advanced Connecting
We also have ways to change how your connection works. Read the code and examples below to learn some ways to modifiy your connection,
```php
/* Example 1
 * Example 1 is the advanced default connection method
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        /* This NOVALIDATE_CERT is used when the server connecting to the imap
         * servers is not https but the imap is. This ignores the failure.
         */
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
    ],
    'connect' => [
        'username' => 'user',
        'password' => 'pass'
    ]
]);

/* Example 2
 * Get debug messages
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        'validateCertificates' => ImapConnect::VALIDATE_CERT,
        // Turns debug on or off
        'debug' => ImapConnect::DEBUG,
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
        'port' => 123,
    ],
    'connect' => [
        'username' => 'user',
        'password' => 'pass'
    ]
]);

/* Example 3
 * You can also set the config then connects
 */
ImapClient::setConnectAdvanced();
ImapClient::setConnectConfig([
    'flags' => [
        'service' => ImapConnect::SERVICE_POP3,
        'validateCertificates' => ImapConnect::VALIDATE_CERT,
        'debug' => ImapConnect::DEBUG,
    ],
]);
$imap = new ImapClient();

/* Example 5
 * Here you can see all the options
 */
$imap = new ImapClient([
    'flags' => [
        # Service can be ImapConnect::SERVICE_IMAP ,ImapConnect::SERVICE_POP3, ImapConnect::SERVICE_NNTP
        'service' => ImapConnect::SERVICE_IMAP,
        # Encrypt can be ImapConnect::ENCRYPT_SSL, ImapConnect::ENCRYPT_TLS, ImapConnect::ENCRYPT_NOTLS
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        # validateCertificates can be ImapConnect::VALIDATE_CERT or ImapConnect::NOVALIDATE_CERT
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
        'secure' => ImapConnect::SECURE, # or null
        'norsh' => ImapConnect::NORSH, # or null
        'readonly' => ImapConnect::READONLY, # or null
        'anonymous' => ImapConnect::ANONYMOUS, # or null
        'debug' => ImapConnect::DEBUG # or null
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
        'port' => null,
        'flags' => null,
        'mailbox_name' => null,
    ],
    'connect' => [
        'mailbox' => null,
        'username' => 'user',
        'password' => 'pass',
        'options' => 0,
        'n_retries' => 0,
        'params' => [],
    ]
]);
```

## Errors

Many errors may be thrown while using the library, if you cant seem to find what an error means or what you are doing wrong, take a look here.
Everything is structured like this:

| Error | Description | Solution/Note |
|-------|-------------|--------------|
| **Imap function not available** | PHP does not support connections to web servers via imap | To fix this download the php_imap.dll and enable it by putting the following text in php.ini `extension=php_gettext.dll` |
| **Mailbox is not installed** | No mail box was provided | Confirm that the $mailbox variable is filled when connecting |
| **Mailbox must be a string** | The mailbox provided to connect to the web server is not a string | We cannot connect to mail boxes that have a integer in them. Make sure the $mailbox variable provied is a string |
| **Username must be a string** | The username provided to connect to the web server is not a string | Web servers dont use arrays as usernames!!! Make sure the $username variable is a string |
| **Password must be a string** | The password provided to connect to the web server is not a string | Confirm that the $password variable is a string |
| **Options must be an integer** | The options variable provided when connecting is not an integer | // Dont know. Someone checking pr tell me and ill fix |
| **N_ must be an integer** | The number of retries provided is not an integer | Make sure that the $N_retries variable is an integer |
| **Params must be an array** | The parameters provided to connect to the server are not an array | // Dont know. Someone checking pr tell me and ill fix |
| **Error connecting to [insert your mailbox string here]** | PHP-imap client had trouble connecting to the provided mailbox with the provided details | This can mean many things. It can mean your mailbox is invalid or your username and password are not valid. Comfirm your login details and make sure your mail server is online |
| **Option connect must be installed** | If you selected the advanced connection and not installed `connect` option like | <pre><code>$imap = new ImapClient([<br>    'connect' => [<br>        'username' => 'user',<br>        'password' => 'pass',<br>    ]<br>]);</code></pre> |
| **File must be specified for saveEmail()** | You did not specify a file path | Insure your code looks like this:<br><pre><code>$imap->saveEmail($your_file_path_var, $your_email_id_var, $your_part_var)</code></pre> |
| **Email id must be specified for saveEmail()** | You did not specify an email id | Insure your code looks like this:<br><pre><code>$imap->saveEmail($your_file_path_var, $your_email_id_var, $your_part_var)</code></pre> |
| **File must be a string for saveEmail()** | The provided file path is not a string | Make sure your $file is a string *not* a open file |
| **$id must be a integer for saveEmail()** | The provided email id is an integer | Make sure your $id is an integer |
| **What to use id or uid?** | You did not let us know weather to use id or uuids |  |

## Installing
PHP-imap-client can be installed 2 ways. The first composer and the second manual

### 1) Composer
Untill Big o' 2.0 is ready, use the following command to install PHP-imap-library:    
`composer require ssilence/php-imap-client dev-master`

### 2) Manual
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
You may then use connect etc

## Methods

The following methods are currently available.

| Method | Description |
|--------|-------------|
| ``__construct($mailbox, $username, $password, $encryption)`` | open new imap connection |
| ``isConnected()`` | check whether the imap connection was opened successfully |
| ``getError()`` | returns the last error message. |
| ``getFolders($separator, $type)`` | @param string $separator. Default is '.' @param int $type. Has three meanings 0,1,2. If 0 returns a nested array, if 1 it returns an array of strings, if 2 returns raw data from imap_list(). |
| ``getMessage($id)`` | get email by given id. |
| ``getMessages($number, $start, $order)`` | get emails in current folder. |
| ``getUnreadMessages($read)`` | get unread messages in current folder and mark them read. Use $read = false marks them unread. |
| ``getQuota($user)`` | Retrieve the quota level settings, and usage statics per mailbox. |
| ``getQuotaRoot($user)`` | Retrieve the quota level settings, and usage statics per mailbox. |
| ``getAllEmailAddresses()`` | returns all email addresses of all emails (for auto suggestion list). |
| ``getMailboxStatistics()`` | returns statistics, see [imap_mailboxmsginfo](https://php.net/manual/en/function.imap-mailboxmsginfo.php). |
| ``getHeaderInfo($msgNumber)`` | Get the header info via the message number. https://php.net/manual/en/function.imap-headerinfo.php#refsect1-function.imap-headerinfo-returnvalues |
| ``getMessagesByCriteria($criteria, $number, $start, $order)`` | Get messages by criteria like 'FROM uncle'. |
| ``getBriefInfoMessages()`` | Get a short information about the messages in the current folder. |
| ``getSection($id, $section)`` | Get the section of the specified message. |
| ``getUid($id)`` | Get uid through id. |
| ``getId($uid)`` | Get id through uid. |
| ``setUnseenMessage($id, $seen = true)`` | set unseen state of the message with given id. |
| ``setEmbed($val)`` | If true, embed all 'inline' images into body HTML, accesible in 'body_embed'. |
| ``setEncoding()`` | Identify encoding by charset attribute in header. |
| ``selectFolder($folder)`` | select the provided folder. |
| ``countMessages()`` | count the messages in current folder. |
| ``countUnreadMessages()`` | count the unread messages in current folder. |
| ``deleteMessage($id)`` | delete message with given id. |
| ``deleteMessages($ids)`` | delete messages with given ids (as array). |
| ``moveMessage($id, $target)`` | move message with given id in new folder |
| ``moveMessages($ids, $target)`` | move messages with given ids (as array) in new folder |
| ``addFolder($name)`` | add new folder with given name |
| ``removeFolder($name)`` | delete folder with given name |
| ``renameFolder($name, $newname)`` | rename folder with given name |
| ``purge()`` | move all emails in the current folder into trash. emails in trash and spam will be deleted. |
| ``convertToUtf8()`` | Apply encoding defined in header |
| ``saveMessageInSent($header, $body)`` | save a sent message in sent folder |
| ``saveEmail($file , $id, $part)`` | saves an email to the $file file |
| ``saveEmailSafe($file , $id, $part, $streamFilter)`` | saves an email to the $file file. This is recommended for servers with low amounts of RAM. Stream filter is set to convert.base64-decode by default. |
| ``saveAttachments($options)`` | Save attachments one incoming message. You can set any of the options: ``$options['dir'=>null, 'incomingMessage'=>null]``. |
| ``saveAttachmentsMessagesBySubject($subject, $dir = null, $charset = null)`` | Save Attachmets Messages By Subject |
| ``sendMail()`` | Send a message using the adapter. |

## Usage

### After install prep
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

### After connection
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

### Advanced connecting

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

## Examples

### Composer
```php
<?php

namespace program;

require_once "vendor/autoload.php";

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient as Imap;

$mailbox = 'my.imapserver.com';
$username = 'username';
$password = 'secret';
$encryption = Imap::ENCRYPT_SSL;

// Open connection
try{
    $imap = new Imap($mailbox, $username, $password, $encryption);
    // You can also check out example-connect.php for more connection options

}catch (ImapClientException $error){
    echo $error->getMessage().PHP_EOL;
    die(); // Oh no :( we failed
}

// Get all folders as array of strings
$folders = $imap->getFolders();
foreach($folders as $folder) {
    echo $folder;
}

// Select the folder Inbox
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

### Connect
```php
<?php

namespace program;

require_once "../ImapClient/ImapClientException.php";
require_once "../ImapClient/ImapConnect.php";
require_once "../ImapClient/ImapClient.php";

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient;

$mailbox = 'my.imapserver.com'; // Your iamp server address. If you have a specific port specify it here
$username = 'username'; // Your imap server user name
$password = 'secret'; // Super secret passsword
$encryption = ImapClient::ENCRYPT_TLS; // or ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS or null

/*
 * Default connection
 * This is the default way to connect to an IMAP server using this
 */

// Encryption
$imap = new ImapClient($mailbox, $username, $password, $encryption);
// No Encryption
$imap = new ImapClient($mailbox, $username, $password);

/*
 * Advanced connect
 *
 * Options flags same like in ImapConnect::prepareFlags() method
 * Options mailbox same like in ImapConnect::prepareMailbox() method
 * Options connect same like in ImapConnect::connect() method
 */

/* Example 1
 * Example 1 is the advanced default connection method
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        /* This NOVALIDATE_CERT is used when the server connecting to the imao
         * servers is not https but the imap is. This ignores the failure.
         */
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
    ],
    'connect' => [
        'username' => 'user',
        'password' => 'pass'
    ]
]);

/* Example 2
 * Get debug messages
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        'validateCertificates' => ImapConnect::VALIDATE_CERT,
        // Turns debug on or off
        'debug' => ImapConnect::DEBUG,
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
        'port' => 123,
    ],
    'connect' => [
        'username' => 'user',
        'password' => 'pass'
    ]
]);

/* Example 3
 * You can also set the config then connects
 */
ImapClient::setConnectAdvanced();
ImapClient::setConnectConfig([
    'flags' => [
        'service' => ImapConnect::SERVICE_POP3,
        'validateCertificates' => ImapConnect::VALIDATE_CERT,
        'debug' => ImapConnect::DEBUG,
    ],
]);
$imap = new ImapClient();

/* Example 5
 * Here you can see all the options
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP, # ImapConnect::SERVICE_IMAP ,ImapConnect::SERVICE_POP3, ImapConnect::SERVICE_NNTP
        'encrypt' => ImapConnect::ENCRYPT_SSL, # ImapConnect::ENCRYPT_SSL, ImapConnect::ENCRYPT_TLS, ImapConnect::ENCRYPT_NOTLS
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT, # ImapConnect::VALIDATE_CERT, ImapConnect::NOVALIDATE_CERT
        'secure' => ImapConnect::SECURE, # or null
        'norsh' => ImapConnect::NORSH, # or null
        'readonly' => ImapConnect::READONLY, # or null
        'anonymous' => ImapConnect::ANONYMOUS, # or null
        'debug' => ImapConnect::DEBUG # or null
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
        'port' => null,
        'flags' => null,
        'mailbox_name' => null,
    ],
    'connect' => [
        'mailbox' => null,
        'username' => 'user',
        'password' => 'pass',
        'options' => 0,
        'n_retries' => 0,
        'params' => [],
    ]
]);
```

### Direct
```php
<?php

namespace program;

require_once "ImapClient/ImapClientException.php";
require_once "ImapClient/ImapConnect.php";
require_once "ImapClient/ImapClient.php";

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient as Imap;

$mailbox = 'my.imapserver.com';
$username = 'username';
$password = 'secret';
$encryption = Imap::ENCRYPT_TLS;

// Open connection
try{
    $imap = new Imap($mailbox, $username, $password, $encryption);
    // You can also check out example-connect.php for more connection options.
}catch (ImapClientException $error){
    echo $error->getMessage().PHP_EOL;
    die();
}

// Get all of the folders as an array of strings
$folders = $imap->getFolders();
foreach($folders as $folder) {
    echo $folder;
}

// Select the folder named INBOX
$imap->selectFolder('INBOX');

// Count the messages in the current folder
$overallMessages = $imap->countMessages();
$unreadMessages = $imap->countUnreadMessages();

// Fetch all of the emails in the current folder
$emails = $imap->getMessages();
var_dump($emails);

// Create a new folder named "Archive"
$imap->addFolder('archive');

// Move the first email from INBOX to our new folder
$imap->moveMessage($emails[0]['uid'], 'archive');

// Delete the second message in INBOX
$imap->deleteMessage($emails[1]['uid']);
```

## Incoming Message

### Structure
Incoming Message is an object(SSilence\ImapClient\IncomingMessage) that has 5 basic properties
```
$imap->incomingMessage->header
$imap->incomingMessage->message
$imap->incomingMessage->attachments
$imap->incomingMessage->section
$imap->incomingMessage->structure
```
and one for debugging
```
$imap->incomingMessage->debug
```

#### Header properties
The header properties typically look like this:
```
["header"]=>
  object(stdClass)#3 (15) {
    ["subject"]=>
    string(10) "Ocoto cat is in the new!"
    ["from"]=>
    string(36) "..."
    ["to"]=>
    string(36) "..."
    ["date"]=>
    string(31) "Tue, 21 Mar 2017 11:02:37 +0000"
    ["message_id"]=>
    string(37) "<1490094157.905281607@f369.i.gmail.com>"
    ["size"]=>
    int(472418)
    ["uid"]=>
    int(42243)
    ["msgno"]=>
    int(82)
    ["recent"]=>
    int(0)
    ["flagged"]=>
    int(0)
    ["answered"]=>
    int(0)
    ["deleted"]=>
    int(0)
    ["seen"]=>
    int(1)
    ["draft"]=>
    int(0)
    ["udate"]=>
    int(1490094157)
    ["details"]=>
        object(stdClass)#4 (20) {}
  }
```
To get the subject of the message, use
$imap->incomingMessage->header->subject

This object also contains the id and the uid of the current message according to https://php.net/manual/en/function.imap-fetch-overview.php:
```php
# id
    $imap->incomingMessage->header->msgno;
# uid
    $imap->incomingMessage->header->uid;
```

To get the CC or BCC use the property
```php
$imap->incomingMessage->header->details
```
Header details properties have more properties like this [imap_headerinfo](https://php.net/manual/en/function.imap-headerinfo.php).
If there is no property in the returned object, then it is not present in this email.
To get the CC or BCC of the message, use
```php
$imap->incomingMessage->header->details->cc
# or
$imap->incomingMessage->header->details->bcc
# cc and bcc is an array of objects with properties [personal, adl, mailbox, host]
```

#### Message properties
Message properties will look like this:
```
["message"]=>
  object(stdClass)#29 (3) {
    ["plain"]=>
    string(16) "text message"
    ["text"]=>
    string(16) "text message"
    ["info"]=>
    array(2) {
      [0]=>
      object(stdClass)#27 (2) {
        ["structure"]=>
        object(stdClass)#28 (11) {
          ["type"]=>
          int(0)
          ["encoding"]=>
          int(3)
          ["ifsubtype"]=>
          int(1)
          ["subtype"]=>
          string(5) "PLAIN"
          ["ifdescription"]=>
          int(0)
          ["ifid"]=>
          int(0)
          ["bytes"]=>
          int(20)
          ["ifdisposition"]=>
          int(0)
          ["ifdparameters"]=>
          int(0)
          ["ifparameters"]=>
          int(1)
          ["parameters"]=>
          array(1) {
            [0]=>
            object(stdClass)#33 (2) {
              ["attribute"]=>
              string(7) "charset"
              ["value"]=>
              string(5) "utf-8"
            }
          }
        }
        ["body"]=>
        string(16) "text message"
      }
      [1]=>
      object(stdClass)#30 (2) {
        ["structure"]=>
        object(stdClass)#31 (11) {
          ["type"]=>
          int(0)
          ["encoding"]=>
          int(3)
          ["ifsubtype"]=>
          int(1)
          ["subtype"]=>
          string(4) "HTML"
          ["ifdescription"]=>
          int(0)
          ["ifid"]=>
          int(0)
          ["bytes"]=>
          int(72)
          ["ifdisposition"]=>
          int(0)
          ["ifdparameters"]=>
          int(0)
          ["ifparameters"]=>
          int(1)
          ["parameters"]=>
          array(1) {
            [0]=>
            object(stdClass)#34 (2) {
              ["attribute"]=>
              string(7) "charset"
              ["value"]=>
              string(5) "utf-8"
            }
          }
        }
        ["body"]=>
        string(56) "<HTML><BODY><br><br><br>html text message<br></BODY></HTML>"
      }
    }
    ["html"]=>
    string(56) "<HTML><BODY><br><br><br>html text message<br></BODY></HTML>"
  }
```
To get info on the message use
$imap->incomingMessage->message->info Is an array. It will allways return an array

To get the body of the message use
```php
$imap->incomingMessage->message->html
# or
$imap->incomingMessage->message->plain
# or synonym plain
$imap->incomingMessage->message->text
```
Get the emails charset like this:
```php
$imap->incomingMessage->message->html->charset
```
Get all subtype message:
```php
# array
$imap->incomingMessage->message->types
```
These are auto generated by the client. This allows you to have an html and a plain text version of the email

#### Attachments properties
Attachment properties is an array of objects.
If the letter has attachments, then its return will look like as follows
```
["attachments"]=>
  array(1) {
    [0]=>
      object(stdClass)#26 (3) {
        ["name"]=> string(19) "20140228_160524.jpg"
        ["body"]=> string(945576) "..."
        ["info"]=> "info about attachment"

      ... and next object ...
    [1]=>
       object(stdClass)#18 (2) {}
  }
```

The attachment object has 3 basic properties. Like this:
```php
$imap->incomingMessage->attachments[0]->name;
$imap->incomingMessage->attachments[0]->body;
$imap->incomingMessage->attachments[0]->info;
```

To get the names of all attachments in an email, use the following
```php
foreach($attachments as $attachment){
    echo $attachment->name;
}
```

All information for the email will be in the info property. It will look like this:
```php
["info"]=>
    object(stdClass)#18 (2) {
      ["structure"]=>
      object(stdClass)#19 (13) {
        ["type"]=>
        int(5)
        ["encoding"]=>
        int(3)
        ["ifsubtype"]=>
        int(1)
        ["subtype"]=>
        string(4) "JPEG"
        ["ifdescription"]=>
        int(0)
        ["ifid"]=>
        int(0)
        ["bytes"]=>
        int(237916)
        ["ifdisposition"]=>
        int(1)
        ["disposition"]=>
        string(10) "attachment"
        ["ifdparameters"]=>
        int(1)
        ["dparameters"]=>
        array(1) {
          [0]=>
          object(stdClass)#25 (2) {
            ["attribute"]=>
            string(8) "filename"
            ["value"]=>
            string(12) "Location.jpg"
          }
        }
        ["ifparameters"]=>
        int(1)
        ["parameters"]=>
        array(1) {
          [0]=>
          object(stdClass)#24 (2) {
            ["attribute"]=>
            string(4) "name"
            ["value"]=>
            string(12) "Location.jpg"
          }
        }
      }
      ["body"]=>string(173862) ""
```

The attachment info object has 2 basic properties.
```php
$imap->incomingMessage->attachment[0]->info->structure
$imap->incomingMessage->attachment[0]->info->body
```

Body properties contain the body attachment(s).

#### Section properties
Section properties it is array sections of which the email consists.
```
["section"]=>
  array(5) {
    [0]=>
    string(1) "1"
    [1]=>
    string(1) "2"
    [2]=>
    string(1) "3"
    [5]=>
    string(3) "1.1"
    [7]=>
    string(3) "1.2"
  }
```
#### Structure properties
The structure propertie is the complete structure of the email
#### Debug properties
The debug propertie can be used for debugging

# Sending Emails

The IMAP protocol is designed to read messages and does not support the sending of messages.
But if you want ImapClient to send messages, then use the AdapterForOutgoingMessage class.
To do this you will also need to install PHPMailer

## First step
The first step is to install php mailer. You can do so in two ways.

1) Composer
```php
composer require phpmailer/phpmailer
```
2) Minimal 
Read [PHPMailer's guide on a minimal installation](https://github.com/PHPMailer/PHPMailer#minimal-installation)

## Second step
Turn phpmailer into an adapter.
File AdapterForOutgoingMessage.php
To do this, we rewrite send() method. 
Note this is note safe yet and may be re written in the future.
```php
# Be sure to add a namespace
use \PHPMailer;

class AdapterForOutgoingMessage
{
# ... code ...

    public function send()
    {
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp1.example.com;smtp2.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = $this->config['connect']['username'];
        $mail->Password = $this->config['connect']['password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom(self::$options['fromEmail'], self::$options['fromEmailName']);
        $mail->addAddress(self::$options['toEmail'], self::$options['toEmailName']);
        $mail->addAttachment(self::$options['fileName']);
        $mail->isHTML(true);
        $mail->Subject = self::$options['subject'];
        $mail->Body    = self::$options['messageHtml'];
        $mail->AltBody = self::$options['messagePlain'];
        if(!$mail->send()) {
            throw new ImapClientException('Message could not be sent'.PHP_EOL.$mail->ErrorInfo);
        } else {
            # echo 'Message has been sent';
            return true;
        };
        return false;
    }

# ... code ...
}
```

## Third step
Now you can send messages like this:
```php

use SSilence\ImapClient\AdapterForOutgoingMessage;

try{

$imap = new ImapClient([
    'flags' => [ ... ],
    'mailbox' => [ ... ],
    'connect' => [
        'username' => 'user@gmail.com',
        'password' => 'password',
    ]
]);

# ... code ...

AdapterForOutgoingMessage::setOptions([
    'fromEmail' => 'from@gmail.com',
    'fromEmailName' => 'fromUser',
    'toEmail' => 'to@gmail.com',
    'toEmailName' => 'toUser',
    'fileName' => 'file',
    'subject' => 'subject',
    'messageHtml' => 'message html',
    'messagePlain' => 'message'
]);
$imap->sendMail();

# ... code ...

}catch (ImapClientException $error){
    echo $error->getInfo();
};
```

# License

Copyright (c) 2025 Tobias Zeising
tobias.zeising@aditu.de  
https://www.aditu.de  
Licensed under the MIT license