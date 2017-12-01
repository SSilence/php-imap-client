<?php

namespace program;

require_once "../autoload.php";

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
