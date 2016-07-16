<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 18:46
 */

namespace app\extensions\geneticalgorithm;


class TestSubject extends Subject
{

    function execute($data)
    {
        $params = $data['equation_coefs'];
        $this->result = 0;
       foreach($params as $key => $value){
            $this->result += $params[$key]*$this->props[$key];
       }
    }
    public static function randomTestSubject($data){
    $params = $data['equation_coefs'];
    $props = array();
        if(!empty($params)){
            $subject = new self;
            foreach($params as $key => $value){
                $props[$key] = rand(1, 10);
            }
            $subject->setProps($props);
            return $subject;
        }
    return null;

    }

    /*function calculateResultDistance($data)
    {
        $result_required = $data['result_required'];
        return $this->result-$result_required;
    }*/

    public static function interbreed($subject1, $subject2, $mutations_on=false, $mutation_chance = 0.2){
        if(count($subject1->props) == count($subject2->props)){
            $child = new self;
            foreach($subject1->props as $key=>$prop){
                if($mutations_on){
                    $mut_probability = mt_rand() / mt_getrandmax();
                    if($mut_probability < $mutation_chance){
                        $child->props[$key] = rand(1,10);
                    }
                    else{
                        $child->props[$key] = (mt_rand(0,1) == 1) ? $subject1->props[$key] : $subject2->props[$key];
                    }
                }
                else{
                    $child->props[$key] = (mt_rand(0,1) == 1) ? $subject1->props[$key] : $subject2->props[$key];

                }
            }
            return $child;
        }
        return null;
    }

}