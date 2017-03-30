<?php

namespace program;

require_once "../ImapClient/ImapClientException.php";
require_once "../ImapClient/ImapConnect.php";
require_once "../ImapClient/ImapClient.php";

use SSilence\ImapClient\ImapClientException;
use SSilence\ImapClient\ImapConnect;
use SSilence\ImapClient\ImapClient;

$mailbox = 'my.imapserver.com'; // Your iamp server address. If you have a specific port specify it here
$username = 'username'; // Your imap server user name 
$password = 'secret'; // Super secret passsword
$encryption = ImapClient::ENCRYPT_TLS; // or ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS or null

/*
 * Default connection
 * This is the default way to connect to an IMAP server using this
 */

// Encryption
$imap = new ImapClient($mailbox, $username, $password, $encryption);
// No Encryption
$imap = new ImapClient($mailbox, $username, $password);

/*
 * Advanced connect
 *
 * Options flags same like in ImapConnect::prepareFlags() method
 * Options mailbox same like in ImapConnect::prepareMailbox() method
 * Options connect same like in ImapConnect::connect() method
 */

/* Example 1
 * Example 1 is the advanced default connection method
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        /* This NOVALIDATE_CERT is used when the server connecting to the imao
         * servers is not https but the imap is. This ignores the failure.
         */
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
    ],
    'connect' => [
        'username' => 'user',
        'password' => 'pass'
    ]
]);

/* Example 2
 * Get debug messages
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        'validateCertificates' => ImapConnect::VALIDATE_CERT,
        // Turns debug on or off
        'debug' => ImapConnect::DEBUG,
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
        'port' => 123,
    ],
    'connect' => [
        'username' => 'user',
        'password' => 'pass'
    ]
]);

/* Example 3 
 * You can also set the config then connects
 */
ImapClient::setConnectAdvanced();
ImapClient::setConnectConfig([
    'flags' => [
        'service' => ImapConnect::SERVICE_POP3,
        'validateCertificates' => ImapConnect::VALIDATE_CERT,
        'debug' => ImapConnect::DEBUG,
    ],
]);
$imap = new ImapClient();

/* Example 5
 * Here you can see all the options
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP, # ImapConnect::SERVICE_IMAP ,ImapConnect::SERVICE_POP3, ImapConnect::SERVICE_NNTP
        'encrypt' => ImapConnect::ENCRYPT_SSL, # ImapConnect::ENCRYPT_SSL, ImapConnect::ENCRYPT_TLS, ImapConnect::ENCRYPT_NOTLS
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT, # ImapConnect::VALIDATE_CERT, ImapConnect::NOVALIDATE_CERT
        'secure' => ImapConnect::SECURE, # or null
        'norsh' => ImapConnect::NORSH, # or null
        'readonly' => ImapConnect::READONLY, # or null
        'anonymous' => ImapConnect::ANONYMOUS, # or null
        'debug' => ImapConnect::DEBUG # or null
    ],
    'mailbox' => [
        'remote_system_name' => 'imap.server.ru',
        'port' => null,
        'flags' => null,
        'mailbox_name' => null,
    ],
    'connect' => [
        'mailbox' => null,
        'username' => 'user',
        'password' => 'pass',
        'options' => 0,
        'n_retries' => 0,
        'params' => [],
    ]
]);