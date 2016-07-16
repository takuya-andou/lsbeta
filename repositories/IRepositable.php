<?php

namespace app\repositories;

/**
 * Interface IRepositable
 * @package app\repository
 */
interface IRepositable {

    /**
     * @param int $id
     * @return \yii\db\ActiveRecord
     */
    public function findById($id);

    /**
     * @return \yii\db\ActiveRecord
     */
    public function findAll();

    /**
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);

    /**
     * @return boolean
     */
    public function deleteAll();

    /**
     * @param int $id
     * @return boolean
     */
    public function updateById($id);

    /**
     * @param \yii\db\ActiveRecord $model
     * @return boolean
     */
    public function add(\yii\db\ActiveRecord $model);

}