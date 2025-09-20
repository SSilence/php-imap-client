<?php
namespace SSilence\ImapClient;

/**
 * Class TypeBody that holds the possible body types for an email.
 */
class TypeBody {
    /**
     * Types of body's.
     *
     * @var array
     */
    private static $types = array('PLAIN', 'HTML');

    /**
     * Get the allowed types.
     *
     * @return array
     */
    public static function get() {
        return static::$types;
    }
}
