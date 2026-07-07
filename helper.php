<?php
/*
 * File: helper.php
 * Created on Tue Jul 07 2026
 * Last Updated: Tue Jul 07 2026 11:32:40 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */


/**
 * Helper functions for SOP plugin
 */

if (!function_exists('getSopFileUrl')) {
    function getSopFileUrl($file_name) {
        if (empty($file_name)) {
            return '';
        }
        return SWB . 'files/sop/' . $file_name;
    }
}

if (!function_exists('incrementSopView')) {
    function incrementSopView($dbs, $sop_id) {
        $sop_id = (int)$sop_id;
        $ip = ip();
        $member_id = isset($_SESSION['member_id']) ? $_SESSION['member_id'] : null;
        $user_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : null;
        $now = date('Y-m-d H:i:s');
        
        $dbs->query("UPDATE sop SET view_count = view_count + 1 WHERE sop_id = '{$sop_id}'");
        
        return true;
    }
}

if (!function_exists('getSopViewCount')) {
    function getSopViewCount($dbs, $sop_id) {
        $sop_id = (int)$sop_id;
        $query = $dbs->query("SELECT view_count FROM sop WHERE sop_id = '{$sop_id}'");
        if ($query && $query->num_rows > 0) {
            $data = $query->fetch_assoc();
            return (int)$data['view_count'];
        }
        return 0;
    }
}