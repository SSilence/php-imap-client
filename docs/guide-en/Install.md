# Installing
---
PHP-imap-client can be installed 2 ways. The first composer and the second manual
### 1) Composer
Add repositories and require in composer.json
```{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/SSilence/php-imap-client",
            "packagist": false
        }
    ],
    "require": {
        "SSilence/php-imap-client": "dev-master"
    }
}
```
and make composer.phar update
composer.phar require
 * package name: "SSilence/php-imap-client": "dev-master"

### 2) Manual
1) Download the files from github or the releases page
2) Extract the files into the folder you wish
20 In the file that will call methods add
```php
require('path/to/ImapClient.php');
```
You may then use connect etc
