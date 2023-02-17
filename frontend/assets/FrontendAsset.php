<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;
use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\web\JqueryAsset;
use common\assets\Html5shiv;
use yii\jui\JuiAsset;
use dungang\touchswipe\TouchSwipeAsset;
use devgroup\dropzone\DropZoneAsset;
use fedemotta\datatables\DataTablesAsset;
use fedemotta\datatables\DataTablesBootstrapAsset;
use common\assets\FontAwesome;
/**
 * Frontend application asset
 */
class FrontendAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $basePath = '@webroot';
    /**
     * @var string
     */
    public $baseUrl = '@web';


//    public $css = [
//        'lib/font-awesome/css/font-awesome.min.css',
//        'css/flag-icon.css',
//        'css/docs.css',
//        'css/sf_express/style.css',
//    ];


    /**
     * @var array
     */
    public $css = [
        'lib/perfect-scrollbar/css/perfect-scrollbar.min.css',
        'css/summernote.css',
        'lib/material-design-icons/css/material-design-iconic-font.min.css',
        'lib/datatables/css/dataTables.bootstrap.min.css',
        'lib/jquery.vectormap/jquery-jvectormap-1.2.2.css',
        'lib/jqvmap/jqvmap.min.css',
        'lib/datetimepicker/css/bootstrap-datetimepicker.min.css',
        'lib/daterangepicker/css/daterangepicker.css',
        'lib/x-editable/bootstrap3-editable/css/bootstrap-editable.css',
        'lib/x-editable/inputs-ext/typeaheadjs/lib/typeahead.js-bootstrap.css',
        'lib/select2/css/select2.min.css',
        'lib/bootstrap-slider/css/bootstrap-slider.css',
        'lib/jquery.gritter/css/jquery.gritter.css',
        'lib/morrisjs/morris.css',
        'lib/bootstrap-multiselect/css/bootstrap-multiselect.css',
        'lib/multiselect/css/multi-select.css',
        'css/carouseller.css',
        //'css/material-design-iconic-font.min.css',
        //'css/perfect-scrollbar.min.css',
        //'css/elliot_style.min.css',
        'css/elliot_style.css',
        'css/product/style.css',
        'css/common.css',
        'css/tree-view.css',
        'css/flag-icon.css',
    ];

//    public $js = [
//        'js/user/user_role.js',
//        'js/ship.js',
//        'js/docs.js',
//        'js/tmall.js',
//        'js/aliexpress.js',
//        'js/wechat.js',
//        'js/tpl-central.js',
//        'js/sf-express.js',
//        'js/global-search.js',
//    ];


    /**
     * @var array
     */
    public $js = [
        'lib/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js',
        'js/main.js',
        'lib/x-editable/bootstrap3-editable/js/bootstrap-editable.min.js',
        'lib/x-editable/inputs-ext/typeaheadjs/typeaheadjs.js',
        'lib/x-editable/inputs-ext/typeaheadjs/lib/typeahead.js',
        'lib/moment.js/min/moment.min.js',
        'lib/fuelux/js/wizard.js',
        'lib/select2/js/select2.min.js',
        'lib/select2/js/select2.full.min.js',
        'lib/bootstrap-slider/js/bootstrap-slider.js',
        'js/app-form-wizard.js',
        'lib/datatables/js/jquery.dataTables.min.js',
        'lib/datatables/js/dataTables.bootstrap.min.js',
        'lib/datatables/plugins/buttons/js/dataTables.buttons.js',
        'lib/datatables/plugins/buttons/js/buttons.html5.js',
        'lib/datatables/plugins/buttons/js/buttons.flash.js',
        'lib/datatables/plugins/buttons/js/buttons.print.js',
        'lib/datatables/plugins/buttons/js/buttons.colVis.js',
        'lib/datatables/plugins/buttons/js/buttons.bootstrap.js',
        'lib/jquery-flot/jquery.flot.js',
        'lib/jquery-flot/jquery.flot.pie.js',
        'lib/jquery-flot/jquery.flot.resize.js',
        'lib/jquery-flot/plugins/jquery.flot.orderBars.js',
        'lib/jquery-flot/plugins/curvedLines.js',
        'lib/countup/countUp.min.js',
        //'lib/jquery-ui/jquery-ui.min.js',
        'lib/jqvmap/jquery.vmap.min.js',
        'lib/jqvmap/maps/jquery.vmap.world.js',
        'lib/jquery.gritter/js/jquery.gritter.js',
        'lib/datetimepicker/js/bootstrap-datetimepicker.min.js',
        'lib/daterangepicker/js/daterangepicker.js',
        'lib/summernote/summernote.min.js',
        'lib/summernote/summernote-ext-beagle.js',
        'lib/bootstrap-markdown/js/bootstrap-markdown.js',
        'lib/markdown-js/markdown.js',
        'lib/jquery.nestable/jquery.nestable.js',
        'lib/jquery.maskedinput/jquery.maskedinput.min.js',
        'lib/bootstrap-multiselect/js/bootstrap-multiselect.js',
        'lib/multiselect/js/jquery.multi-select.js',
        'lib/raphael/raphael-min.js',
        'lib/morrisjs/morris.min.js',
        'lib/chartjs/Chart.js',
        'lib/jquery.sparkline/jquery.sparkline.min.js',

        'js/flot-categories.js',
        //'js/perfect-scrollbar.jquery.min.js',
        //'js/extra/summernote.min.js',
        //'js/extra/summernote-ext-beagle.js',
        //'js/extra/bootstrap-markdown/bootstrap-markdown.js',
        //'js/extra/markdown-js/markdown.js',
        'js/drag-arrange.min.js',

        //'js/app.js',
        'js/app-charts-sparkline.js',
        'js/app-ui-notifications.js',
        'js/app-form-wysiwyg.js',
        'js/app-form-elements.js',
        'js/app-form-multiselect.js',
        'js/app-ui-nestable-lists.js',
        'js/app-form-masks.js',
        'js/app-dashboard.js',

        'js/tree-view.js',
        'js/m_treeview.js',
        'js/channels/facebook.js',

        'js/app-form-editable.js',
        'js/app-tables-datatables.js',
        'js/user/user_permission.js',

        'js/product/product.js',
        'js/product/view.js',
        'js/corporate/corporate.js',
        'js/category/category.js',
        'js/general/general.js',
        'js/attribution/attribution.js',
        'js/profile/profile.js',
        'js/attribution/attribution.js',
        'js/sfexpress/sf-express.js',
        'js/common.js',
        'js/connection/channels.js',
        'js/connection/channel_setting.js',
        'js/connection/smartling.js',
        'js/tpl/tpl-central.js',
        'js/people/people.js',
        'js/order/orders.js',
        'js/general.js',
        'js/custom_graph.js',
        'js/custom.js',
        'js/carouseller.min.js',
        'js/store/magento.js',
        'js/store/magento2.js',

    ];

    /**
     * @var array
     */
    public $depends = [
        JqueryAsset::class,
        YiiAsset::class,
        JuiAsset::class,
        TouchSwipeAsset::class,
        Html5shiv::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        DropZoneAsset::class,
    ];
}
