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
 * Helper class the internals of php-imap-client
 *
 * @package    SSilence\ImapClient
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>, sergey144010
 */

class Helper
{
    const OUT_OBJECT = 'object';
    const OUT_ARRAY = 'array';

    /**
     * Preparing properties
     *
     * Return object like this
     * ```php
     * $obj->to => 'to',
     * $obj->subject => 'subject',
     * $obj->message => null
     * # it is if incoming array not have 'message', like this ['subject'=>'val', 'to'=>'val']
     * ```
     *
     * @param array $arrayCurrentPropertiesAndValues available properties like only ['subject'=>'val', 'message'=>'val']
     * @param array $arrayAllowedProperties all need properties [... 'to', 'subject', 'message' ...]
     * @param string $outType if Helper::OUT_OBJECT return object, if Helper::OUT_ARRAY return array.
     * @return object|array
     */
    public static function preparingProperties($arrayCurrentPropertiesAndValues, $arrayAllowedProperties, $outType = self::OUT_OBJECT)
    {
        if($outType == self::OUT_ARRAY){
            $outArray = [];
            foreach ($arrayAllowedProperties as $property) {
                if(!isset($arrayCurrentPropertiesAndValues[$property])){
                    $outArray[$property] = null;
                }else{
                    $outArray[$property] = $arrayCurrentPropertiesAndValues[$property];
                };
            };
            return $outArray;
        };
        if($outType == self::OUT_OBJECT){
            $obj = new \stdClass();
            foreach ($arrayAllowedProperties as $property) {
                if(!isset($arrayCurrentPropertiesAndValues[$property])){
                    $obj->$property = null;
                }else{
                    $obj->$property = $arrayCurrentPropertiesAndValues[$property];
                };
            };
            return $obj;
        };
        return null;
    }
}