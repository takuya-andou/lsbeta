<?php

use yii\db\Migration;

class m160503_104250_create_model_param_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%model_param}}', [
            'id' => $this->primaryKey(),
            'system_name' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%model_param}}');
    }
}
