# Default connnection
A default connection is the easy and most uncommon way to connect to an imap sever. Use the code
below to use our default connection method.
```php

// Encryption
$imap = new ImapClient($mailbox, $username, $password, $encryption);
// No Encryption
$imap = new ImapClient($mailbox, $username, $password);
```
This code does not check for errors and assumeing everything is right. If you are using this be sure to check for errors