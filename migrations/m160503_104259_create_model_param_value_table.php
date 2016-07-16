<?php

use yii\db\Migration;

class m160503_104259_create_model_param_value_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%model_param_value}}', [
            'id' => $this->primaryKey(),
            'model_id' => $this->integer()->notNull(),
            'param_id' => $this->integer()->notNull(),
            'value' => $this->string()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-model_param_value-model_id',
            '{{%model_param_value}}',
            'model_id',
            '{{%model}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-model_param_value-param_id',
            '{{%model_param_value}}',
            'param_id',
            '{{%model_param}}',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-model_param_value-model_id',
            '{{%model_param_value}}'
        );
        $this->dropForeignKey(
            'fk-model_param_value-param_id',
            '{{%model_param_value}}'
        );

        $this->dropTable('{{%model_param_value}}');
    }
}
