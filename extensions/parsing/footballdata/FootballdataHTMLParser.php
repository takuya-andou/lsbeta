<?php
/**
 * Date: 18.04.2016
 * Предназначен для парсинга блоков со страниц сайта.
 */

namespace app\extensions\parsing\footballdata;

use app\extensions\parsing\footballdata\helpers\Helper;
use app\extensions\parsing\helpers\HTMLHelper;
use app\extensions\parsing\HTMLParser;

class FootballdataHTMLParser extends HTMLParser
{

    public function __construct($params = array()) {
        parent::__construct(array_merge($params, FootballdataRequirement::$config['connection_rules'] ));
    }
    public function run()
    {
        $documents=array();
        foreach(FootballdataRequirement::$config['loader_rules']['countries_results_list'] as $country){
            $page = $this->connector->curl($country['url'], null);
            if($page){
                $html = str_get_html($page['content']);
                if($html){
                    $table_i = HTMLHelper::findAll('table', $html);
                    if(($docs = $this->parseDocs($table_i[6], $country)) != null){
                        $documents[$country['title']] = $this->parseDocs($table_i[6], $country);
                    }
                    else{
                        self::logPush('FootballdataHTMLParser. '.$country['title'].' was not parsed.',self::$log_filename, self::ERROR);
                    }
                }
                else{
                    self::logPush('FootballdataHTMLParser. Simple html cannot convert html page.',self::$log_filename, self::ERROR);
                }
            }
            else{
                self::logPush('FootballdataHTMLParser. Simple html cannot convert html page.',self::$log_filename, self::ERROR);
            }

        }
        return $documents;

    }
    protected function parseDocs($pageHTML, $country){
        $seasons_data = array();
        foreach($country['seasons'] as $season){
            $season_info = Helper::getSeasonData($pageHTML, $season);
            $leagues_data = array();
            if(isset($season_info)){
                foreach($season['leagues'] as $league){
                    if(($url = Helper::getLeagueFileURL($season_info,$league))!=null)
                    $leagues_data[$league['title']] = $url;
                    else{
                        self::logPush('FootballdataHTMLParser. '.$league['title'].' no document url was parsed.',self::$log_filename, self::ERROR);
                    }
                }

            }
            if(!empty($leagues_data))
            $seasons_data[$season['title']] = $leagues_data;
            else{
                self::logPush('FootballdataHTMLParser. '.$season['title'].' no leagues were parsed.',self::$log_filename, self::ERROR);
            }
        }
        if(!empty($seasons_data))
        return $seasons_data;
        else return null;

    }
}