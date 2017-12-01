<?php
/**
 * This class starts up everything for Travis.
 */

namespace tests;


ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once "autoload.php";

require_once "tests/TestImapClient.php";
require_once "tests/MessagesPool.php";
require_once "tests/MessageInterface.php";
require_once "tests/Message.php";
require_once "tests/Check.php";
require_once "tests/SimpleMessage.php";
require_once "tests/TestMessage1.php";

use SSilence\ImapClient\Helper;
use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient;
use SSilence\ImapClient\OutgoingMessage;
use SSilence\ImapClient\AdapterForOutgoingMessage;
use SSilence\ImapClient\IncomingMessage;
use SSilence\ImapClient\IncomingMessageAttachment;
use SSilence\ImapClient\Section;
use SSilence\ImapClient\SubtypeBody;
use SSilence\ImapClient\TypeAttachments;
use SSilence\ImapClient\TypeBody;

use SSilence\ImapClient\Tests\TestImapClient;
use SSilence\ImapClient\Tests\MessagesPool;
use SSilence\ImapClient\Tests\Check;
use SSilence\ImapClient\Tests\TestMessage1;
use SSilence\ImapClient\Tests\SimpleMessage;



try{

    $imap = new TestImapClient();

    /*
     * Test_1
     * Check basis methods for testing
     * getFolders() and selectFolder() and addFolder()
     */
    Check::method($imap, 'getFolders');
    Check::method($imap, 'selectFolder');
    Check::method($imap, 'addFolder');
    Check::method($imap, 'countMessages');
    Check::method($imap, 'getMailbox');
    Check::method($imap, 'getBriefInfoMessages');
    Check::method($imap, 'getUid');
    Check::method($imap, 'deleteMessage');
    Check::method($imap, 'getMessage');
    Check::method($imap, 'getUid');
    Check::method($imap, 'saveEmail');
    Check::method($imap, 'saveEmailSafe');
    Check::method($imap, 'getError');
    Check::method($imap, 'countUnreadMessages');
    Check::method($imap, 'getUnreadMessages');
    Check::method($imap, 'getMessagesByCriteria');
    Check::method($imap, 'saveAttachmentsMessagesBySubject');
    Check::method($imap, 'getMessages');
    Check::method($imap, 'getSection');
    Check::method($imap, 'saveAttachments');
    Check::method($imap, 'deleteMessages');
    Check::method($imap, 'moveMessage');
    Check::method($imap, 'moveMessages');
    Check::method($imap, 'setUnseenMessage');
    Check::method($imap, 'removeFolder');
    Check::method($imap, 'renameFolder');
    Check::method($imap, 'purge');
    Check::method($imap, 'getAllEmailAddresses');
    Check::method($imap, 'getEmailAddressesInFolder');
    Check::method($imap, 'saveMessageInSent');
    Check::method($imap, 'getTrash');
    Check::method($imap, 'getSent');
    Check::method($imap, 'getMessageHeader');
    Check::method($imap, 'getMessageOverview');
    Check::method($imap, 'getMessagesOverview');
    Check::method($imap, 'imapFetchOverview');
    Check::method($imap, 'imapHeaderInfo');
    Check::method($imap, 'imapFetchStructure');
    Check::method($imap, 'toAddress');
    Check::method($imap, 'arrayToAddress');
    Check::method($imap, 'convertToUtf8');
    Check::method($imap, 'getEncoding');
    Check::method($imap, 'getMailboxStatistics');
    Check::method($imap, 'unSubscribe');
    Check::method($imap, 'getQuota');
    Check::method($imap, 'getQuotaRoot');
    Check::method($imap, 'getUid');
    Check::method($imap, 'getId');
    Check::method($imap, 'getId');
    Check::method($imap, 'sendMail');
    Check::method($imap, 'checkMessageId');
    echo 'Test_1 OK'.PHP_EOL;

    /*
     * Test_2
     * Create test folder in you imap server
     */
    $testFolder = 'TestImapClient';
    $folders = $imap->getFolders(null, 2);
    $find = false;

    foreach ($folders as $folder) {
        $pos = strpos($folder, $testFolder);
        if($pos !== false) {
            $find = true;
        };
    };

    if($find){
        $imap->selectFolder($testFolder);
    }else{
        $imap->addFolder($testFolder);
        $imap->selectFolder($testFolder);
    };

    $countMessages4 = $imap->countMessages();
    if($countMessages4 == 0){
        echo 'The mailbox is cleared'.PHP_EOL;
    }else{
        throw new ImapClientException('The mailbox is not cleaned');
    };
    echo 'Test_2 OK'.PHP_EOL;

    /*
     * Test_3
     * Send emails in current mailbox and delete
     */
    $mailbox = $imap->getMailbox();
    MessagesPool::push(new SimpleMessage());
    MessagesPool::push(new SimpleMessage());
    MessagesPool::push(new SimpleMessage());
    MessagesPool::send($imap->getImap(), $mailbox.$testFolder);

    $countMessages5 = $imap->countMessages();
    if($countMessages5 != 0){
        echo 'Mail(s) is send'.PHP_EOL;
    }else{
        throw new ImapClientException('Mail(s) are not sent');
    };

    $infos = $imap->getBriefInfoMessages();
    $countMessages1 = count($infos);
    $countMessages2 = $imap->countMessages();

    for($i=1; $i<=$countMessages2; $i++) {
        $uid = $imap->getUid(1);
        $imap->deleteMessage($uid);
    };

    $countMessages3 = $imap->countMessages();
    if($countMessages3 == 0){
        echo 'The mailbox is cleared.'.PHP_EOL;
    }else{
        throw new ImapClientException('The mailbox is not cleaned');
    };
    echo 'Test_3 OK'.PHP_EOL;

    /*
     * Test_4
     * Send email in current mailbox and get it
     */
    MessagesPool::clean();
    MessagesPool::push(new SimpleMessage());
    MessagesPool::send($imap->getImap(), $mailbox.$testFolder);
    $imap->getMessage(1);
    Check::incomingMessage($imap->incomingMessage);
    $id = $imap->getUid('1');
    $imap->deleteMessage($id);
    MessagesPool::clean();
    echo 'Test_4 OK'.PHP_EOL;

    /*
     * Test_5
     * Check getMessages()
     */
    MessagesPool::clean();
    MessagesPool::push(new SimpleMessage());
    MessagesPool::push(new SimpleMessage());
    MessagesPool::send($imap->getImap(), $mailbox.$testFolder);
    $array = $imap->getMessages();
    if(!is_array($array)){
        throw new ImapClientException('getMessages() returns not an array');
    };
    if(count($array) != 2){
        throw new ImapClientException('getMessages() returns an unset number of messages');
    };
    $id = $imap->getUid('1');
    $imap->deleteMessage($id);
    $id = $imap->getUid('1');
    $imap->deleteMessage($id);
    MessagesPool::clean();
    echo 'Test_5 OK'.PHP_EOL;

    /*
     * Test_6
     * Check attachment
     */
    MessagesPool::push(new TestMessage1());
    MessagesPool::send($imap->getImap(), $mailbox.$testFolder);
    $imap->getMessage(1);
    var_dump($imap->incomingMessage);
    Check::incomingMessage($imap->incomingMessage);
    $count = count($imap->incomingMessage->attachments);
    if($count == 0){
        throw new ImapClientException('Attachments was not found');
    };
    $id = $imap->getUid('1');
    $imap->deleteMessage($id);
    MessagesPool::clean();
    echo 'Test_6 OK'.PHP_EOL;

}catch (ImapClientException $error){
    echo $error->getInfo();
};
