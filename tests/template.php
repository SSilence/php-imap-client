<?php

namespace program;

require_once "../ImapClient/ImapClientException.php";
require_once "../ImapClient/ImapConnect.php";
require_once "../ImapClient/ImapClient.php";
require_once "../ImapClient/IncomingMessage.php";
require_once "../ImapClient/TypeAttachments.php";
require_once "../ImapClient/TypeBody.php";
require_once "../ImapClient/OutgoingMessage.php";
require_once "../ImapClient/Helper.php";

use SSilence\ImapClient\Helper;
use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient;
use SSilence\ImapClient\OutgoingMessage;

try{

    $imap = new ImapClient([
        'flags' => [
            'service' => ImapConnect::SERVICE_IMAP,
            'encrypt' => ImapConnect::ENCRYPT_SSL,
            'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
        ],
        'mailbox' => [
            'remote_system_name' => '',
            'mailbox_name' => '',
        ],
        'connect' => [
            'username' => '',
            'password' => ''
        ]
    ]);



}catch (ImapClientException $error){
    echo $error->getInfo();
};
