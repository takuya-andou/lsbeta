<?php
namespace app\extensions\geneticalgorithm;
use app\extensions\geneticalgorithm\helpers\Helper;
use app\extensions\parsing\LogWritable;

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 18:14
 */
abstract class Generation extends LogWritable
{
    protected $id;
    protected $data;
    protected $subjects;
    protected $generation_size;
    protected $prev_fitness_val = null;
    protected $mutation_chance;
    protected $info;
    static $log_filename = 'ga_generation.log';

    public function __construct($params = array()) {
        if(isset($params['id'])) $this->id = $params['id'];
        $this->generation_size = GAConfig::$config['common']['generation_size'];
        $this->mutation_chance = GAConfig::$config['common']['mutation_chance'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function getGenerationSize()
    {
        return $this->generation_size;
    }

    public function setGenerationSize($generation_size)
    {
        $this->generation_size = $generation_size;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getMutationChance()
    {
        return $this->mutation_chance;
    }

    public function setMutationChance($mutation_chance)
    {
        $this->mutation_chance = $mutation_chance;
    }

    public function execute(){
        foreach($this->subjects as $subject){
            $subject->execute($this->data);
        }
    }

    public function save($with_info = true){
        $file_name = \Yii::getAlias('@data') .
            DIRECTORY_SEPARATOR.'ga'.DIRECTORY_SEPARATOR.
            (isset($this->id) ? $this->id : Helper::generateRandomString(5)).'.dat';

        $str = "";
        if(!empty($this->subjects))
        foreach($this->subjects as $key => $subject){
            $str.='\n'.$subject->save($with_info);
        }

        $data_to_save = ['id' => $this->id, 'info' => $this->info, 'subjects' => $str];
        $str = json_encode($data_to_save);
        $file = fopen($file_name,"w");
        fwrite($file, $str);
        fclose($file);
    }

    abstract public function getNewGeneration();
    abstract protected function filter();
    abstract protected function distributeProbabilities();
    abstract public function generateFirstGeneration();

}