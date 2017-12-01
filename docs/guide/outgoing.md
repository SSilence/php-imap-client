# Adapter for outgoing messages

The IMAP protocol is designed to read messages and does not support the sending of messages.
But if you want ImapClient to send messages, then use the AdapterForOutgoingMessage class.
To do this you will also need to install PHPMailer

## First step
The first step is to install php mailer. You can do so in two ways.

1) Composer
```php
composer require phpmailer/phpmailer
```
2) Minimal 
Read [PHPMailer's guide on a minimal installation](https://github.com/PHPMailer/PHPMailer#minimal-installation)

## Second step
Turn phpmailer into an adapter.
File AdapterForOutgoingMessage.php
To do this, we rewrite send() method. 
Note this is note safe yet and may be re written in the future.
```php
# Be sure to add a namespace
use \PHPMailer;

class AdapterForOutgoingMessage
{
# ... code ...

    public function send()
    {
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp1.example.com;smtp2.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = $this->config['connect']['username'];
        $mail->Password = $this->config['connect']['password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom(self::$options['fromEmail'], self::$options['fromEmailName']);
        $mail->addAddress(self::$options['toEmail'], self::$options['toEmailName']);
        $mail->addAttachment(self::$options['fileName']);
        $mail->isHTML(true);
        $mail->Subject = self::$options['subject'];
        $mail->Body    = self::$options['messageHtml'];
        $mail->AltBody = self::$options['messagePlain'];
        if(!$mail->send()) {
            throw new ImapClientException('Message could not be sent'.PHP_EOL.$mail->ErrorInfo);
        } else {
            # echo 'Message has been sent';
            return true;
        };
        return false;
    }

# ... code ...
}
```

## Third step
Now you can send messages like this:
```php

use SSilence\ImapClient\AdapterForOutgoingMessage;

try{

$imap = new ImapClient([
    'flags' => [ ... ],
    'mailbox' => [ ... ],
    'connect' => [
        'username' => 'user@gmail.com',
        'password' => 'password',
    ]
]);

# ... code ...

AdapterForOutgoingMessage::setOptions([
    'fromEmail' => 'from@gmail.com',
    'fromEmailName' => 'fromUser',
    'toEmail' => 'to@gmail.com',
    'toEmailName' => 'toUser',
    'fileName' => 'file',
    'subject' => 'subject',
    'messageHtml' => 'message html',
    'messagePlain' => 'message'
]);
$imap->sendMail();

# ... code ...

}catch (ImapClientException $error){
    echo $error->getInfo();
};
```