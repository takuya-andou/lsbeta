<?php

namespace app\repositories;

/**
 * @see https://lionshot.myjetbrains.com/youtrack/issue/LIONSHOT-28
 *
 * Class Repository
 * @package app\repository
 */
abstract class Repository implements IRepositable
{

    /**
     * @var bool $cache
     */
    protected static $cache = true;

    /**
     * @param bool $cache
     */
    public static function setCache($cache) {
        self::$cache = (bool) $cache;
    }

    /**
     * @return bool
     */
    public static function getCache() {
        return self::$cache;
    }

    /**
     * @param int $id
     * @return \yii\db\ActiveRecord
     */
    public function findById($id){}

    /**
     * @return \yii\db\ActiveRecord
     */
    public function findAll(){}

    /**
     * @param int $id
     * @return boolean
     */
    public function deleteById($id){}

    /**
     * @return boolean
     */
    public function deleteAll(){}

    /**
     * @param int $id
     * @return boolean
     */
    public function updateById($id){}

    /**
     * @param \yii\db\ActiveRecord $model
     * @return boolean
     */
    public function add(\yii\db\ActiveRecord $model){}

}