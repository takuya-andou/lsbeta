<?php

use yii\db\Migration;

class m160709_105127_create_forecast_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%forecast}}', [
            'id' => $this->primaryKey(),
            'match_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            //'active' => $this->integer()->notNull(),
            'date' => $this->dateTime(),
            'title' => $this->string(),
            'summary' => $this->string(),
            'content' => $this->text()->notNull(),
            'views' => $this->integer()->notNull(),
            'update_date' => $this->dateTime(),
            'updated_by_user_id' => $this->integer(),
            'bet_id' => $this->integer(),
            'current_coef' =>  $this->float(),
            'bet_amount' => $this->float(),
            'result' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addForeignKey(
            'fk-forecast-match_id',
            '{{%forecast}}',
            'match_id',
            '{{%match}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-forecast-user_id',
            '{{%forecast}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-forecast-updated_by_user_id',
            '{{%forecast}}',
            'updated_by_user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-forecast-bet_id',
            '{{%forecast}}',
            'bet_id',
            '{{%bet}}',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-forecast-match_id',
            '{{%forecast}}'
        );
        $this->dropForeignKey(
            'fk-forecast-user_id',
            '{{%forecast}}'
        );
        $this->dropForeignKey(
            'fk-forecast-updated_by_user_id',
            '{{%forecast}}'
        );
        $this->dropForeignKey(
            'fk-forecast-bet_id',
            '{{%forecast}}'
        );
        $this->dropTable('{{%forecast}}');
    }
}
