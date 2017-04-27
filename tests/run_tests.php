<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 24.04.2017
 * Time: 20:55
 */

namespace tests;


ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once "../autoload.php";
require_once "TestImapClient.php";
require_once "ObjectPool.php";
require_once "MessageInterface.php";
require_once "Message.php";
require_once "SimpleMessage.php";
require_once "TestMessage1.php";
$conf = require_once "config.php";

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
use SSilence\ImapClient\Tests\TestMessage1;
use SSilence\ImapClient\TypeAttachments;
use SSilence\ImapClient\TypeBody;

use SSilence\ImapClient\Tests\TestImapClient;
use SSilence\ImapClient\Tests\ObjectPool;
use SSilence\ImapClient\Tests\SimpleMessage;


try{

    $imap = new TestImapClient($conf);

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

    $mailbox = $imap->getMailbox();
    #ObjectPool::push(new SimpleMessage());
    ObjectPool::push(new TestMessage1());
    ObjectPool::send($imap->getImap(), $mailbox.$testFolder);

    #$messages = $imap->getMessages();
    /*
    $count = $imap->countMessages();
    for($i=1; $i<=$count; $i++){
        imap_delete($imap->getImap(), $i);
    };
*/
    #var_dump($messages);
    #$json = json_encode($messages, JSON_PRETTY_PRINT);
    #var_dump($json);

}catch (ImapClientException $error){
    echo $error->getInfo();
};
