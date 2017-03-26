<?php

namespace SSilence\ImapClient;


class Helper
{
    /*
     * Preparing properties
     *
     * Return object like this
     * $obj->to => 'to',
     * $obj->subject => 'subject',
     * $obj->message => null , if incoming array not have 'message', like this ['subject'=>'val', 'to'=>'val']
     *
     * @param array $arrayCurrentPropertiesAndValues. Available properties like only ['subject'=>'val', 'message'=>'val']
     * @param array $arrayAllowedProperties. All need properties [... 'to', 'subject', 'message' ...]
     * @return obj
     */
    public static function preparingProperties($arrayCurrentPropertiesAndValues, $arrayAllowedProperties)
    {
        $obj = new \stdClass();
        foreach ($arrayAllowedProperties as $property) {
            if(!isset($arrayCurrentPropertiesAndValues[$property])){
                $obj->$property = null;
            }else{
                $obj->$property = $arrayCurrentPropertiesAndValues[$property];
            };
        };
        return $obj;
    }
}