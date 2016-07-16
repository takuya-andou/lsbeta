<?php
/**
 * Date: 16.04.2016
 * Используется для загрузки файлов с сайта
 */
namespace app\extensions\parsing\footballdata;

use app\extensions\parsing\footballdata\helpers\Helper;

class FootballdataLoader
{
    protected $connector;

    public function getConnector()
    {
        return $this->connector;
    }

    public function setConnector($connector)
    {
        $this->connector = $connector;
    }

    public function __construct($params = array()) {
        $this->connector = new CUrlConnectionFile(
                FootballdataRequirement::$config['connection_rules']
        );
    }

    public function loadData(){

        $fp = null;
        $url_files = null;
        $data = array();
        foreach(FootballdataRequirement::$config['loader_rules']['countries_results_list'] as $country_results_required){
            foreach($country_results_required['seasons'] as $season){
                foreach($season['leagues'] as $league){
                    if(Helper::ifFileExists($country_results_required['title'],$season['title'], $league['title'])){
                        $data[] = array(
                            'country_id' => $country_results_required['country_id'],
                            'category_id' => $league['category_id'],
                            'title' => $country_results_required['title'],
                            'file' => Helper::makeFilename($country_results_required['title'],$season['title'],$league['title'])
                        );
                    }
                    else{
                        if(!isset($fp)){
                            $fp = new FootballdataHTMLParser();
                            $url_files = $fp->run();
                        }
                        $response = $this->connector->curl($url_files[$country_results_required['title']][$season['title']][$league['title']], null);
                        if(isset($response['content'])){
                            Helper::saveFile($response['content'],$country_results_required['title'], $season['title'], $league['title']);
                            $data[] =
                                array(
                                    'country_id' => $country_results_required['country_id'],
                                    'category_id' => $league['category_id'],
                                    'title' => $country_results_required['title'],
                                    'file' => Helper::makeFilename($country_results_required['title'],$season['title'],$league['title'])
                                );
                        }
                        else{
                            self::logPush($country_results_required['title'].'-'.
                                $season['title'].'-'.$league['title'].
                                '. No file received.', self::$log_filename, self::ERROR);
                        }
                    }
                }

            }

        }
        return $data;
    }




}