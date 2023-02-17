<?php
namespace frontend\components;


class Helpers {

    const menu_people = "People";
    const menu_people_view_all = "People_View_All";
    const menu_people_add_new = "People_Add_New";
    const menu_product = "Product";
    const menu_product_view_all = "Product_View_All";
    const menu_product_add_new = "Product_Add_New";
    const menu_product_attribute = "Product_Attribute";
    const menu_product_type = "Product_Type";
    const menu_product_category = "Product_Category";
    const menu_product_variation = "Product_Variation";
    const menu_product_variation_set = "Product_Variation_Set";
    const menu_order = "Order";
    const menu_order_view_all = "Order_View_All";
    const menu_content = "Content";
    const menu_content_view_all = "Content_View_All";
    const menu_content_add_new = "Content_Add_New";

    const other_product_edit = "other_product_edit";
    const other_assign_channel = "other_assign_channel";
    const other_connected_channel = "Other_Connected_Channel";
    const other_translation = "other_translation";
    const other_fulfillment = "other_fulfilment";
    const other_currency = "other_currency";

    const role_merchant_user = "merchant_user";

    public static function isExistSubMenuItem($input, $key, $role = self::role_merchant_user) {
        if ($role != self::role_merchant_user)
            return true;
        if (strlen($input) == 0)
            return false;
        $items = explode(", ", $input);
        foreach ($items as $item) {
            if ($item == $key)
            {
                return true;
            }
        }
        return false;
    }

    public static function isExistTopMenuItem($input, $key, $role = self::role_merchant_user) {
        if ($role != self::role_merchant_user)
            return true;
        if (strlen($input) == 0)
            return false;
        $items = explode(", ", $input);
        foreach ($items as $item) {
            if (strpos($item, $key) === false)
            {
            }
            else {
                return true;
            }
        }
        return false;
    }

    public static function isMenuHeaderItem($input, $item) {
        if ($input === $item)
            return true;
        return false;
    }

    public static function isMenuBodyItem($input) {
        if (strpos($input, "_") !== false)
            return true;
        return false;
    }

    public static function isExistTopChannelItem($input, $key) {
        if (strlen($input) == 0)
            return true;
        $items = explode(", ", $input);
        foreach ($items as $item) {
            if (strpos($item, $key) === false)
            {
            }
            else {
                return true;
            }
        }
        return false;
    }

    public static function isExistSubChannelItem($input, $key) {
        if (strlen($input) == 0)
            return true;
        $items = explode(", ", $input);
        foreach ($items as $item) {
            if ($item == $key)
            {
                return true;
            }
        }
        return false;
    }

    public static function isExistSubChannelItemForRole($input, $key) {
        if (strlen($input) == 0)
            return false;
        $items = explode(", ", $input);
        foreach ($items as $item) {
            if ($item == $key)
            {
                return true;
            }
        }
        return false;
    }

    public static function isExistSubOtherItemForRole($input, $key) {
        if (strlen($input) == 0)
            return false;
        $items = explode(", ", $input);
        foreach ($items as $item) {
            if ($item == $key)
            {
                return true;
            }
        }
        return false;
    }

    public static function removeStr($input, $key = "channel_") {
        return str_replace($key, "", $input);
    }
}