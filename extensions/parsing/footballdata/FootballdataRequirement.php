<?php

/**
 * Created by PhpStorm.
 * Date: 16.04.2016
 * Конфиг парсера с football-co.uk
 */
namespace app\extensions\parsing\footballdata;

class FootballdataRequirement
{
    public static $config = array(
        'connection_rules' => array(
            'domain'    => 'http://www.football-data.co.uk',
        ),
        'loader_rules' => array(
            'save_path' => 'footballcouk',
            'countries_results_list' => array(
                array(
                    'title' => 'England Football Results',
                    'country_id' => '11',
                    'url' => 'englandm.php',
                    'seasons' => array(
                        array(
                            'title' => 'Season 2015/2016',
                            'preg_pattern' => 'Season 2015\/2016',
                            'leagues' => array(
                                array('title' => 'Premier League', 'category_id' => '9'),//если нужен шаблон для прега, то указать
                                array('title' => 'Championship', 'category_id' => '81'),
                            ),
                        ),
                        /*array(
                            'title' => 'Season 2014/2015',
                            'preg_pattern' => 'Season 2014\/2015',
                            'leagues' => array(
                                array('title' => 'Premier League', 'id_category' => '9'),
                               //array('title' => 'Championship'),
                            ),
                        ),*/
                    ),
                ),
            ),
        ),
        'parser_rules' => array(
            'fields_required' => array(
                'match_datetime' => 'Date',
                'member1' => 'HomeTeam',
                'member2' => 'AwayTeam',
            ),
            'coefs_required' => array(
                'B365H' => array('id' => '3', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Bet365 home win odds'),
                'B365D' => array('id' => '3', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Bet365 draw odds'),
                'B365A' => array('id' => '3', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Bet365 away win odds'),
            'BSH' => array('id' => '4', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Blue Square home win odds'),
            'BSD' => array('id' => '4', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Blue Square draw odds'),
            'BSA' => array('id' => '4', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Blue Square away win odds'),
            'BWH' => array('id' => '5', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Bet&Win home win odds'),
            'BWD' => array('id' => '5', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Bet&Win draw odds'),
            'BWA' => array('id' => '5', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Bet&Win away win odds'),
            'GBH' => array('id' => '6', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Gamebookers home win odds'),
            'GBD' => array('id' => '6', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Gamebookers draw odds'),
            'GBA' => array('id' => '6', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Gamebookers away win odds'),
            'IWH' => array('id' => '7', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Interwetten home win odds'),
            'IWD' => array('id' => '7', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Interwetten draw odds'),
            'IWA' => array('id' => '7', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Interwetten away win odds'),
            'LBH' => array('id' => '8', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Ladbrokes home win odds'),
            'LBD' => array('id' => '8', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Ladbrokes draw odds'),
            'LBA' => array('id' => '8', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Ladbrokes away win odds'),
            'PSH' => array('id' => '9', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Pinnacle Sports home win odds'),
            'PSD' => array('id' => '9', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Pinnacle Sports draw odds'),
            'PSA' => array('id' => '9', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Pinnacle Sports away win odds'),
            'SOH' => array('id' => '10', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Sporting Odds home win odds'),
            'SOD' => array('id' => '10', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Sporting Odds draw odds'),
            'SOA' => array('id' => '10', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Sporting Odds away win odds'),
            'SBH' => array('id' => '11', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Sportingbet home win odds'),
            'SBD' => array('id' => '11', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Sportingbet draw odds'),
            'SBA' => array('id' => '11', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Sportingbet away win odds'),
            'SJH' => array('id' => '12', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Stan James home win odds'),
            'SJD' => array('id' => '12', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Stan James draw odds'),
            'SJA' => array('id' => '12', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Stan James away win odds'),
            'SYH' => array('id' => '13', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Stanleybet home win odds'),
            'SYD' => array('id' => '13', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Stanleybet draw odds'),
            'SYA' => array('id' => '13', 'type' => '1x3', 'event' => 'match_result', 'title' => 'Stanleybet away win odds'),
            'VCH' => array('id' => '14', 'type' => '1x3', 'event' => 'match_result', 'title' => 'VC Bet home win odds'),
            'VCD' => array('id' => '14', 'type' => '1x3', 'event' => 'match_result', 'title' => 'VC Bet draw odds'),
            'VCA' => array('id' => '14', 'type' => '1x3', 'event' => 'match_result', 'title' => 'VC Bet away win odds'),
            'WHH' => array('id' => '15', 'type' => '1x3', 'event' => 'match_result', 'title' => 'William Hill home win odds'),
            'WHD' => array('id' => '15', 'type' => '1x3', 'event' => 'match_result', 'title' => 'William Hill draw odds'),
            'WHA' => array('id' => '15', 'type' => '1x3', 'event' => 'match_result', 'title' => 'William Hill away win odds'),
            'BbAv>2.5' => array('id' => '2', 'type' => 'total', 'event' => 'goal', 'title' => 'Betbrain average over 2.5 goals'),
            'BbAv<2.5' => array('id' => '2', 'type' => 'total', 'event' => 'goal', 'title' => 'Betbrain average under 2.5 goals'),
            'GB>2.5' => array('id' => '6', 'type' => 'total', 'event' => 'goal', 'title' => 'Gamebookers over 2.5 goals'),
            'GB<2.5' => array('id' => '6', 'type' => 'total', 'event' => 'goal', 'title' => 'Gamebookers under 2.5 goals'),
            'B365>2.5' => array('id' => '3', 'type' => 'total', 'event' => 'goal', 'title' => 'Bet365 over 2.5 goalss'),
            'B365<2.5' => array('id' => '3', 'type' => 'total', 'event' => 'goal', 'title' => 'Bet365 under 2.5 goals'),

            ),
        ),
        'db_save_rules' => array(
            'identity_threshold' => 0.8
        ),
        //на будущее для сверки данных в базе
        'checker_rules' => array(

        ),
    );
}