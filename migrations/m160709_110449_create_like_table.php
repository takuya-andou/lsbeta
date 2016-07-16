<?php

use yii\db\Migration;

class m160709_110449_create_like_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%like}}', [
            'id' => $this->primaryKey(),
            'module_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'ip' => $this->string()->notNull(),
            'value' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-like-user_id',
            '{{%like}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-like-user_id',
            '{{%like}}'
        );
        $this->dropTable('{{%like}}');
    }
}
