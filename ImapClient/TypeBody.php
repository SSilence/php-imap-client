<?php
/**
 * Copyright (C) 2016-2017  SSilence
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

namespace SSilence\ImapClient;

/**
 * Class TypeBody that holds the possible body types for an email.
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class TypeBody
{
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
    public static function get()
    {
        return static::$types;
    }
}
