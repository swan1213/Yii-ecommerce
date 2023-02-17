<?php
namespace frontend\components;
use common\models\PermissionOther;
use common\models\User;
use common\models\UserConnection;
use common\models\UserPermission;
use Yii;
use common\models\PermissionSubmenu;
use common\models\PermissionMenu;
use common\models\Connection;

class CustomFunction {

    const trial_status_activate = 'activate';
    const trial_status_deactivate = 'deactivate';
    const plan_status_activate = 'activate';
    const plan_status_deactivate = 'deactivate';

    public static function checkSubscriptionPlan() {
        /* get current user login id */
        $user_id = Yii::$app->user->identity->id;

        /* get user data */
        $userdata = User::find()->where(['id' => $user_id])->one();

        $trial_period_status = $userdata->trial_period_status;
        $plan_status = $userdata->plan_status;
        if ($trial_period_status == CustomFunction::trial_status_deactivate && $plan_status == CustomFunction::plan_status_activate) {
            return 'true';
        } elseif ($trial_period_status == CustomFunction::trial_status_deactivate && $plan_status == CustomFunction::plan_status_deactivate) {
            return 'false';
        } elseif ($trial_period_status == CustomFunction::trial_status_activate && $plan_status == CustomFunction::plan_status_activate) {
            $userdata->trial_period_status = 'deactivate';
            $userdata->save(false);
            return 'true';
        } else {
            return 'true';
        }
    }

    public static function getEmailDomain($new_user_email) {

        $explode_email = substr(strrchr($new_user_email, "@"), 1);
        $explode_domain = explode('.', $explode_email);
        $new_domain = $explode_domain[0];

        return $new_domain;
    }

