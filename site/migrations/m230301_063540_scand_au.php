<?php

use yii\db\Migration;

use app\models\Lot;
use app\models\User;

/**
 * Class m230301_063540_scand_au
 */
class m230301_063540_scand_au extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lots}}', [
            'id' => $this->primaryKey()->comment('Ключик лотов'),
            'name' => $this->string(50)->notNull()->comment('Наименование лота'),
            'sale' => $this->decimal(10,2)->unsigned()->notNull()->defaultValue(0)->comment('Цена продажи, р'),
            'dprice' => $this->decimal(2,2)->unsigned()->notNull()->comment('Шаг цены, р'),
            'dtime' => $this->smallInteger()->unsigned()->notNull()->comment('время ожидания ставки, с'),
            'state' => sprintf("enum(%s) not null default '%s' comment 'состояние лота' ", "'" . implode("','", array_keys(Lot::STATES)) . "'", Lot::STATE_NEW),
            // $this->boolean()->defaultValue(false)->notNull()->comment('Завершен'),
        ], "comment 'список лотов'");


        $this->createTable('stakes', [
            'id' => $this->primaryKey()->comment('Ключик'),
            'lid' => $this->integer()->notNull()->comment('ссылка на лот'),
            'uid' => $this->integer()->comment('ссылка на юзеря'),
            'created' => $this->timestamp()->notNull()->defaultExpression('current_timestamp()')->comment('дата время осуществления ставки'),
        ], "comment 'ставки'");
        $this->createIndex('stakes_lid_ind', '{{%stakes}}', ['lid']);
        $this->createIndex('stakes_lid_und', '{{%stakes}}', ['uid']);
        $this->addForeignKey('lots_staks_fk', '{{%stakes}}', ['lid'], '{{%lots}}', ['id'], 'cascade', 'cascade');


        $this->createTable('{{%user_free_stakes}}', [
            'uid' => $this->integer()->notNull()->comment('Ссылка на пользователя'),
            'stakes_size' => $this->integer()->notNull()->defaultValue(50)->comment('Свободные ставки'),
        ], "comment 'свободные ставки игроков'");
        $this->addPrimaryKey('user_pk', '{{%user_free_stakes}}', ['uid']);
        $this->createIndex('stake_size_ind', '{{%user_free_stakes}}', ['stakes_size']);
        $list = [];
        foreach (User::getIds() as $uid) {
            $list[] = ['uid' => $uid];
        }
        $this->batchInsert('{{%user_free_stakes}}', ['uid'], $list);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_free_stakes}}');
        $this->dropTable('{{%stakes}}');
        $this->dropTable('{{%lots}}');
    }

}
