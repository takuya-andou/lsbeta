<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 19:00
 */

namespace app\extensions\geneticalgorithm;


class GAConfig
{
    public static $config = array(
        'common' => array(
            'generations_num' => 3,
            'generation_size' => 10,
            'mutation_chance' => 0.1,
        ),

        'model' => array(
            'model_params_range' => array(
                'difference'=> array('type' => 'float', 'lower_limit' => 0.05, 'upper_limit' => 0.15),
                'PERSONAL' => array('type' => 'int', 'lower_limit' => 2000, 'upper_limit' => 4000),
                'PREVIOUS' => array('type' => 'int', 'lower_limit' => 500, 'upper_limit' => 3000),
                'INCOMPETITION' => array('type' => 'int', 'lower_limit' => 5000, 'upper_limit' => 7000),
                ' ' => array('type' => 'int', 'lower_limit' => 500, 'upper_limit' => 8000),
                'HOME' => array('type' => 'int', 'lower_limit' => 500, 'upper_limit' => 2000),
                'AWAY' => array('type' => 'int', 'lower_limit' => 500, 'upper_limit' => 2000),
                'differenceLimit' => array('type' => 'float', 'lower_limit' => 1, 'upper_limit' => 1.5),
                'smoothingMode' => array('type'=>'bool', 'lower_limit' => 1, 'upper_limit' => 1),
                'cutDeflectionMode' => array('type'=>'bool', 'lower_limit' => 0, 'upper_limit' => 0),
            ),
            'execution' => array(
                'initial_bank_size' => '100.0',//perc
                'bet_size' => '3.0', //perc,
                'matches_num' => '30',
            ),
            'bookies' => array(
                //3 => 'Bet365',
                16 => 'Marathon',
                //17 => '1xbet'
            ),
        )
    );
}