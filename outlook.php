<?php

// Require the class file
require_once 'Imap.php';

// Login data
$mailbox = 'imap-mail.outlook.com';
$username = 'phpimapclient@outlook.com';
$password = 'Abcd12345**';
$encryption = 'ssl';

// Initialize imap connection
$imap = new Imap($mailbox, $username, $password, $encryption);

// Check connection status
if ($imap->isConnected())
{
	// Output test. Do you tests here:
	$output = array(
		'getFolders'           => $imap->getFolders(),
		'selectFolder'         => $imap->selectFolder('Inbox'),
		'countMessages'        => $imap->countMessages(),
		'addFolder '           => $imap->addFolder('Test'),
		'countUnreadMessages' => $imap->countUnreadMessages(),
		'getMessage'           => $imap->getMessage(1, TRUE),
		'getAttachment'        => $imap->getAttachment(1, 0),
	);
}
else
{
	$output = array(
		'getError' => $imap->getError(),
	);
}

// Require the view to show the output results
require_once 'view.php';
