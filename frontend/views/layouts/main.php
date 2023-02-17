<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->params['content-class'] = array_key_exists('content-class', $this->params) ?
        $this->params['content-class'] : null;
?>
<?php
    $this->beginContent('@frontend/views/layouts/common.php');


    echo Html::beginTag('div', [
        'class' => 'main-content container-fluid' . implode(' ', [
                ArrayHelper::getValue($this->params, 'content-class'),
            ])
    ]);

    echo $content;

    echo Html::endTag('div');


    $this->endContent();

?>

