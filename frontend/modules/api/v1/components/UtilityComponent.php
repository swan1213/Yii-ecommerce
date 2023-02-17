<?php

namespace frontend\modules\api\v1\components;

use yii\base\Component;

class UtilityComponent extends Component
{
    public static function array2xml($array, $xml = false){

        if($xml === false){
            $xml = new \SimpleXMLElement('<result/>');
        }

        foreach($array as $key => $value){
            if(is_array($value)){
                self::array2xml($value, $xml->addChild($key));
            } else {
                $xml->addChild($key, $value);
            }
        }

        return $xml->asXML();
    }
}