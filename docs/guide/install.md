# Installing
PHP-imap-client can be installed 2 ways. The first composer and the second manual
### 1) Composer
Untill Big o' 2.0 is ready, use the following command to install PHP-imap-library:    
`composer require ssilence/php-imap-client dev-master`
### 2) Manual
1) Download the files from github or the releases page    
2) Extract the files into the folder you wish    
3) In the file that will call methods add    
```php
require_once "path/to/ImapClientException.php";
require_once "path/to/ImapConnect.php";
require_once "path/to/ImapClient.php";
require_once "path/to/IncomingMessage.php";
require_once "path/to/TypeAttachments.php";
require_once "path/to/TypeBody.php";
```
You may then use connect etc
