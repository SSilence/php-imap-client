<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 24.04.2017
 * Time: 20:55
 */

namespace program;


ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once "../autoload.php";
require_once "TestImapClient.php";
$conf = require_once "config.php";

use SSilence\ImapClient\Helper;
use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient;
use tests\TestImapClient;
use SSilence\ImapClient\OutgoingMessage;
use SSilence\ImapClient\AdapterForOutgoingMessage;
use SSilence\ImapClient\IncomingMessage;
use SSilence\ImapClient\IncomingMessageAttachment;
use SSilence\ImapClient\Section;
use SSilence\ImapClient\SubtypeBody;
use SSilence\ImapClient\TypeAttachments;
use SSilence\ImapClient\TypeBody;


try{
    $imap = new TestImapClient($conf);

}catch (ImapClientException $error){
    echo $error->getInfo();
};