    public static function syncMailchimp($data) {

        $apiKey = 'e757203fbe9fb22101ff4019f1cbdfd6-us16';
        $listId = '587de2a787';

        $memberId = md5(strtolower($data['email']));
        $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

        $json = json_encode([
            'email_address' => $data['email'],
            'status'        => $data['status'] // "subscribed","unsubscribed","cleaned","pending"
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // var_dump($result);
        // die('here');
        return $httpCode;
    }

    public static function SendWelcomeEmail($new_user_email) {

        Yii::$app->mailer->compose('template',
            ['title' => 'Welcome Mail',
                'content' => 'Hi ' . $new_user_email . 'Thanks for Signing up',
                'server' =>Yii::$app->params['BASE_URL']])
            ->setFrom(['mail@helloiamelliot.com' => 'Elliot'])
            ->setTo($new_user_email)
            ->setSubject('Welcome Mail')
            ->send();
    }

    /**
    * delete directory and all files in it
    * @$dirname param: directory path
    */
    public static function delete_directory($dirname) {
        if (is_dir($dirname)) {
            $dir_handle = opendir($dirname);
        }
        
        if (empty($dir_handle)) {
            return false;
        }
        
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file)) {
                    unlink($dirname."/".$file);
                }
                
                else {
                    self::delete_directory($dirname.'/'.$file);
                }
            }
        }

        closedir($dir_handle);
        rmdir($dirname);

        return true;
    }

    public static function curlHttp($url, $params=null, $method = 'GET', $header = [], $encode_type = 0, $multi = false) {
        $opts = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        ];

        switch (strtoupper($method)) {
            case 'GET':
                if(empty($params)) {
                    $opts[CURLOPT_URL] = $url;
                } else {
                    if($encode_type == 0) {
                        $opts[CURLOPT_URL] = $url . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);    
                    } else {
                        $opts[CURLOPT_URL] = $url . '?' . json_encode($params);
                    }
                    
                }
                break;
            case 'POST':
                if($encode_type == 0) {
                    $params = $multi ? $params : http_build_query($params, '', '&', PHP_QUERY_RFC3986);
                } else {
                    $params = json_encode($params);
                }
                
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_POST]       = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new \Exception('Unsupported request method!');
        }

        set_time_limit(0);
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = utf8_encode(curl_exec($ch));
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Request error occurred:' . $error);
        }
        return $data;
    }

    // menu permission check
    public static function getPermissionMenuLabel($menu_ids) {
        $label = "";
        $items = explode(", ", $menu_ids);
        foreach ($items as $item) {
            $permission_menu = PermissionSubmenu::find()->Where(['id' => $item])->one();
            if (isset($permission_menu->parent->name) && !empty($permission_menu->parent->name) && isset($permission_menu->name) && !empty($permission_menu->name))
                $label = $label . $permission_menu->parent->name . '_' . $permission_menu->name . ', ';
        }
        if (strlen($label) > 0) $label = substr($label, 0, strlen($label) - 2);
        return $label;
    }

    // channel permission check
    public static function getPermissionChannelLabel($channel_ids) {
        $label = "";
        $items = explode(", ", $channel_ids);
        foreach ($items as $item) {
            $permission_channel = Connection::find()->Where(['id' => $item])->one();
            if ($permission_channel->type_id == 1)
                $label = $label . $permission_channel->name . ', ';
            else
                $label = $label . $permission_channel->getParent()->name . '_' . $permission_channel->name . ', ';
        }
        if (strlen($label) > 0) $label = substr($label, 0, strlen($label) - 2);
        return $label;
    }

    public static function checkPermissionMenu($menu_index) {
        if (Yii::$app->user->identity->level != User::USER_LEVEL_MERCHANT_USER)
            return true;
        $permission_id = Yii::$app->user->identity->permission_id;
        $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
        if (!empty($user_permission)) {
            $menu_ids = $user_permission->menu_permission;
            $items = explode(", ", $menu_ids);
            foreach ($items as $item) {
                if($item == $menu_index)
                    return true;
            }
        }
        return false;
    }

    public static function checkPermissionOther($other_index) {
        if (Yii::$app->user->identity->level != User::USER_LEVEL_MERCHANT_USER)
            return true;
        $others = PermissionOther::find()->where(['id' => $other_index])->one();
        if (empty($others))
            return false;
        $other_permission = $others->name;
        $permission_id = Yii::$app->user->identity->permission_id;
        $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
        if (!empty($user_permission)) {
            $other_ids = $user_permission->other_permission;
            $items = explode(", ", $other_ids);
            foreach ($items as $item) {
                if($item == $other_permission)
                    return true;
            }
        }
        return false;
    }

    // checking connected channel on Product
    public static function checkConnectedChannel( $user_id, $user_level ) {
        if ($user_level == User::USER_LEVEL_MERCHANT_USER){
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0)
                    return true;
                else
                    return false;
            }
        }
        else {
            $connected_channel_count = UserConnection::find()->where(['user_id' => $user_id])->available()->count();
            if ($connected_channel_count > 0)
                return true;
            else
                return false;
        }
    }

    // get connected channel list on Product
    public static function getConnectedChannel($user_id, $user_level) {

        if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
            $permission_id = Yii::$app->user->identity->permission_id;
            $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
            if (!empty($user_permission)) {
                $channel_ids = $user_permission->channel_permission;
                $items = explode(", ", $channel_ids);
                if (sizeof($items) > 0)
                {
                    $menu_channels = array();
                    $connected_channels = UserConnection::find()
                        ->where(['user_id' => Yii::$app->user->identity->parent_id])
                        ->andWhere(['and',
                            ['in', 'connection_id', $items],])->all();
                    foreach ($connected_channels as $connected_channel) {
                        $menu_item = array();
                        $menu_item['label'] = $connected_channel->getPublicName();
                        $menu_item['url'] = ['product/connected-products?user_connection_id=' . $connected_channel->id];
                        $menu_item['active'] = (Yii::$app->controller->action->uniqueId == 'product/connected-products' && Yii::$app->request->get()['user_connection_id'] == $connected_channel->id);
                        $menu_channels[] = $menu_item;
                    }
                    return $menu_channels;
                }
                else
                    return array();
            }
        }

        $connected_channel_count = UserConnection::find()->where(['user_id' => $user_id])->available()->count();
        if ($connected_channel_count > 0) {
            $menu_channels = array();
            $connected_channels = UserConnection::find()->where(['user_id' => $user_id])->available()->all();
            foreach ($connected_channels as $connected_channel) {
                $menu_item = array();
                $menu_item['label'] = $connected_channel->getPublicName();
                $menu_item['url'] = ['product/connected-products?user_connection_id=' . $connected_channel->id];
                $menu_item['active'] = (Yii::$app->controller->action->uniqueId == 'product/connected-products' && Yii::$app->request->get()['user_connection_id'] == $connected_channel->id);
                $menu_channels[] = $menu_item;
            }
            return $menu_channels;
        }
        else
            return array();
    }

    // checking created permission
    public static function checkCreatedPermission( $user_id ) {
        $count = UserPermission::find()->where(['user_id' => $user_id])->count();
        if ($count > 0) {
            return true;
        }
        return false;
    }

    public static function getBaseUrl($request, $userDomain, $baseDomain){
        preg_match("/^(?<protocol>(http|https):\/\/)(((?<subdomain>[a-z]+)\.)*)((.*\.)*(?<domain>.+\.[a-z]+))$/", $request->hostInfo, $matches);

        return $matches['protocol'] . $userDomain . $baseDomain;

    }
}
