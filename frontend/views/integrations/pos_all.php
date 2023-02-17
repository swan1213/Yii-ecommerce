<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use common\models\Integration;
use common\models\UserIntegration;
?>
<?php
    $this->title = 'All POS';
    $this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
    $this->params['breadcrumbs'][] = ['label' => 'Integrations', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
    $this->params['breadcrumbs'][] = ['label' => 'POS', 'url' => ['/integrations/pos-all']];
    $this->params['breadcrumbs'][] = 'View All';
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
                        <th>POS</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pos_connections as $pos_connection): ?>
                            <tr class="odd" role="row">
                                <td class="sorting_1">Square</td>
                                <td>
                                    <a href="<?= $pos_connection['link'] ?>">
                                        <?php echo $pos_connection['status']; ?>    
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>