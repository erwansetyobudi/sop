<?php
/**
 * Plugin Name: SOP (Standar Operasional Prosedur)
 * Plugin URI: https://github.com/erwansetyobudi/sop
 * Description: Plugin untuk mengelola SOP Perpustakaan
 * Version: 1.0.0
 * Author: Erwan Setyo Budi
 * Author URI: https://github.com/erwansetyobudi/
 */

use SLiMS\Plugins;
use SLiMS\DB;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

$plugins = Plugins::getInstance();

// Register menu di admin bibliografi
$plugins->registerMenu('bibliography', 'SOP', __DIR__ . '/admin/sop_admin.inc.php');

// Register halaman OPAC
$plugins->registerMenu('opac', 'sop', __DIR__ . '/opac/sop.inc.php');

// Register migration
$plugins->register(Plugins::ADMIN_SESSION_AFTER_START, function() {
    $db = DB::getInstance();
    try {
        $tableExists = $db->query("SHOW TABLES LIKE 'sop'");
        if ($tableExists->rowCount() == 0) {
            require_once __DIR__ . '/migration/1_CreateSopTable.php';
            $migration = new CreateSopTable();
            $migration->up();
        }
    } catch (Exception $e) {
        // Error handling
    }
});