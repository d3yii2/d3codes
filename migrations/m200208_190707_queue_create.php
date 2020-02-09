<?php

use yii\db\Migration;

class m200208_190707_queue_create  extends Migration {

    public function safeUp() {

        $this->execute('
            CREATE TABLE `d3codes_code_queue` (
              `id` int(10) unsigned NOT NULL,
              `record_id` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `d3codes_code_queue_ibfk_record` (`record_id`),
              CONSTRAINT `d3codes_code_queue_ibfk_record` FOREIGN KEY (`record_id`) REFERENCES `d3codes_code_record` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1

        ');

    }

    public function safeDown() {
        echo "m200208_190707_queue_create cannot be reverted.\n";
        return false;
    }
}
