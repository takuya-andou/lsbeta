<?php

use yii\db\Migration;

class m160704_162309_alter_model_param_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%model_param}}', 'required', $this->integer()->notNull());
        $this->addColumn('{{%model_param}}', 'default_value', $this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%model_param}}', 'required');
        $this->dropColumn('{{%model_param}}', 'default_value');
    }
}
