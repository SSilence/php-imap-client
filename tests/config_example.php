<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 24.04.2017
 * Time: 21:01
 */
use SSilence\ImapClient\ImapConnect;

return [
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.com',
        'mailbox_name' => 'INBOX',
    ],
    'connect' => [
        'username' => '',
        'password' => '',
    ]
];