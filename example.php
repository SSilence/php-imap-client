<?PHP

require_once "Imap.php";

$mailbox = 'my.imapserver.com';
$username = 'username';
$password = 'secret';
$encryption = 'tls'; // or ssl or ''

// open connection
$imap = new Imap($mailbox, $username, $password, $encryption);

// stop on error
if($imap->isConnected()===false)
    die($imap->getError());

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
$imap->moveMessage($emails[0]['id'], 'archive');

// delete second message
$imap->deleteMessage($emails[1]['id']);