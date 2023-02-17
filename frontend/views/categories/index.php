<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\Category;
use common\models\ProductCategory;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Categories';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['/categories']];
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
    <?= Html::a('Add Category', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<div class="row">
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-divider">Dynamic Navigation<span class="panel-subtitle">Drag and drop menu items for easier management of your information architecture</span></div>


        <div class="panel-body">
            <div id="list2" class="dd">
                <ol class="dd-list">
                    <?php
                    foreach ($categories as $category):
                        //get all categoreis
                        $count_cat_product = ProductCategory::find()->Where(['category_id' => $category->id])->count();
                        $parent_cat = $category->parent_id;
                        $cat_id = $category->id;
                        ?>

                        <li data-id="<?= $category->id; ?>" class="dd-item dd3-item as">
                            <!--Main Li diaplay if Parent id Is 0 -->
                            <?php if ($parent_cat == 0): ?>
                                <div class="dd-handle dd3-handle" data-id="<?php $category->id; ?>"></div>
                                <div class="dd3-content div-dd3-content">
                                    <div class="col-md-8 span_cat_name_nested"><span class="span-nested-display"><?= $category->name; ?></span></div>
                                    <div class="col-md-3 span_product_nested"><span class="span-nested-display"><?= $count_cat_product; ?> Product </span></div>
                                    <a href="categories/update?id=<?= $category->id;  ?>"><div class="col-md-1 span_edit_nested mdi mdi-edit"></div></a>
                                </div>
                            <?php endif; ?>
                            <!--Check If Chil Is Exist Level1 !-->
                            <?php
                            $parent_data1 = Category::find()->Where(['parent_id' => $cat_id])->orderBy(['name' => SORT_ASC])->all();
                            foreach ($parent_data1 as $pn1):
                                $pn_pid1 = $pn1->id;
                                $count_cat_product = ProductCategory::find()->Where(['category_id' => $pn1->id])->count();
                                if (!empty($pn1) || $pn_pid1 !== 0) :
                                    ?>  
                                    <ol class="dd-list">
                                        <li data-id="<?= $pn1->id; ?>" class="dd-item dd3-item as">
                                            <div class="dd-handle dd3-handle" data-id="<?= $pn1->id; ?>"></div>
                                            <div class="dd3-content div-dd3-content">
                                                <div class="col-md-8 span_cat_name_nested"><span class="span-nested-display"><?= $pn1->name; ?></span></div>
                                                <div class="col-md-3 span_product_nested"><span class="span-nested-display"><?= $count_cat_product; ?> Product </span></div>
                                                <a href="categories/update?id=<?= $pn1->id; ?>"><div class="col-md-1 span_edit_nested mdi mdi-edit"></div></a>
                                            </div>

                                            <?php
                                            //Check level 2
                                            $parent_data2 = Category::find()->Where(['parent_id' => $pn_pid1])->orderBy(['name' => SORT_ASC])->all();

                                            foreach ($parent_data2 as $pn2):
                                                $pn_pid2 = $pn2->id;
                                                $count_cat_product = ProductCategory::find()->Where(['category_id' => $pn2->id])->count();
                                                if (!empty($pn2) || $pn_pid2 !== 0) :
                                                    ?>  
                                                    <ol class="dd-list">
                                                        <li data-id="<?= $pn2->id; ?>" class="dd-item dd3-item as">
                                                            <div class="dd-handle dd3-handle" data-id="<?= $pn2->id; ?>"></div>
                                                            <div class="dd3-content div-dd3-content">
                                                                <div class="col-md-8 span_cat_name_nested"><span class="span-nested-display"><?= $pn2->name; ?></span></div>
                                                                <div class="col-md-3 span_product_nested"><span class="span-nested-display"><?= $count_cat_product; ?> Product </span></div>
                                                                <a href="categories/update?id=<?= $pn2->id; ?>"><div class="col-md-1 span_edit_nested mdi mdi-edit"></div></a>
                                                            </div>
                                                            <?php 
                                                            //Check Level 3
                                                                $parent_data3 = Category::find()->Where(['parent_id' => $pn_pid2])->orderBy(['name' => SORT_ASC])->all();
                                                                foreach ($parent_data3 as $pn3):
                                                                $pn_pid3 = $pn3->id;
                                                                    $count_cat_product = ProductCategory::find()->Where(['category_id' => $pn3->id])->count();
                                                                if (!empty($pn3) || $pn_pid3 !== 0) :
                                                            ?>  
                                                            <ol class="dd-list">
                                                                <li data-id="<?= $pn3->id; ?>" class="dd-item dd3-item as">
                                                                    <div class="dd-handle dd3-handle" data-id="<?= $pn3->id; ?>"></div>
                                                                    <div class="dd3-content div-dd3-content">
                                                                        <div class="col-md-8 span_cat_name_nested"><span class="span-nested-display"><?= $pn3->name; ?></span></div>
                                                                        <div class="col-md-3 span_product_nested"><span class="span-nested-display"><?= $count_cat_product; ?> Product </span></div>
                                                                        <a href="categories/update?id=<?= $pn3->id; ?>"><div class="col-md-1 span_edit_nested mdi mdi-edit"></div></a>
                                                                    </div>
                                                            <?php
                                                            //Check Level 4
                                                                $parent_data4 = Category::find()->Where(['parent_id' => $pn_pid3])->orderBy(['name' => SORT_ASC])->all();
                                                                foreach ($parent_data4 as $pn4):
                                                                $pn_pid4 = $pn4->id;
                                                                $count_cat_product = ProductCategory::find()->Where(['category_id' => $pn4->id])->count();
                                                                if (!empty($pn4) || $pn_pid4 !== 0) :
                                                            ?>
                                                                <ol class="dd-list">
                                                                    <li data-id="<?= $pn4->id; ?>" class="dd-item dd3-item as">
                                                                        <div class="dd-handle dd3-handle" data-id="<?= $pn4->id; ?>"></div>
                                                                        <div class="dd3-content div-dd3-content">
                                                                            <div class="col-md-8 span_cat_name_nested"><span class="span-nested-display"><?= $pn4->name; ?></span></div>
                                                                            <div class="col-md-3 span_product_nested"><span class="span-nested-display"><?= $count_cat_product; ?> Product </span></div>
                                                                            <a href="categories/update?id=<?= $pn4->id; ?>"><div class="col-md-1 span_edit_nested mdi mdi-edit"></div></a>
                                                                        </div>
                                                                    </li>
                                                                </ol>  
                                                            <!--End Level 4 foreach!-->
                                                            <?php endif; endforeach; ?>
                                                        <!--End Level 3 li ol !-->
                                                               </li>
                                                            </ol>
                                                            <!--End Level 3 foreach!-->
                                                            <?php endif; endforeach; ?>
                                                    <!--End Level 2 li ol !-->
                                                        </li>
                                                    </ol>
                                                    <!--End Level 2 foreach!-->
                                                <?php endif; endforeach; ?>
                                    <!--End Level 1 li ol !-->
                                         </li>
                                    </ol>
                                    <!--End Level 1 foreach!-->
                                <?php endif;
                            endforeach; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <div class="xs-mt-30" style="display: none">
                <h4>Serialized Output:</h4>
                <pre><code id="out2"></code></pre>
            </div>
        </div>
    </div>
</div>





  