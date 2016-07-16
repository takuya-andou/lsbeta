<?php
namespace app\components\widgets\soccer;

use app\components\helpers\Html;
use app\modules\soccer\models\Match;

class LastMatches extends \yii\bootstrap\Widget
{

    /** @var Match[] $matches */
    public $matches = [];


    public function init() {
        parent::init();
        if (!empty($this->matches)) {}
    }

    /**
     * @return string
     */
    public function run() {
        return $this->render('views/lastmatches');
        return HTML::getListOfMatches($this->matches);
    }


}