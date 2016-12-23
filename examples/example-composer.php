<?php

namespace program;

require_once "vendor/autoload.php";

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient as Imap;

$mailbox = 'my.imapserver.com';
$username = 'username';
$password = 'secret';
$encryption = Imap::ENCRYPT_SSL; // or ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS or null
// open connection
try{
    $imap = new Imap($mailbox, $username, $password, $encryption);

    /*
     * Or use advanced connect option like this
     *
    $imap = new ImapClient([
        'flags' => [
            'service' => ImapConnect::SERVICE_IMAP,
            'encrypt' => ImapConnect::ENCRYPT_SSL,
            'validateCertificates' => ImapConnect::VALIDATE_CERT,
        ],
        'mailbox' => [
            'remote_system_name' => 'imap.server.ru',
            'port' => 431,
        ],
        'connect' => [
            'username' => 'user',
            'password' => 'pass'
        ]
    ]);
    */

}catch (ImapClientException $error){
    echo $error->getMessage().PHP_EOL;
    die();
}

// get all folders as array of strings
$folders = $imap->getFolders();
foreach($folders as $folder)
    echo $folder;

// select folder Inbox
$imap->selectFolder('INBOX');

// count messages in current folder
$overallMessages = $imap->countMessages();
$unreadMessages = $imap->countUnreadMessages();

// fetch all messages in the current folder
$emails = $imap->getMessages();
var_dump($emails);

// add new folder for archive
$imap->addFolder('archive');

// move the first email to archive
$imap->moveMessage($emails[0]['uid'], 'archive');

// delete second message
$imap->deleteMessage($emails[1]['uid']);
