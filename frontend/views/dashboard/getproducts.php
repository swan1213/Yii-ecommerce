<?php
if (isset($products_count) and ! empty($products_count)) {
    if ($products_count > 4) {
        ?> <a href="javascript:void(0)" class="icon-container carouseller__left carouseller__left1">
            <span class="icon"><span class="mdi mdi-arrow-left"></span></span>
        </a><?php }
    ?>

    <div class="carouseller__wrap"> 
        <div class="carouseller__list"> 
            <?php
            if (isset($products) and ! empty($products)) {
                foreach ($products as $key => $single) {
                    if (empty($single['product']))
                        continue;
                    ?>
                    <div class="car__3" data-value="<?php echo isset($single['price']) ? $single['price'] : $single['qty'] ?>">
                        <?php
                        if (isset($single['product']['productImages']) and ! empty($single['product']['productImages']) and isset($single['product']['productImages'][0]) and ! empty($single['product']['productImages'][0])) {
                            $src = $single['product']['productImages'][0]['link'];
                        } else {
                            $src = '/img/elliot-logo.svg';
                        }
                        ?>
                        <img src="<?php echo $src ?>" class="product-img-slide">
                        <?php ?>
                        <br>
                        <h5><a href="/product/view?id=<?php echo $single['product']['id'] ?>"><?php echo @$single['product']['name'] ?></a></h5>

                        <h6><?php echo $symbol . ' ' . @$single['product']['price'] ?></h6>

                    </div>

                    <?php
                }
            }
            ?>     </div>
    </div>
    <?php
    if ($products_count > 4) {
        ?>  <a href="javascript:void(0)" class="icon-container carouseller__right carouseller__right1">
            <span class="icon"><span class="mdi mdi-arrow-right"></span></span>
        </a>         <?php }
}
?>    