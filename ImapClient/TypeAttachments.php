<?php

namespace SSilence\ImapClient;


class TypeAttachments
{
    public $types;

    public function __construct()
    {
        $this->types = ['JPEG', 'PNG', 'GIF', 'PDF', 'X-MPEG', 'MSWORD', 'OCTET-STREAM'];
    }

    public function get()
    {
        return $this->types;
    }

}