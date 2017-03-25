<?php

namespace SSilence\ImapClient;


class TypeBody
{
    public $types;

    public function __construct()
    {
        /*
         * Allowed types message body
         */
        $this->types = ['PLAIN', 'HTML'];
    }

    /*
     * Get the allowed types.
     *
     * @return array
     */
    public function get()
    {
        return $this->types;
    }

}