<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 18.04.2016
 * Time: 21:27
 */

namespace app\extensions\parsing;

include_once \Yii::getAlias('@components') . '/simple_html_dom/simple_html_dom.php';
abstract class HTMLParser extends Parser
{
    protected $connector;
    const REQUEST_NUM_TO_PAUSE = 60;
    const PAUSE_LENGTH = 10;

    protected $cur_requests_num = 0;
    protected function incrementRequests(){
        $this->cur_requests_num++;
        if($this->cur_requests_num >= self::REQUEST_NUM_TO_PAUSE)
        {
            self::logPush('HTMLParser. Pausing requests...', self::$log_filename, self::INFO);

            $this->pauseRequests();
            $this->cur_requests_num =0;
        }
    }
    protected function pauseRequests(){
        $this->cur_requests_num = 0;
        sleep(self::PAUSE_LENGTH);
    }

    /**
     * @return CUrlConnection
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * @param CUrlConnection $connector
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;
    }

    public function __construct($params) {
        $this->connector = new CUrlConnection(
            [
                'domain'    => $params['domain'],
                'language'  => (isset($params["language"])) ? $params["language"] : null,
                'proxy' => (isset($params["proxy"])) ? $params["proxy"] : null
            ]
        );
    }
}