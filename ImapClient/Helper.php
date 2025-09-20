<?php

namespace SSilence\ImapClient;

class Helper {
    const OUT_OBJECT = 'object';
    const OUT_ARRAY = 'array';

    /**
     * Preparing properties.
     *
     * Return object like this
     * ```php
     * $obj->to => 'to',
     * $obj->subject => 'subject',
     * $obj->message => null
     * # it is if incoming array not have 'message', like this ['subject'=>'val', 'to'=>'val']
     * ```
     *
     * @param array  $arrayCurrentPropertiesAndValues available properties like only ['subject'=>'val', 'message'=>'val']
     * @param array  $arrayAllowedProperties          all need properties [... 'to', 'subject', 'message' ...]
     * @param string $outType                         if Helper::OUT_OBJECT return object, if Helper::OUT_ARRAY return array.
     *
     * @return object|array
     */
    public static function preparingProperties($arrayCurrentPropertiesAndValues, $arrayAllowedProperties, $outType = self::OUT_OBJECT) {
        if ($outType === self::OUT_ARRAY) {
            $outArray = array();
            foreach ($arrayAllowedProperties as $property) {
                if (!isset($arrayCurrentPropertiesAndValues[$property])) {
                    $outArray[$property] = null;
                } else {
                    $outArray[$property] = $arrayCurrentPropertiesAndValues[$property];
                }
            }

            return $outArray;
        }
        if ($outType === self::OUT_OBJECT) {
            $obj = new HelperObject();
            foreach ($arrayAllowedProperties as $property) {
                if (!isset($arrayCurrentPropertiesAndValues[$property])) {
                    $obj->$property = null;
                } else {
                    $obj->$property = $arrayCurrentPropertiesAndValues[$property];
                }
            }

            return $obj;
        }
    }
}
