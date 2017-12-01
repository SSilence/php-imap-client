# Errors

Many errors may be thrown while using the library, if you cant seem to find what an error means or what you are doing wrong, take a look here.
Everything is structured like this:
#### [Error name]
[How error occurs]
[fix]
#### Imap function not available
PHP does not support connections to web servers via imap
To fix this download the php_imap.dll and enable it by putting the following text in php.ini `extension=php_gettext.dll`
#### Mailbox is not installed
No mail box was provided
Confirm that the $mailbox variable is filled when connecting
#### Mailbox must be a string
The mailbox provided to connect to the web server is not a string
We cannot connect to mail boxes that have a integer in them. Make sure the $mailbox variable provied is a string
#### Username must be a string
The username provided to connect to the web server is not a string
Web servers dont use arrays as usernames!!! Make sure the $username variable is a string
#### Password must be a string
The password provided to connect to the web server is not a string
Confirm that the $password variable is a string
#### Options must be an integer
The options variable provided when connecting is not an integer
// Dont know. Someone checking pr tell me and ill fix
#### N_ must be an integer
The number of retries provided is not an integer
Make sure that the $N_retries variable is an integer
#### Params must be an array
The parameters provided to connect to the server are not an array
// Dont know. Someone checking pr tell me and ill fix
#### Error connecting to[insert your mailbox string here]
PHP-imap client had trouble connecting to the provided mailbox with the provided details
This can mean many things. It can mean your mailbox is invalid or your username and password are not valid. Comfirm your login details and make sure your mail server is online
#### Option connect must be installed
If you selected the advanced connection and not installed `connect` option like
```php
$imap = new ImapClient([
    'connect' => [
        'username' => 'user',
        'password' => 'pass',
    ]
]);
```
#### File must be specified for saveEmail()
You did not specify a file path
Insure your code looks like this:
```php
$imap->saveEmail($your_file_path_var, $your_email_id_var, $your_part_var)
```
#### Email id must be specified for saveEmail()
You did not specify an email id
Insure your code looks like this:
```php
$imap->saveEmail($your_file_path_var, $your_email_id_var, $your_part_var)
```
#### File must be a string for saveEmail()
The provided file path is not a string
Make sure your $file is a string *not* a open file
#### $id must be a integer for saveEmail()
The provided email id is an integer
Make sure your $id is an integer
#### What to use id or uid?
You did not let us know weather to use id or uuids
