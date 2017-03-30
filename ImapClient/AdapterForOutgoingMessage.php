<?php

namespace SSilence\ImapClient;

use SSilence\ImapClient\ImapClientException;

/**
 * Adapter for outgoing messages
 *
 * Copyright (C) 2016-2017  SSilence
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package    protocols
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */

class AdapterForOutgoingMessage
{
    /*
     * Connect config
     */
    private $config;

    /*
     * Array ['to'=>'', 'subject'=>'' and other]
     */
    private static $options;

    public function __construct(array $connectConfig)
    {
        $this->config = $connectConfig;
    }

    public static function setOptions(array $options)
    {
        self::$options = $options;
    }

    /*
     * Example use OutgoingMessage class
     */
    /*
    public function send()
    {
        $outMessage = new OutgoingMessage();
        $outMessage->setFrom(self::$options['from']);
        $outMessage->setTo(self::$options['to']);
        $outMessage->setSubject(self::$options['subject']);
        $outMessage->setMessage(self::$options['message']);
        $outMessage->send();
    }
    */

    /*
     * Example for PhpMailer class
     */
    /*
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
     */

    public function send()
    {
        throw new ImapClientException('Not implemented');
    }
}