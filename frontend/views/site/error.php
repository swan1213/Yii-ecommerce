<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<div class="be-error be-error-404">
    <div class="main-content container-fluid">
        <div class="error-container">
            <div class="error-number">404</div>
            <div class="error-description">The page you are looking for might have been removed.</div>
            <div class="error-goback-text">Would you like to go home?</div>
            <div class="error-goback-button"><a href="/" class="btn btn-xl btn-primary">Let's go home</a></div>
            <div class="footer">&copy; <?php echo date('Y'). "&nbsp;" . env('APP_NAME')?></div>
        </div>
    </div>

</div>