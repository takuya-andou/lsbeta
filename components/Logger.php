<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class Logger extends Component {
    public $log_path;

    public function push($message, $fileUrl  = false, $action = false, $encode = false){

        $file= \Yii::getAlias('@logs').'/'.(($fileUrl) ? $fileUrl : $this->log_path);
        $time = ($message=="")?"":'['.date("d.m.Y H:i:s",strtotime("now"))."]: ";
        if ($encode) {
            $message = json_encode($message);
        }
        file_put_contents($file, $time.$message."\r\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path) {
        if ($path) {
            $this->log_path = $path;
        }
        return $this;
    }

}
/*
class Logger2 extends Component
{
    public $log_path;
    public function write($msg, $jsonencode = false) {
        $file = $this->log_path;
        if (!is_file($file)) {
            throw new \DomainException('Path ' . $file . ' for logs doesn\'t exist');
        }
        $fh = fopen( $this->log_path, "a" );
        $msg = ($jsonencode) ? json_encode($msg) : $msg;
        $msg = "[" . date('d.m.Y H:i:s') . "] " . $msg . "\r\n";
        fputs($fh, $msg);
        fclose($fh);
    }
}*/