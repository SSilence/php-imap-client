<?php
namespace SSilence\ImapClient;

/**
 * Class TypeAttachments that holds attachment types.
 */
class TypeAttachments {
    /**
     * Types of attachments.
     *
     * @var array
     */
    private static $types = array('JPEG', 'JPG', 'PNG', 'GIF', 'PDF', 'X-MPEG', 'MSWORD', 'OCTET-STREAM', 'TXT', 'TEXT', 'MWORD', 'ZIP', 'MPEG', 'DBASE', 'ACROBAT', 'POWERPOINT', 'BMP', 'BITMAP');

    /**
     * Get the allowed types.
     */
    public static function get() {
        return static::$types;
    }
}
