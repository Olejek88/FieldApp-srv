<?php

use common\components\MainFunctions;
use common\models\RequestBuyStatus;
use common\models\User;
use yii\db\Migration;
use yii\web\HttpException;

class m170112_051612_new_migrations_1 extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function up()
    {
        $tables = $this->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $currentTime = date('Y-m-d\TH:i:s');

        $this->createTable('measure_type', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string()->notNull()->unique(),
            'title' => $this->string()->notNull()->defaultValue(""),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable('channel', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string()->notNull()->unique(),
            'title' => $this->string()->notNull()->defaultValue(""),
            'measureTypeUuid' => $this->string()->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable('measured_value', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string()->notNull()->unique(),
            'date' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'value' => $this->string()->notNull()->defaultValue("0"),
            'channelUuid' => $this->string()->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-measured_value-channelUuid',
            'measured_value',
            'channelUuid',
            'channel',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->addForeignKey(
            'fk-channel-measureTypeUuid',
            'channel',
            'measureTypeUuid',
            'measure_type',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createTable('users', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string()->notNull()->unique(),
            'name' => $this->string()->notNull(),
            'pass' => $this->string()->notNull(),
            'login' => $this->string()->notNull(),
            'type' => $this->integer()->notNull()->defaultValue(0),
            'image' => $this->string(),
            'active' => $this->string()->notNull(),
            'userId' => $this->integer()->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        if (!in_array('token', $tables)) {
            if ($dbType == "mysql") {
                $this->createTable('{{%token}}', [
                    'tagId' => 'VARCHAR(128)',
                    'accessToken' => 'VARCHAR(128) NOT NULL',
                    'tokenType' => 'VARCHAR(128) NOT NULL',
                    'expiresIn' => 'INT(10) UNSIGNED NOT NULL',
                    'userName' => 'VARCHAR(128) NOT NULL',
                    0 => 'PRIMARY KEY (`userName`)',
                    'issued' => 'VARCHAR(128) NOT NULL',
                    'expires' => 'VARCHAR(128) NOT NULL',
                ], $tableOptions);
                $this->createIndex('access_token_index', '{{%token}}', 'accessToken', TRUE);
            }
        }

        // пользователи
        $this->insert('{{%users}}', [
            '_id' => '1',
            'uuid' => 'E788CF00-CDCF-4BB5-A53A-DCBC946B2325',
            'name' => 'Olejek',
            'login' => 'oleg',
            'pass' => Yii::$app->security->generatePasswordHash(MainFunctions::GUID()),
            'type' => '222',
            'active' => '1',
            'userId' => 1,
            'image' => '',
            'createdAt' => $currentTime,
            'changedAt' => $currentTime
        ]);

        $this->addColumn('{{%user}}', 'verification_token', $this->string()->defaultValue(null));
        $this->alterColumn('{{%users}}', 'userId', $this->integer()->notNull()->unique());

        $this->insert('{{%user}}', [
            'id' => '1',
            'username' => 'admin',
            'email' => 'olejek8@yandex.ru',
            'status' => 1,
            'password_reset_token' => '',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash("admin"),
            'created_at' => $currentTime,
            'updated_at' => $currentTime
        ]);

        $this->addForeignKey(
            'fk-users-user-userId',
            '{{%users}}',
            'userId',
            'user',
            'id',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->insertIntoType('measure_type', 'Глубина (м)');
        $this->insertIntoType('measure_type', 'Высота (м)');
        $this->insertIntoType('measure_type', 'Объем (м3)');
        $this->insertIntoType('measure_type', 'Масса (т.)');
        $this->insertIntoType('measure_type', 'Длина (м)');
        $this->insertIntoType('measure_type', 'Частота (Гц)');
        $this->insertIntoType('measure_type', 'Мощность (кВт/ч)');
        $this->insertIntoType('measure_type', 'Энергия (кВт)');
    }

    /**
     * @param $table
     * @param $title
     * @throws Exception
     */
    private function insertIntoType($table, $title)
    {
        $currentTime = date('Y-m-d\TH:i:s');
        $this->insert($table, [
            'uuid' => MainFunctions::GUID(),
            'title' => $title,
            'createdAt' => $currentTime,
            'changedAt' => $currentTime
        ]);
    }

    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `measured_value`');
        $this->execute('DROP TABLE IF EXISTS `users`');
        $this->execute('DROP TABLE IF EXISTS `token`');
        return true;
    }
}
