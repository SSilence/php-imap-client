<?php

namespace SSilence\ImapClient;


class TypeAttachments
{
    public $types;

    public function __construct()
    {
        $this->types = ['JPEG', 'PNG', 'PDF', 'X-MPEG'];
    }

    public function get()
    {
        return $this->types;
    }

}