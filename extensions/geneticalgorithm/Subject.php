<?php
namespace app\extensions\geneticalgorithm;
use app\extensions\parsing\LogWritable;

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 18:13
 */
interface IInterbreeding{
    public static function interbreed($subject1, $subject2, $mutationOn=false, $mutation_chance = 0.05);
}
abstract class Subject extends LogWritable implements IInterbreeding
{
    protected $id;
    protected $data;
    protected $props;
    protected $result;
    protected $parent_probability;
    protected $consider = true;
    protected $info;

    static $log_filename = 'ga_subject.log';

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getProps()
    {
        return $this->props;
    }

    public function setProps($props)
    {
        $this->props = $props;
    }

    public function getParentProbability()
    {
        return $this->parent_probability;
    }

    public function setParentProbability($parent_probability)
    {
        $this->parent_probability = $parent_probability;
    }

    public function isConsider()
    {
        return $this->consider;
    }

    public function setConsider($consider)
    {
        $this->consider = $consider;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function __construct($params = array()) {
        if(!empty($params))
        foreach($params as $key => $prop_val) {
            $this->props[$key] = $prop_val;
        }
    }

    abstract function execute($data);

    public function save($with_info = true){
        if($with_info){
            $str = json_encode([
                'id' => isset($this->id) ? $this->id : 'undef',
                'props' => !empty($this->props) ? $this->props : 'undef',
                'info' => !empty($this->info) ? $this->info : 'undef',
            ]);

        }
        else{
            $str = json_encode([
                'id' => isset($this->id) ? $this->id : 'undef',
                'props' => !empty($this->props) ? $this->props : 'undef',
            ]);
        }
        return $str;
    }

   // abstract function calculateResultDistance($data);



}