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
if ($imap->is_connected())
{
	// Output test. Do you tests here:
	$output = array(
		'get_folders'           => $imap->get_folders(),
		'select_folder'         => $imap->select_folder('Inbox'),
		'count_messages'        => $imap->count_messages(),
		'add_folder '           => $imap->add_folder('Tes'),
		'count_unread_messages' => $imap->count_unread_messages(),
		'get_message'           => $imap->get_message(1, TRUE),
		'get_attachment'        => $imap->get_attachment(1, 0),
	);
}
else
{
	$output = array(
		'get_error' => $imap->get_error(),
	);
}

// Require the view to show the output results
require_once 'view.php';
