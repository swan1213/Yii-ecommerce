<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\FulfillmentList;
use common\models\Fulfillment;

$this->title = 'FulFillment Software Partners';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Fulfillment', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Software Partners', 'url' => ['/fulfillment/software']];
$this->params['breadcrumbs'][] = 'View All';

$user_id = Yii::$app->user->identity->id;
?>

<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
            echo Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]);
        ?>
    </ol>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table" >
            <div class="panel-heading">
                <div class="tools">
                    <a href="<?php //echo $new_basepath  ?>/export-user"><span class="icon mdi mdi-download"></span></span></a>
                    <span class="icon mdi mdi-more-vert"></span>
                </div>
            </div>
            <div class="panel-body">
                <table id="channels_view_table" class="table table-striped table-hover table-fw-widget">
                    <thead>

                        <tr>
                            <th>Software Partners</th>
                            <!--<th>Products in Fulfillment</th>
                            <th>Orders Pending in Fulfillment</th>!-->
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $connected_fulfillment = Fulfillment::find()->where(['user_id' => $user_id])->all();
                        $fulfillment_arr = array();
                        foreach($connected_fulfillment as $conn) {
                            $fulfillment_arr[] = $conn->fulfillment_list_id;
                        }
                        $fulfillment_arr = array_unique($fulfillment_arr);
                        $software_list =  FulfillmentList::findAll(['type' => FulfillmentList::FULFILLMENT_TYPE_SOFTWARE]);

                        foreach($software_list as $software) {
                            if($software->name!='Shipwire') {

                    ?>
                        <tr class="odd gradeX">
                            <td><?php echo $software->name; ?></td>
                            <td>
                                <a href="<?php echo $software->link; ?>">
                    <?php
                        if(in_array($software->id, $fulfillment_arr))
                            {echo 'Connected';}
                        else
                            { echo 'Get Connected'; }
                    ?>
                                </a></td>
                        </tr>
		            <?php
                            }
                        }
                    ?>
                            </tbody>
                </table>
            </div>
        </div>
    </div>
</div>