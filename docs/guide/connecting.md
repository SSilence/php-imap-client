# Default connnection
A default connection is the easy and most uncommon way to connect to an imap sever. Use the code
below to use our default connection method.
```php

// Encryption
$imap = new ImapClient($mailbox, $username, $password, $encryption);
// No Encryption
$imap = new ImapClient($mailbox, $username, $password);
```
This code does not check for errors and assumeing everything is right. If you are using this in production be sure to check for errors
# Advanced Conecting
We also have ways to change how your connection works. Read the code and examples below to learn some ways to modifiy your connection,
```php
/* Example 1
 * Example 1 is the advanced default connection method
 */
$imap = new ImapClient([
    'flags' => [
        'service' => ImapConnect::SERVICE_IMAP,
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        /* This NOVALIDATE_CERT is used when the server connecting to the imap
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
        # Service can be ImapConnect::SERVICE_IMAP ,ImapConnect::SERVICE_POP3, ImapConnect::SERVICE_NNTP
        'service' => ImapConnect::SERVICE_IMAP,
        # Encrypt can be ImapConnect::ENCRYPT_SSL, ImapConnect::ENCRYPT_TLS, ImapConnect::ENCRYPT_NOTLS
        'encrypt' => ImapConnect::ENCRYPT_SSL,
        # validateCertificates can be ImapConnect::VALIDATE_CERT or ImapConnect::NOVALIDATE_CERT
        'validateCertificates' => ImapConnect::NOVALIDATE_CERT,
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
```
