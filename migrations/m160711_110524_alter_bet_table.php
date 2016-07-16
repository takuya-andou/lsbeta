<?php

use yii\db\Migration;

class m160711_110524_alter_bet_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bet}}', 'update_date', $this->dateTime());
    }

    public function down()
    {
        $this->dropColumn('{{%bet}}', 'update_date');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
