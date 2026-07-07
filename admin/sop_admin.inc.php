<?php
/*
 * File: sop_admin.inc.php
 * Created on Tue Jul 07 2026
 * Last Updated: Tue Jul 07 2026 11:30:10 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */


use SLiMS\DB;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

// start the session
require SB . 'admin/default/session.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';

// Load helper
require_once __DIR__ . '/../helper.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">' . __('You don\'t have enough privileges to access this area!') . '</div>');
}

$plugin_id = $_GET['id'] ?? '';
$plugin_mod = $_GET['mod'] ?? '';

$selfUrl = pluginUrl(reset: true);
$selfUrlWithQuery = $selfUrl . (($_SERVER['QUERY_STRING'] ?? '') ? '&' . $_SERVER['QUERY_STRING'] : '');

// Path untuk upload file
$sop_dir = SB . 'files/sop/';
if (!file_exists($sop_dir)) {
    mkdir($sop_dir, 0777, true);
}

/* =========================
 * SAVE / UPDATE
 * ========================= */
if (isset($_POST['saveData']) && $can_write) {
    $title = trim(strip_tags($_POST['title'] ?? ''));
    $description = trim(strip_tags($_POST['description'] ?? ''));
    $approval_date = trim(strip_tags($_POST['approval_date'] ?? ''));
    $sop_id = isset($_POST['sop_id']) ? (int)$_POST['sop_id'] : 0;

    if ($title === '') {
        utility::jsToastr(__('SOP'), __('Title cannot be empty!'), 'error');
        exit();
    }

    $db = DB::getInstance();
    $now = date('Y-m-d H:i:s');
    $uid = $_SESSION['uid'] ?? 0;

    // Handle file upload
    $file_name = '';
    $file_original = '';
    $file_size = 0;

    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $file_ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        
        if ($file_ext == 'pdf') {
            $file_original = $_FILES['pdf_file']['name'];
            $file_name = 'sop_' . time() . '_' . md5($file_original) . '.pdf';
            $file_size = $_FILES['pdf_file']['size'];
            
            if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $sop_dir . $file_name)) {
                utility::jsToastr(__('SOP'), __('Failed to upload PDF file'), 'error');
                exit();
            }
        } else {
            utility::jsToastr(__('SOP'), __('Only PDF files are allowed'), 'error');
            exit();
        }
    }

    $query_str = $_POST['lastQueryStr'] ?? ('id=' . $plugin_id . '&mod=' . $plugin_mod);

    if ($sop_id > 0) {
        // UPDATE
        if (!empty($file_name)) {
            $old = $db->query("SELECT file_name FROM sop WHERE sop_id='{$sop_id}'");
            if ($old && $old->rowCount() > 0) {
                $old_data = $old->fetch(\PDO::FETCH_ASSOC);
                if ($old_data['file_name'] && file_exists($sop_dir . $old_data['file_name'])) {
                    unlink($sop_dir . $old_data['file_name']);
                }
            }
            
            $sql = "UPDATE sop SET 
                    title = ?,
                    description = ?,
                    approval_date = ?,
                    file_name = ?,
                    file_original = ?,
                    file_size = ?,
                    last_update = ?,
                    uid = ?
                    WHERE sop_id = ?";
            $params = [$title, $description, $approval_date, $file_name, $file_original, $file_size, $now, $uid, $sop_id];
        } else {
            $sql = "UPDATE sop SET 
                    title = ?,
                    description = ?,
                    approval_date = ?,
                    last_update = ?,
                    uid = ?
                    WHERE sop_id = ?";
            $params = [$title, $description, $approval_date, $now, $uid, $sop_id];
        }

        $stmt = $db->prepare($sql);
        $update = $stmt->execute($params);

        if ($update) {
            utility::jsToastr(__('SOP'), __('SOP data successfully updated'), 'success');
        } else {
            utility::jsToastr(__('SOP'), __('Update failed'), 'error');
        }
    } else {
        // INSERT
        $sql = "INSERT INTO sop (title, description, approval_date, file_name, file_original, file_size, upload_date, last_update, uid) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $insert = $stmt->execute([$title, $description, $approval_date, $file_name, $file_original, $file_size, $now, $now, $uid]);

        if ($insert) {
            utility::jsToastr(__('SOP'), __('New SOP data successfully saved'), 'success');
        } else {
            utility::jsToastr(__('SOP'), __('Save failed'), 'error');
        }
    }

    echo '<script>parent.$("#mainContent").simbioAJAX("' . $selfUrl . '&' . $query_str . '");</script>';
    exit();
}

