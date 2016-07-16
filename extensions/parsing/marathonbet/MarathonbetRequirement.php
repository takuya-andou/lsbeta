<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 14.04.2016
 * Time: 16:51
 */

namespace app\extensions\parsing\marathonbet;

//TODO: after line is empty change replace types and events with ids
use app\modules\betting\config\BettingConfig;
use app\modules\betting\models\BetType;

class MarathonbetRequirement
{
    public static $config = array(
        'connection_rules' => array(
            'domain'    => 'https://www.marathonbet.com',
            'language'  => 'en',
            'proxy' => '188.166.237.140:8080',//'187.35.77.50:3128'//null,
        ),
        'common_rules' => array(
            'bookie_id' => '16',
        ),
        'parsing_rules' => array(
            'football' => array(
                'url' => 'popular/Football',
                'categories' => array(
                    'handler' => 'parseLeagues',
                    'list' => array(
                        9 => '/^England\.(\s{0,1})Premier League/U',
                        15 => '/^Spain\.(\s{0,1})Primera Division/U',
                        16 => '/^France\.(\s{0,1})Ligue 1/U',
                        13 => '/^Germany\.(\s{0,1})Bundesliga/U',
                        14 => '/^Italy\.(\s{0,1})Serie A/U',
                        10 => '/^UEFA Champions League(?!(.*)Qualifying)/U',
                        12 => '/^UEFA Europa League(?!(.*)Qualifying)/U',
                        29 => '/^UEFA Champions League\.(\s{0,1})Qualifying/U',
                        24 => '/^UEFA Europa League\.(\s{0,1})Qualifying/U',
                        76 => '/^Denmark\.(\s{0,1})Superliga/U',
                    ),
                ),
                'totals' => array(
                    'handler' => 'parseFootballTotals',
                    'list' => array(
                        array(
                            'title' => 'Total Markets',
                            'list' => array(
                                array('title' => 'Total Goals', 'type' => BettingConfig::TYPE_TOTAL, 'event' => BettingConfig::EVENT_GOAL, 'handler' => 'totalUnderOverHandler'),
                                array('title' => 'Total Goals (member1)', 'type' => BettingConfig::TYPE_INDIVIDUAL_TOTAL, 'event' => BettingConfig::EVENT_GOAL, 'handler' => 'individualTotalM1Handler'),
                                array('title' => 'Total Goals (member2)', 'type' => BettingConfig::TYPE_INDIVIDUAL_TOTAL, 'event' => BettingConfig::EVENT_GOAL, 'handler' => 'individualTotalM2Handler'),
                            ),
                        ),
                        array(
                            'title' => 'Main Markets',
                            'list' => array(
                                array('title' => 'Result','type' => BettingConfig::TYPE_1X3, 'event' => BettingConfig::EVENT_MATCH_RESULT, 'handler' => 'resultHandler'),
                            ),
                        ),
                        array(
                          'title' => 'Foul Markets',
                          'list' => array(
                              array('title' => 'Total Yellow Cards', 'type' => BettingConfig::TYPE_TOTAL, 'event' => BettingConfig::EVENT_YELLOW_CARD, 'handler' => 'totalUnderOverHandler'),
                              array('title' => 'Most Yellow Cards With Handicap', 'type' => BettingConfig::TYPE_HANDICAP, 'event' => BettingConfig::EVENT_YELLOW_CARD, 'handler' => 'handicapHandler'),
                             // array('title' => 'Total Yellow Cards', 'type' => 'total', 'handler' => 'totalYellowCardsHandler'),

                          ),
                        ),
                        array(
                            'title' => 'Handicap Markets',
                            'list' => array(
                                array('title' => 'To Win Match With Handicap', 'type' => BettingConfig::TYPE_HANDICAP, 'event' => BettingConfig::EVENT_GOAL, 'handler' => 'handicapHandler'),
                                // array('title' => 'Total Yellow Cards', 'type' => 'total', 'handler' => 'totalYellowCardsHandler'),

                            ),
                        ),
                        array(
                            'title' => 'Corner Markets',
                            'list' => array(
                                array('title' => 'Most Corners With Handicap', 'type' => BettingConfig::TYPE_HANDICAP, 'event' => BettingConfig::EVENT_CORNER, 'handler' => 'handicapHandler'),
                                array('title' => 'Total Corners', 'type' => BettingConfig::TYPE_TOTAL, 'event' => BettingConfig::EVENT_CORNER, 'handler' => 'totalUnderOverHandler'),
                                array('title' => 'Total Corners (member1)', 'type' => BettingConfig::TYPE_INDIVIDUAL_TOTAL, 'event' => BettingConfig::EVENT_CORNER, 'handler' => 'individualTotalM1Handler'),
                                array('title' => 'Total Corners (member2)', 'type' => BettingConfig::TYPE_INDIVIDUAL_TOTAL, 'event' => BettingConfig::EVENT_CORNER, 'handler' => 'individualTotalM2Handler'),

                            ),
                        ),
                    ),
                )
            ),
        ),
        'db_save_rules' => array(
            'identity_threshold' => 0.8, //граница % совпадения названий команд
        ),


    );
}