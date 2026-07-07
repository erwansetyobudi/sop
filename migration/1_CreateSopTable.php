<?php
/*
 * File: 1_CreateSopTable.php
 * Created on Tue Jul 07 2026
 * Last Updated: Tue Jul 07 2026 11:31:54 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */


use SLiMS\Migration\Migration;
use SLiMS\DB;

class CreateSopTable extends Migration
{
    function up() {
        $db = DB::getInstance();
        
        $db->query("
            CREATE TABLE IF NOT EXISTS `sop` (
                `sop_id` int NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `description` text,
                `approval_date` date DEFAULT NULL,
                `file_name` varchar(255),
                `file_original` varchar(255),
                `file_size` int DEFAULT 0,
                `view_count` int NOT NULL DEFAULT 0,
                `upload_date` datetime NOT NULL,
                `last_update` datetime DEFAULT NULL,
                `uid` int DEFAULT NULL,
                PRIMARY KEY (`sop_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    function down() {
        $db = DB::getInstance();
        $db->query("DROP TABLE IF EXISTS `sop`");
    }
}