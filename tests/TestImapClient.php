<?php
/**
 * Created by PhpStorm.
 * User: Sergey144010
 * Date: 24.04.2017
 * Time: 20:54
 */

namespace SSilence\ImapClient\Tests;


use SSilence\ImapClient\ImapClient;

class TestImapClient extends ImapClient
{
    public function getImap()
    {
        return $this->imap;
    }

    public function getMailbox()
    {
        return $this->mailbox;
    }
}