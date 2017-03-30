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
