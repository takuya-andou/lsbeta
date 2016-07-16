<?php
namespace app\components\helpers;

use yii\helpers\Html as HtmlBase;

class Html extends HtmlBase{

    public static function getListOfMatches($matches) {

        $items = [];
        foreach( $matches as $match ) {
            $items[] = self::getMatchLine($match);
        }

        return self::ul($items);
    }

    public static function getMatchLine($match) {
        return $match->date . " [{$match->id}] " . $match->home->name . " - " . $match->away->name;
    }
}