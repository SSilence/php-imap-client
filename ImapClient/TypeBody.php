<?php

namespace SSilence\ImapClient;


class TypeBody
{
    public $types;

    public function __construct()
    {
        $this->types = ['PLAIN', 'HTML'];
    }

    public function get()
    {
        return $this->types;
    }
}