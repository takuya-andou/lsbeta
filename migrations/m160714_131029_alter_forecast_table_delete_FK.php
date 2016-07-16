<?php

use yii\db\Migration;

class m160714_131029_alter_forecast_table_delete_FK extends Migration
{
    public function up()
    {
        $this->addColumn('{{%forecast}}', 'bet_in_history', $this->boolean()->notNull());
        $this->dropForeignKey(
            'fk-forecast-bet_id',
            '{{%forecast}}'
        );
    }

    public function down()
    {
        $this->dropColumn('{{%forecast}}', 'bet_in_history');
        $this->addForeignKey(
            'fk-forecast-bet_id',
            '{{%forecast}}',
            'bet_id',
            '{{%bet}}',
            'id',
            'CASCADE'
        );
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
