<?php

use backend\models\LazadaCategories;

?>
<div id="list1" class="dd">
    <ol class="dd-list">
    <?php foreach($lazada_data as $_lazada) {$chh_acc=trim(str_replace(" ","_",$_lazada->channel_accquired));?>
	<li data-id="1" class="dd-item">
	    <?php $child_check=LazadaCategories::find()->where(['channel_accquired'=>$_lazada->channel_accquired,'parent_id'=>$_lazada->channel_abb_id])->one();?>
	    <?php $cat_check= empty($child_check) ? 'child' : 'parent'; ?>
	    <?php $lazada_least= empty($child_check) ? 'lazada_least' : ''; ?>
	    <?php echo empty($child_check) ? '' : '<button data-action="expand" type="button">Expand</button>'; ?>
	    <div class="dd-handle lazada_cat_class <?=$lazada_least;?>" for="<?=$chh_acc.$_lazada->channel_abb_id;?>" id="<?=$chh_acc.$_lazada->channel_abb_id?>" data-cat_check="<?=$cat_check;?>" data-open="no" data-lazada_cat="<?=$chh_acc;?>" data-id="<?=$chh_acc.$_lazada->channel_abb_id?>"><?=$_lazada->name;?></div>
	    <?php echo empty($child_check) ? '<input type="radio" class="display_none" name="'.$chh_acc.'_least_category" id="check'.$chh_acc.$_lazada->channel_abb_id.'" value="'.$_lazada->channel_abb_id.'">' :''; ?>
	</li>
    <?php } ?>
    </ol>
</div>