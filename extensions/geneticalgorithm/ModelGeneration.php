<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 18:44
 */

namespace app\extensions\geneticalgorithm;


use app\extensions\geneticalgorithm\helpers\Helper;

class ModelGeneration extends Generation
{
    public function generateFirstGeneration()
    {
        for($i=0; $i < $this->generation_size; $i++){
            $this->subjects[] = ModelSubject::randomModelSubject(['model_params_range' => GAConfig::$config['model']['model_params_range']]);
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
    /**
     * Метод для предварительной фильтрации моделей, например не пускать модели по которым
     * количество ставок не превышает 10%
     */
    function filter()
    {
        // TODO: Implement filter() method.
    }

    public function getNewGeneration(){

        $res = $this->distributeProbabilities();
        $current_fitness_val = $res['avg'];
        $min = $res['min'];
        $max = $res['max'];
        $mutations_on = false;
        if(isset($this->prev_fitness_val) && $this->prev_fitness_val > $current_fitness_val){
            $mutations_on = true;
        }
        $this->prev_fitness_val = $current_fitness_val;
        $this->info = [
            'avg' => $current_fitness_val,
            'min' => $min,
            'max' => $max,
            'generation_size' => count($this->subjects)
        ];
        /*echo '<pre>';
        echo 'AVG:'.$current_fitness_val.' MIN:'.$min.' MAX:'.$max;
        //print_r($current_fitness_val);
        echo '</pre>';*/
        $new_generation = array();
        for($i=0; $i < $this->generation_size; $i++){
            $probability = mt_rand() / mt_getrandmax();
            //echo 'Prob1:'.$probability.'<br>';
            $probs_sum =0;
            $parent1_id = null;
            $parent2_id = null;
            foreach($this->subjects as $key=>$subject){
                if($probability <= $probs_sum + $subject->getParentProbability()){
                    $parent1_id = $key;
                    $this->subjects[$key]->setConsider(false);
                    $this->subjects[$key]->setParentProbability(0);
                    $this->distributeProbabilities();
                    $sprobability = mt_rand() / mt_getrandmax();
                    //echo 'Prob2:'.$sprobability.'<br>';
                    $sprobs_sum =0;
                    foreach($this->subjects as $skey=>$ssubject){
                        if($sprobability <= $sprobs_sum + $ssubject->getParentProbability()){
                            $parent2_id = $skey;
                            $new_generation[] = ModelSubject::interbreed($subject, $ssubject, $mutations_on);
                            break;
                        }
                        else{
                            $sprobs_sum+=$ssubject->getParentProbability();
                        }
                    }
                    $this->subjects[$key]->setConsider(true);
                    $this->distributeProbabilities();
                    break;
                }
                else{
                    $probs_sum+=$subject->getParentProbability();
                }
            }


        }
        $this->subjects = $new_generation;
    }


}