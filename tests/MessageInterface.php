<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 25.04.2017
 * Time: 0:55
 */

namespace SSilence\ImapClient\Tests;


interface MessageInterface
{
    public function send($stream, $folder);
}