<?php
namespace app\extensions\parsing\cornerstats;
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21.04.2016
 * Time: 14:56
 */
class CornerstatsRequirement
{
    public static $config = array(
        'connection_rules' => array(
            'domain'    => 'http://corner-stats.com',
            //'language'  => 'en',
            'proxy' => null,//'5.153.139.38:3128'
        ),
        'parsing_rules' => array(
            'bookies' => array(
                3 => array('title' => 'Bet365',  'db_title' => 'Bet365'),
                9 => array('title' => 'PinnacleSports', 'db_title' => 'Pinnacle Sports'),
                17 => array('title' => '1xbet', 'db_title' => '1xbet'),
            ),
            'categories' => array(
                'list' => array(
                        9 => 'Premier League',
                        13 => 'Bundesliga',
                        14 => 'Serie A',
                        15=> 'Primera Division',
                        19 => 'Ligue 1',
                        20 => 'Premier League Russia',
                        81 => 'Championship',

                ),
            ),
            'totals' => array(
                'corners' => array(
                    'url' => 'index.php?route=football/game/corners',
                    'handler' => 'parseTable',
                    'list' => array(
                        'total' => array('title' => 'Total Corners', 'type' => 'total', 'event' => 'corner'),
                        'handicap' => array('title' => 'Handicap Corners', 'type' => 'handicap', 'event' => 'corner'),
                        '1x3' => array('title' => 'Most Corners', 'type' => '1x3', 'event' => 'corner'),
                    ),
                ),
                'goals' => array(
                    'url' => 'index.php?route=football/game/goals',
                    'handler' => 'parseTable',
                    'list' => array(
                        'total' => array('title' => 'Total Goals', 'type' => 'total', 'event' => 'goal'),
                        'handicap' => array('title' => 'Handicap Goals', 'type' => 'handicap', 'event' => 'goal'),
                        '1x3' => array('title' => 'Most Goals', 'type' => '1x3', 'event' => 'goal'),
                    ),
                ),
                'cards' => array(
                    'url' => 'index.php?route=football/game/cards',
                    'handler' => 'parseTable',
                    'list' => array(
                        'total' => array('title' => 'Total Cards', 'type' => 'total', 'event' => 'yellow_card'),
                        'handicap' => array('title' => 'Handicap Cards', 'type' => 'handicap', 'event' => 'yellow_card'),
                        '1x3' => array('title' => 'Most Cards', 'type' => '1x3', 'event' => 'yellow_card'),
                    ),
                ),

            )
        ),
    );
}