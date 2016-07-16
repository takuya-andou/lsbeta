<?php
// This is the config for linear model v1.0
return [
    'logPath'   => 'linermodel_v1.log',
    'errorPath' => 'linermodel_v1_error.log',

    // ouput value for debug or logs (possible: decimal, fraction, percent)
    'output'    => 'percent',

    // event from LinearModel::getBetProperties (possible :Yellow Card, Corners etc)
    'event'     => null,

    //id from soccer_match
    'matchId'   => null,

    //Value of bet (possible: >5.5, <9, +1, -2; possible sign: >, <, =, !, <=, >=)
    'value'     => null,

    //difference between
    'difference'=> 0.3,


    // relevance for different events from LinearModel::getBetProperties
    // possible keys:
    //   'PERSONAL'     - Personal matches
    //   'PREVIOUS'     - Previous matches
    //   'INCOMPETITION'- Matches from this competition,
    //   'REFEREE'      - Matches with current referee (for cards)
    'relevance' => [
        'default' => [
            'PERSONAL' => 600,
            'PREVIOUS' => 250,
            'INCOMPETITION' => 380,
            'REFEREE' => 50,
        ],
        'Yellow Card' => [
            'PERSONAL' => 500,
            'PREVIOUS' => 150,
            'INCOMPETITION' => 280,
            'REFEREE' => 400,
        ]
    ],

    'timeDistribution'  => 'HYPERBOLIC_TANGENT', //(?)
    'smoothingMode'     => false,
    'cutDeflectionMode' => false,

];