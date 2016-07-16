<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 18:45
 */

namespace app\extensions\geneticalgorithm;


use app\extensions\geneticalgorithm\helpers\Helper;

class TestGeneration extends Generation
{
    public function __construct($params = array()) {
        parent::__construct($params);
    }
    public function generateFirstGeneration()
    {
        for($i=0; $i < $this->generation_size; $i++){
            $this->subjects[] = TestSubject::randomTestSubject($this->data);
        }
    }

    protected function distributeProbabilities()
    {
        $p_vector = array();

        if(!empty($this->subjects)){
            $min = $this->subjects[0]->getResult();
            $max = 0;
            $avg_res = 0;
            foreach($this->subjects as $key=>$subject){
                if($subject->getResult() < $min) $min = $subject->getResult();
                if($subject->getResult() > $max) $max = $subject->getResult();
                if($subject->isConsider()){
                    $avg_res +=$subject->getResult();
                    $p_vector[$key] = $subject->getResult();
                }
            }
            $p_vector = Helper::normalizeVectorProb($p_vector);
            foreach($this->subjects as $key=>&$subject){
                if($subject->isConsider())
                $this->subjects[$key]->setParentProbability($p_vector[$key]);
            }
            /*echo '<pre>';
            print_r($this->subjects);
            echo '</pre>';*/
            $avg_res/=count($this->subjects);
            return array('avg' => $avg_res, 'min' => $min, 'max' => $max);

        }

    }
    protected function targetFunction1($res){
        return 10000-$res;
    }
    protected function targetFunction2($max, $res){
        return $res - $max;
    }
    protected function targetFunctionLinear($min, $max, $count, $val){

    }


}