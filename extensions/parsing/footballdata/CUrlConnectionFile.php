<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 16.04.2016
 * Time: 23:09
 */

namespace app\extensions\parsing\footballdata;


use app\extensions\parsing\CUrlConnection;

class CUrlConnectionFile extends CUrlConnection
{
    public function curlFile($url_part, $data = null)
    {
        $url = $this->base_url.'/'.$url_part;
        $uagent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8";
        $connection_try =0;
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
            curl_setopt($ch, CURLOPT_SSLVERSION,3);

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
            if(!empty($header['content']))
                return $header;
            else{
                self::logPush('CUrlConnectionFile. Empty html page. ' . ++$connection_try . ' retry.', self::$log_filename, self::WARNING);
                sleep(2);
            }
        }
        self::logPush('CUrlConnectionFile. The number of attempts is exceeded.', self::$log_filename, self::ERROR);
        return null;
    }

}