/* =========================
 * DELETE
 * ========================= */
if (isset($_POST['itemID']) && isset($_POST['itemAction']) && $can_write) {
    $db = DB::getInstance();
    $ids = $_POST['itemID'];
    if (!is_array($ids)) {
        $ids = [$ids];
    }

    $deleted = 0;
    foreach ($ids as $id) {
        $id = (int)$id;
        $query = $db->query("SELECT file_name FROM sop WHERE sop_id='{$id}'");
        if ($query && $query->rowCount() > 0) {
            $data = $query->fetch(\PDO::FETCH_ASSOC);
            if ($data['file_name'] && file_exists($sop_dir . $data['file_name'])) {
                unlink($sop_dir . $data['file_name']);
            }
        }
        
        $stmt = $db->prepare("DELETE FROM sop WHERE sop_id = ?");
        if ($stmt->execute([$id])) {
            $deleted++;
        }
    }

    if ($deleted > 0) {
        utility::jsToastr(__('SOP'), __('Data deleted successfully'), 'success');
    } else {
        utility::jsToastr(__('SOP'), __('Delete failed'), 'error');
    }

    echo '<script>parent.$("#mainContent").simbioAJAX("' . $selfUrl . '&id=' . $plugin_id . '&mod=' . $plugin_mod . '");</script>';
    exit();
}

/* =========================
 * UI HEADER
 * ========================= */
?>
<div class="menuBox">
    <div class="menuBoxInner masterFileIcon">
        <div class="per_title"><h2><?php echo __('SOP Perpustakaan'); ?></h2></div>
        <div class="sub_section">
            <div class="btn-group">
                <a href="<?php echo $selfUrl; ?>" class="btn btn-default"><?php echo __('SOP List'); ?></a>
                <a href="<?php echo $selfUrl; ?>&action=detail" class="btn btn-default"><?php echo __('Add New SOP'); ?></a>
            </div>
            <form action="<?php echo $selfUrl; ?>" method="get" class="form-inline">
                <?php echo __('Search'); ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($plugin_id); ?>">
                <input type="hidden" name="mod" value="<?php echo htmlspecialchars($plugin_mod); ?>">
                <input type="text" name="keywords" class="form-control col-md-3" value="<?php echo htmlspecialchars($_GET['keywords'] ?? ''); ?>">
                <input type="submit" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default">
            </form>
        </div>
    </div>
</div>

<?php

/* =========================
 * FORM DETAIL
 * ========================= */
