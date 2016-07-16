<?php

use yii\db\Migration;

class m160714_122420_create_bet_history_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%bet_history}}', [
            'id' => $this->primaryKey(),
            'match_id' => $this->integer()->notNull(),
            'bookie_id' => $this->integer()->notNull(),
            'type_id' => $this->integer()->notNull(),
            'event_id' => $this->integer()->notNull(),
            'member' => $this->integer(),
            'value' => $this->float(),
            'sign' => $this->integer(),
            'coef' => $this->float(),
            'initial_coef' => $this->float(),
            'external_match_id' => $this->string(),
            'date' => $this->dateTime(),
            'update_date' => $this->dateTime(),
            'move_date' => $this->dateTime(),
        ]);

        $this->addForeignKey(
            'fk-bet_history-match_id',
            '{{%bet_history}}',
            'match_id',
            '{{%match}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-bet_history-bookie_id',
            '{{%bet_history}}',
            'bookie_id',
            '{{%bookie}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-bet_history-type_id',
            '{{%bet_history}}',
            'type_id',
            '{{%bet_type}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-bet_history-event_id',
            '{{%bet_history}}',
            'event_id',
            '{{%bet_event}}',
            'id',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-bet_history-match_id',
            '{{%bet_history}}'
        );
        $this->dropForeignKey(
            'fk-bet_history-bookie_id',
            '{{%bet_history}}'
        );
        $this->dropForeignKey(
            'fk-bet_history-type_id',
            '{{%bet_history}}'
        );
        $this->dropForeignKey(
            'fk-bet_history-event_id',
            '{{%bet_history}}'
        );

        $this->dropTable('{{%bet_history}}');
    }
}
