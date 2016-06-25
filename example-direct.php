<?php

namespace program;

require_once "ImapClient/ImapClientException.php";
require_once "ImapClient/Imap.php";

use SSilence\ImapClientException;
use SSilence\Imap;

$mailbox = 'my.imapserver.com';
$username = 'username';
$password = 'secret';
$encryption = 'tls'; // or ssl or ''

// open connection
try{
    $imap = new Imap($mailbox, $username, $password, $encryption);
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