<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 27.04.2017
 * Time: 1:46
 */

namespace SSilence\ImapClient\Tests;


use SSilence\ImapClient\ImapClientException;

class Check
{
    public static function method($imap, $method)
    {
        if(!method_exists($imap, $method)){
            throw new ImapClientException('Method '.$method.' was not found');
        };
        echo 'Method '.$method.' found'.PHP_EOL;
    }

    public static function incomingMessage($incomingMessage)
    {
        if(!isset($incomingMessage)){
            throw new ImapClientException('incomingMessage not installed.');
        };
        if(!isset($incomingMessage->header)){
            throw new ImapClientException('incomingMessage->header not installed.');
        };
        if(!isset($incomingMessage->message)){
            throw new ImapClientException('incomingMessage->message not installed.');
        };
        if(!isset($incomingMessage->attachments)){
            throw new ImapClientException('incomingMessage->attachments not installed.');
        };
        if(!isset($incomingMessage->section)){
            throw new ImapClientException('incomingMessage->section not installed.');
        };
        if(!isset($incomingMessage->structure)){
            throw new ImapClientException('incomingMessage->structure not installed.');
        };
    }
}