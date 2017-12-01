# Composer
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
# Connect
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
# Direct
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
