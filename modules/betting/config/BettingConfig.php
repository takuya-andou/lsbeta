<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 11.07.2016
 * Time: 14:37
 */
namespace app\modules\betting\config;

class BettingConfig{
    /**
     * bet events
     */
    const EVENT_CORNER = 1;
    const EVENT_GOAL = 2;
    const EVENT_YELLOW_CARD = 3;
    const EVENT_RED_CARD = 4;
    const EVENT_PENALTY = 5;
    const EVENT_FOUL = 6;
    const EVENT_MATCH_RESULT = 7;

    const TYPE_TOTAL = 1;
    const TYPE_HANDICAP = 2;
    const TYPE_INDIVIDUAL_TOTAL = 3;
    const TYPE_1X3 = 4;
    const TYPE_ASIAN_TOTAL = 5;
    const TYPE_ASIAN_HANDICAP = 6;

    const BET_WIN = 1;
    const BET_LOSS = 2;
    const BET_DRAW = 3;

    const MEMBER1 = 1;
    const MEMBER2 = 2;
    const NO_MEMBER = 0;

    const DRAW = 0;
    const WIN = 1;
    const WIN_OR_DRAW = 2;
    const NO_DRAW = 3;

    const LESSER_THAN = -1;
    const EQUAL = 0;
    const GREATER_THAN =1;

    const BET_NOT_IN_HISTORY = 0;
    const BET_IN_HISTORY = 1;

    public static $EVENTS = array(
        self::EVENT_CORNER => 'CORNER',
        self::EVENT_GOAL => 'GOAL',
        self::EVENT_YELLOW_CARD => 'YELLOW CARD',
        self::EVENT_RED_CARD => 'RED CARD',
        self::EVENT_PENALTY => 'PENALTY',
        self::EVENT_FOUL => 'FOUL',
        self::EVENT_MATCH_RESULT => 'MATCH RESULT',
    );

    public static $BEtoMS_ACCORDIANCES = [
        self::EVENT_CORNER => 1,
        self::EVENT_FOUL => 5,
        self::EVENT_YELLOW_CARD => 10,
        self::EVENT_RED_CARD => 11,
        self::EVENT_PENALTY => 17,
        self::EVENT_GOAL => 9,
        self::EVENT_MATCH_RESULT => null,
    ];

    public static $MEMBERS = [
        self::MEMBER1 => 'T1',
        self::MEMBER2 => 'T2',
        self::NO_MEMBER => null
    ];

    public static $MATCH_OUTCOMES = [
        self::DRAW => 'DRAW',
        self::WIN => 'WIN',
        self::WIN_OR_DRAW => 'WIN OR DRAW',
        self::NO_DRAW => 'NO DRAW'
    ];

    public static $TOTAL_SIGNS = [
        self::LESSER_THAN => 'TL',
        self::GREATER_THAN => 'TG',
        self::EQUAL => 'EQ'
    ];

    public static $HANDICAP_SIGNS = [
        self::LESSER_THAN => '-',
        self::GREATER_THAN => '+',
        self::EQUAL => ''
    ];

    /**
     * bet types
     */
    public static $TYPES = array(
        self::TYPE_TOTAL => 'total',
        self::TYPE_HANDICAP => 'handicap',
        self::TYPE_INDIVIDUAL_TOTAL => 'individual_total',
        self::TYPE_1X3 => '1x3',
        self::TYPE_ASIAN_TOTAL => 'asian_total',
        self::TYPE_ASIAN_HANDICAP => 'asian_handicap',
    );

}