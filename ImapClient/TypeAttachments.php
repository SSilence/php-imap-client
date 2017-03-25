<?php

namespace SSilence\ImapClient;


class TypeAttachments
{
    public $types;

    public function __construct()
    {
        /*
         * Allowed types attachments
         */
        $this->types = ['JPEG', 'PNG', 'GIF', 'PDF', 'X-MPEG', 'MSWORD', 'OCTET-STREAM'];
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