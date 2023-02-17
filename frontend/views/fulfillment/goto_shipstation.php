<?php
$this->title = 'ShipStation';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .main-content{
        padding:0px;
    }
</style>
<div class="shipstation-index">
   <iframe src="https://ss6.shipstation.com/?var=<?php echo time(); ?>" class="ship-iframe" id="theframe" name="<?php echo time(); ?>">
   </iframe>

</div>
