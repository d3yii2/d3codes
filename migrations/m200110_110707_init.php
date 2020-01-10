<?php

use yii\db\Migration;

class m200110_110707_init  extends Migration {

    public function safeUp() {

        $this->execute('
            CREATE TABLE `d3codes_code` (
              `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(50) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1

        ');

        $this->execute('
            CREATE TABLE `d3codes_series` (
              `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(50) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1

        ');

        $this->execute('
            CREATE TABLE `d3codes_code_record` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `code_id` tinyint(3) unsigned NOT NULL,
              `series_id` tinyint(3) unsigned NOT NULL,
              `model_id` tinyint(3) unsigned NOT NULL,
              `model_record_id` int(10) unsigned NOT NULL,
              `sqn` int(10) unsigned NOT NULL,
              `full_code` varchar(50) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `code_id` (`code_id`),
              KEY `model_code_series` (`model_id`,`code_id`,`series_id`),
              KEY `model_fullCode` (`model_id`,`full_code`(10)),
              KEY `d3codes_code_record_ibfk_series` (`series_id`),
              CONSTRAINT `d3codes_code_record_ibfk_code` FOREIGN KEY (`code_id`) REFERENCES `d3codes_code` (`id`),
              CONSTRAINT `d3codes_code_record_ibfk_model` FOREIGN KEY (`model_id`) REFERENCES `sys_models` (`id`),
              CONSTRAINT `d3codes_code_record_ibfk_series` FOREIGN KEY (`series_id`) REFERENCES `d3codes_series` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1


        ');

    }

    public function safeDown() {
        $this->execute('drop table d3codes_code_record');
        $this->execute('drop table d3codes_series');
        $this->execute('drop table d3codes_code');
    }
}
