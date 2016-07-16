<?php

use yii\db\Migration;

class m160503_103136_create_model_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%model}}', [
            'id' => $this->primaryKey(),
            'type_id' => $this->integer()->notNull(),
            'event_id' => $this->integer()->notNull(),
            'usable' => $this->integer()->notNull(),
            'name' => $this->string(),

        ]);
        $this->addForeignKey(
            'fk-model-type_id',
            '{{%model}}',
            'type_id',
            'soccer_bet_type',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-model-event_id',
            '{{%model}}',
            'event_id',
            'soccer_bet_event',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-model-type_id',
            '{{%model}}'
        );
        $this->dropForeignKey(
            'fk-model-event_id',
            '{{%model}}'
        );
        $this->dropTable('{{%model}}');
    }
}
