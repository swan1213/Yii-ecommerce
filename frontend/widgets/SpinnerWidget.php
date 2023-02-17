<?php

namespace frontend\widgets;

use yii\base\Widget;
use yii\helpers\Html;

class SpinnerWidget extends Widget
{

    /**
     * @var String
     */
    public $id = "";
    /**
     * @var String
     */
    private $spinnerClass = "be-spinner";
    /**
     * @var String
     */
    public $spinnerStyle = "display: none; transform: translateY(-50%);";
    /**
     * @var String
     */
    public $title = "";
    /**
     * @var String
     */
    public $extraClass = "";
    /**
     * @var String
     */
    public $extraStyle = "";
    /**
     * @var String
     */
    public $caption = "";
    /**
     * @var String
     */
    public $captionClass = "";
    /**
     * @var String
     */
    public $captionStyle = "";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initId();
        $this->renderSpinner();
    }

    /**
     * Initializes the widget options
     */
    protected function initId()
    {
        if (!isset($this->id) || empty($this->id)) {
            $this->id = 'spinner_' . $this->getId();
        }
    }

    /**
     * Registers the needed assets
     */
    public function renderSpinner()
    {
        if ( !empty(trim($this->title)) ) {
            echo "\n" . Html::tag('div', trim($this->title), ['class' => 'be-loader-modal-text']) . "\n";
        }

        if ( !empty($this->extraClass) ) {
            $this->spinnerClass .= " ". $this->extraClass;
        }

        echo "\n" . Html::beginTag('div', ['id'=> $this->id, 'class' => $this->spinnerClass, 'style' => $this->spinnerStyle]) . "\n";

        echo "\n" . Html::beginTag('svg', [ 'width'=>'40px', 'height'=>'40px', 'viewBox'=>'0 0 66 66', 'xmlns'=>'http://www.w3.org/2000/svg']) . "\n";
        echo Html::tag('circle', '',
            [
                'fill'=>'none',
                'stroke-width'=>'4',
                'stroke-linecap'=>'round',
                'cx'=>'33',
                'cy'=>'33',
                'r' => '30',
                'class' => 'circle'
            ]
        );
        echo "\n" . Html::endTag('svg') . "\n";

        if ( !empty($this->caption) ){
            echo "\n". Html::tag('span', $this->caption, ['class'=> $this->captionClass, 'style' => $this->captionStyle]) ."\n";
        }

        echo "\n" .Html::endTag('div') . "\n";
    }
}