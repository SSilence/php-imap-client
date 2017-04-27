<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 25.04.2017
 * Time: 1:25
 */

namespace SSilence\ImapClient\Tests;


class SimpleMessage extends Message
{
    public function __construct()
    {
        $this->body =
            "From: meS@example.com\r\n"
            ."To: youS@example.com\r\n"
            ."Subject: testS\r\n"
            ."\r\n"
            ."TestS message\r\n";
    }
}