if (isset($_POST['detail']) || (isset($_GET['action']) && $_GET['action'] === 'detail')) {
    if (!$can_write) {
        die('<div class="errorBox">' . __('No write access') . '</div>');
    }

    $db = DB::getInstance();
    $itemID = (int)($_POST['itemID'] ?? $_GET['id'] ?? 0);
    $rec_d = [];

    if ($itemID > 0) {
        $query = $db->query("SELECT * FROM sop WHERE sop_id='{$itemID}'");
        if ($query && $query->rowCount() > 0) {
            $rec_d = $query->fetch(\PDO::FETCH_ASSOC);
        }
    }

    $form = new simbio_form_table_AJAX('mainForm', $selfUrlWithQuery, 'post', 'enctype="multipart/form-data"');
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    $form->addHidden('id', $plugin_id);
    $form->addHidden('mod', $plugin_mod);
    $form->addHidden('lastQueryStr', $_SERVER['QUERY_STRING'] ?? '');

    if ($itemID > 0 && !empty($rec_d)) {
        $form->edit_mode = true;
        $form->record_id = $itemID;
        $form->record_title = $rec_d['title'] ?? '';
        $form->submit_button_attr = 'name="saveData" value="' . __('Update') . '" class="s-btn btn btn-primary"';
        $form->addHidden('sop_id', $itemID);
    } else {
        $form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
        $form->addHidden('sop_id', '0');
    }

    // Judul SOP
    $form->addTextField('text', 'title', __('SOP Title') . '*', $rec_d['title'] ?? '', 'style="width:60%;" class="form-control" required');

    // Deskripsi
    $form->addTextField('textarea', 'description', __('Description'), $rec_d['description'] ?? '', 'style="width:60%; height:150px;" class="form-control"');

    // Tanggal Pengesahan - dengan datepicker
    $approval_date_value = !empty($rec_d['approval_date']) ? $rec_d['approval_date'] : date('Y-m-d');
    // Ganti bagian Approval Date dengan:
    $form->addTextField('date', 'approval_date', __('Approval Date'), $approval_date_value, 'style="width:100%;" class="form-control" id="approval_date"');

    // Upload File PDF
    $file_info = '';
    if (!empty($rec_d['file_name'])) {
        $file_url = SWB . 'files/sop/' . $rec_d['file_name'];
        $file_info = '<div class="alert alert-info">';
        $file_info .= __('Current file') . ': <a href="' . $file_url . '" target="_blank">' . htmlspecialchars($rec_d['file_original'] ?? $rec_d['file_name']) . '</a>';
        $file_info .= ' (' . number_format($rec_d['file_size'] / 1024, 2) . ' KB)';
        $file_info .= '</div>';
    }
    $form->addTextField('file', 'pdf_file', __('PDF File') . ($itemID > 0 ? '' : '*'), '', 'style="width:60%;" class="form-control" accept=".pdf"');
    if (!empty($file_info)) {
        $form->addAnything('', $file_info);
    }

    echo $form->printOut();
    
    // Tambahkan JavaScript untuk datepicker
    ?>
    <script type="text/javascript">
    // Tunggu hingga DOM siap
    jQuery(document).ready(function($) {
        // Cek apakah datepicker tersedia
        if ($.fn.datepicker) {
            // Inisialisasi datepicker untuk field approval_date
            $('#approval_date').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '2000:2030',
                showButtonPanel: true,
                showAnim: 'slideDown'
            });
        } else {
            // Fallback jika datepicker tidak tersedia
            console.log('Datepicker tidak tersedia, menggunakan input text biasa');
        }
    });
    </script>
    <?php
    return;
}

/* =========================
 * LIST (DataGrid)
 * ========================= */
$table_spec = 'sop';
$datagrid = new simbio_datagrid();

$datagrid->setSQLColumn(
    'sop_id',
    'title AS \'' . __('SOP Title') . '\'',
    'description AS \'' . __('Description') . '\'',
    'approval_date AS \'' . __('Approval Date') . '\'',
    'file_name AS \'' . __('File') . '\'',
    'view_count AS \'' . __('Views') . '\''
);

$datagrid->setSQLorder('sop_id DESC');

if (!empty($_GET['keywords'])) {
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $datagrid->setSQLCriteria("title LIKE '%{$keywords}%' OR description LIKE '%{$keywords}%'");
}

$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight:bold;"';
$datagrid->chbox_form_URL = $selfUrl;

$datagrid->custom_columns = [
    'File' => function($row) {
        if (!empty($row['file_name'])) {
            return '<a href="' . SWB . 'files/sop/' . $row['file_name'] . '" class="btn btn-xs btn-success" target="_blank">
                        <span class="glyphicon glyphicon-download"></span> PDF
                    </a>';
        }
        return '<span class="text-muted">-</span>';
    },
    'Views' => function($row) {
        return '<span class="badge" style="background:#3498db; color:white; padding:3px 10px; border-radius:20px; font-size:13px;">' . number_format($row['view_count']) . '</span>';
    },
    'Actions' => function($row) use ($selfUrl, $plugin_id, $plugin_mod) {
        return '<div class="btn-group btn-group-xs">
                    <a href="' . $selfUrl . '&action=detail&id=' . $row['sop_id'] . '&id=' . $plugin_id . '&mod=' . $plugin_mod . '" class="btn btn-warning" title="' . __('Edit') . '">
                        <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <form action="' . $selfUrl . '" method="post" style="display:inline;" onsubmit="return confirm(\'' . __('Are you sure want to delete this data?') . '\')">
                        <input type="hidden" name="id" value="' . $plugin_id . '">
                        <input type="hidden" name="mod" value="' . $plugin_mod . '">
                        <input type="hidden" name="itemAction" value="delete">
                        <input type="hidden" name="itemID[]" value="' . $row['sop_id'] . '">
                        <button type="submit" class="btn btn-danger" title="' . __('Delete') . '">
                            <span class="glyphicon glyphicon-trash"></span>
                        </button>
                    </form>
                </div>';
    }
];

echo $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read && $can_write));