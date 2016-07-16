<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 11.07.2016
 * Time: 14:35
 */
namespace app\modules\forecast\config;

use app\modules\betting\config\BettingConfig;

class ForecastConfig{


    const RESULT_PROCESSING_ERROR = -1;
    const RESULT_UNKNOWN = 0;
    const RESULT_WIN = BettingConfig::BET_WIN;
    const RESULT_LOSS = BettingConfig::BET_LOSS;
    const RESULT_DRAW = BettingConfig::BET_DRAW;
    const RESULT_CANCELED = 3;

    const MIN_SUMMARY_LEN = 20;
    const MAX_SUMMARY_LEN = 255;
    const MIN_CONTENT_LEN = 20;
    const MAX_TITLE_LEN = 50;

    const UPCOMING_MATCHES_RANGE = '10'; //in DAYS. is used when form is being filled as a default date range for upcoming matches

    const STATUS_TO_BE_MODERATED = 1;
    const STATUS_NOT_MODERATED_BUT_PUBLISHED = 2;//не модерировался но опубликован
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = 4;

}