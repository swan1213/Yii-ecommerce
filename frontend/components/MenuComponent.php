<?php

namespace frontend\components;

use common\models\FulfillmentList;
use yii\base\Component;
use common\models\Fulfillment;

class MenuComponent extends Component
{

    public static function FulfillmentSoftwareConnectLists($user_id){

        $menu_items = [];

        $connected_software = Fulfillment::findAll(['user_id' => $user_id]);
        if (!empty($connected_software)) {
            foreach ($connected_software as $software) {

                $fulfill_type = $software->fulfillmentList->type;
                if ( $fulfill_type == FulfillmentList::FULFILLMENT_TYPE_SOFTWARE ) {
                    $menu_items[] = [
                        'label' => $software->fulfillmentList->name,
                        'url' => [$software->fulfillmentList->link],
                        'active' => (\Yii::$app->controller->id == $software->fulfillmentList->link)
                    ];
                }
            }
        }

        return $menu_items;

    }

}