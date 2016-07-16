<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 13.04.2016
 * Time: 20:58
 */

namespace app\extensions\parsing;

class CUrlConnection extends LogWritable
{
    const DEFAULT_RETRY_COUNT = 2;
    protected $base_url;
    protected $proxy;
    protected $retry_count;
    static $log_filename = 'parsing_curl.log';
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    public function __construct($params) {
        if(isset($params['retry_count'])) $this->retry_count = $params['retry_count'];
        else $this->retry_count = self::DEFAULT_RETRY_COUNT;
        $this->base_url = $params['domain'];
        if(isset($params["language"])) $this->base_url.= "/" . $params["language"];
        $this->proxy=null;
        if(isset($params["proxy"])) $this->proxy = $params['proxy'];
    }

    public function curl($url_part, $data = null) {
        $url = $this->base_url.'/'.$url_part;
        $uagent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8";
        $connection_try =0;
        self::logPush('CUrlConnection. Requesting: ' .$url . '.', self::$log_filename, self::INFO);

        while($connection_try < $this->retry_count){
            $ch = curl_init( $url );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ���������� ���-��������
            //curl_setopt($ch, CURLOPT_HEADER, 0); // �� ���������� ���������
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // ��������� �� ����������
            curl_setopt($ch, CURLOPT_ENCODING, ""); // ������������ ��� ���������
            curl_setopt($ch, CURLOPT_USERAGENT, $uagent); // useragent
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // ������� ����������
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // ������� ������
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // ��������������� ����� 10-��� ���������
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            if ($data) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            if($this->proxy){
                curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            }

            $content = curl_exec( $ch );
            $err = curl_errno( $ch );
            $errmsg = curl_error( $ch );
            $header = curl_getinfo( $ch );
            curl_close( $ch );

            $header['errno'] = $err;
            $header['errmsg'] = $errmsg;
            $header['content'] = $content;
            //print_r($header);
            if(!empty($header['content']))
            return $header;
            else{
                self::logPush('CUrlConnection. Empty html page. ' . ++$connection_try . ' retry.', self::$log_filename, self::WARNING);
                sleep(2);
            }
        }
        self::logPush('CUrlConnection. The number of attempts is exceeded.', self::$log_filename, self::ERROR);
        return null;

    }

}