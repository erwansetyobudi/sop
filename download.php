<?php
/*
 * File: download.php
 * Created on Tue Jul 07 2026
 * Last Updated: Tue Jul 07 2026 11:32:22 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */


use SLiMS\DB;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

require_once __DIR__ . '/../sysconfig.inc.php';

$sop_id = isset($_GET['sop_id']) ? (int)$_GET['sop_id'] : 0;

if ($sop_id <= 0) {
    die('Invalid SOP ID');
}

$db = DB::getInstance();
$query = $db->query("SELECT file_name, file_original FROM sop WHERE sop_id='{$sop_id}'");

if (!$query || $query->rowCount() == 0) {
    die('SOP not found');
}

$data = $query->fetch(\PDO::FETCH_ASSOC);
$file_path = SB . 'files/sop/' . $data['file_name'];

if (!file_exists($file_path)) {
    die('File not found');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $data['file_original'] . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();