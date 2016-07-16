<?php
// This is the config for parser from corner-stats.com
return [
    'logPath'       => 'parser_cornerstats.log',
    'url'           => 'http://corner-stats.com',
    'periodUpdate'  => '-14 days',

    'cacheKey'     => substr(md5('last_parser_update_id'), 0, 9),
];