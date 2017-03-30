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
