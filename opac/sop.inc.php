<?php
/*
 * File: sop.inc.php
 * Created on Tue Jul 07 2026
 * Last Updated: Tue Jul 07 2026 11:32:10 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */


use SLiMS\DB;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// Pastikan header yang benar
header("Content-Type: text/html; charset=UTF-8");

do_checkIP('opac');
do_checkIP('opac-member');

$db = DB::getInstance();

// Load helper
require_once __DIR__ . '/../helper.php';

// ============================================================
// HANDLE VIEW COUNTER (AJAX)
// ============================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['action']) && $_GET['action'] == 'view') {
    $sop_id = isset($_GET['sop_id']) ? (int)$_GET['sop_id'] : 0;
    
    if ($sop_id > 0) {
        incrementSopView($dbs, $sop_id);
        $view_count = getSopViewCount($dbs, $sop_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'view_count' => $view_count
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
    }
    exit();
}

// ============================================================
// HANDLE GET PDF DATA (AJAX)
// ============================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['action']) && $_GET['action'] == 'getpdf') {
    $sop_id = isset($_GET['sop_id']) ? (int)$_GET['sop_id'] : 0;
    
    if ($sop_id > 0) {
        $query = $db->query("SELECT file_name, file_original FROM sop WHERE sop_id='{$sop_id}'");
        if ($query && $query->rowCount() > 0) {
            $data = $query->fetch(\PDO::FETCH_ASSOC);
            $file_url = SWB . 'files/sop/' . $data['file_name'];
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'file_url' => $file_url,
                'file_name' => $data['file_original']
            ]);
            exit();
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit();
}

// ============================================================
// TAMPILAN OPAC
// ============================================================

// Ambil semua SOP
$sql = "SELECT * FROM sop ORDER BY title ASC";
$sops = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('SOP Perpustakaan'); ?></title>
    
    <style>
        /* Sembunyikan elemen pencarian SLiMS default */
        .search-box, #searchBox, .opac-search-box, .search-form, .header-search,
        .sidebar, .filter-sidebar, .filter-panel {
            display: none !important;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0px;
            min-height: 100vh;
        }
        
        .sop-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .sop-header {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 25px;
            text-align: center;
        }
        
        .sop-header h1 {
            margin: 0 0 8px 0;
            color: #1a1a2e;
            font-size: 28px;
            font-weight: 700;
        }
        
        .sop-header p {
            color: #6c757d;
            font-size: 15px;
            margin: 0;
        }
        
        .sop-header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .sop-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .sop-item {
            background: white;
            border-radius: 12px;
            padding: 18px 25px;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sop-item:hover {
            border-color: #0d6efd;
            transform: translateX(6px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        
        .sop-item .sop-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 0;
        }
        
        .sop-item .sop-icon {
            font-size: 28px;
            color: #0d6efd;
            flex-shrink: 0;
        }
        
        .sop-item .sop-info {
            flex: 1;
            min-width: 0;
        }
        
        .sop-item .sop-info h3 {
            margin: 0 0 4px 0;
            font-size: 17px;
            color: #1a1a2e;
            font-weight: 600;
        }
        
        .sop-item .sop-info .sop-desc {
            margin: 0;
            font-size: 13px;
            color: #6c757d;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .sop-item .sop-info .sop-meta {
            margin: 4px 0 0 0;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .sop-item .sop-right {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
            margin-left: 15px;
        }
        
        .sop-item .sop-views {
            font-size: 13px;
            color: #95a5a6;
            white-space: nowrap;
        }
        
        .sop-item .sop-arrow {
            font-size: 20px;
            color: #adb5bd;
            transition: all 0.3s;
        }
        
        .sop-item:hover .sop-arrow {
            transform: translateX(5px);
            color: #0d6efd;
        }
        
        .no-sop {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 12px;
            color: #6c757d;
        }
        
        .no-sop .icon {
            font-size: 64px;
        }
        
        .no-sop p {
            margin-top: 20px;
            font-size: 18px;
        }
        
        /* ============================================================
           MODAL PDF VIEWER - BROWSER DEFAULT
           ============================================================ */
        #sopModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        #sopModal.active {
            display: flex;
        }
        
        .sop-modal-content {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 1100px;
            height: 92vh;
            max-height: 900px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            display: flex;
            flex-direction: column;
        }
        
        /* Header Modal */
        .sop-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 25px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            flex-shrink: 0;
        }
        
        .sop-modal-header h3 {
            margin: 0;
            font-size: 16px;
            color: #1a1a2e;
            font-weight: 600;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding-right: 15px;
        }
        
        .sop-modal-header .close-btn {
            background: none;
            border: none;
            color: #495057;
            font-size: 26px;
            cursor: pointer;
            padding: 0 8px;
            transition: color 0.2s;
            flex-shrink: 0;
            line-height: 1;
        }
        
        .sop-modal-header .close-btn:hover {
            color: #dc3545;
        }
        
        /* Body Modal - Tempat PDF */
        .sop-modal-body {
            flex: 1;
            background: #525659;
            position: relative;
            overflow: hidden;
        }
        
        /* Loading */
        .pdf-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #fff;
            z-index: 5;
        }
        
        .pdf-loading .spinner {
            border: 4px solid rgba(255,255,255,0.2);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        .pdf-loading p {
            margin: 0;
            font-size: 14px;
            opacity: 0.8;
        }
        
        /* Iframe PDF - Viewer Bawaan Browser */
        .sop-modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #fff;
            display: none;
        }
        
        .sop-modal-body iframe.active {
            display: block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .sop-item {
                padding: 15px;
                flex-direction: column;
                align-items: stretch;
            }
            
            .sop-item .sop-right {
                margin-left: 0;
                margin-top: 10px;
                justify-content: flex-end;
            }
            
            .sop-item .sop-info h3 {
                font-size: 15px;
            }
            
            #sopModal {
                padding: 10px;
            }
            
            .sop-modal-content {
                height: 95vh;
                max-height: none;
                border-radius: 12px;
            }
            
            .sop-modal-header {
                padding: 12px 15px;
            }
            
            .sop-modal-header h3 {
                font-size: 14px;
            }
            
            .sop-modal-header .close-btn {
                font-size: 22px;
            }
        }
        
        @media (max-width: 480px) {
            .sop-header {
                padding: 20px 15px;
            }
            
            .sop-header h1 {
                font-size: 22px;
            }
            
            .sop-item {
                padding: 12px 15px;
            }
            
            .sop-item .sop-left {
                gap: 10px;
            }
            
            .sop-item .sop-icon {
                font-size: 22px;
            }
            
            .sop-item .sop-info h3 {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="sop-container">
    <!-- Header -->
    <div class="sop-header">
        <div class="icon">📋</div>
        <h1><?php echo __('Standar Operasional Prosedur'); ?></h1>
        <p><?php echo __('Berikut adalah daftar SOP Perpustakaan yang dapat diakses'); ?></p>
    </div>
    
    <!-- Daftar SOP -->
    <?php if (empty($sops)): ?>
        <div class="no-sop">
            <div class="icon">📋</div>
            <p><?php echo __('Belum ada SOP yang tersedia'); ?></p>
        </div>
    <?php else: ?>
        <div class="sop-list">
            <?php foreach ($sops as $sop): ?>
                <div class="sop-item" onclick="openSOP(<?php echo $sop['sop_id']; ?>, '<?php echo htmlspecialchars(addslashes($sop['title'])); ?>')">
                    <div class="sop-left">
                        <span class="sop-icon">📄</span>
                        <div class="sop-info">
                            <h3><?php echo htmlspecialchars($sop['title']); ?></h3>
                            <?php if (!empty($sop['description'])): ?>
                                <p class="sop-desc"><?php echo nl2br(htmlspecialchars(substr($sop['description'], 0, 120))); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($sop['approval_date'])): ?>
                                <p class="sop-meta"><?php echo __('Disahkan'); ?>: <?php echo date('d M Y', strtotime($sop['approval_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="sop-right">
                        <span class="sop-views">👁️ <?php echo number_format($sop['view_count']); ?></span>
                        <span class="sop-arrow">→</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ============================================================
     MODAL PDF VIEWER
     ============================================================ -->
<div id="sopModal">
    <div class="sop-modal-content">
        <!-- Header -->
        <div class="sop-modal-header">
            <h3 id="sopModalTitle">📄 <?php echo __('Dokumen SOP'); ?></h3>
            <button class="close-btn" onclick="closeSOP()" title="<?php echo __('Tutup'); ?>">✕</button>
        </div>
        
        <!-- Body -->
        <div class="sop-modal-body" id="sopModalBody">
            <!-- Loading -->
            <div class="pdf-loading" id="pdfLoading">
                <div class="spinner"></div>
                <p><?php echo __('Memuat dokumen...'); ?></p>
            </div>
            
            <!-- PDF Viewer (Browser Default) -->
            <iframe id="sopFrame" src=""></iframe>
        </div>
    </div>
</div>

<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->
<script>
var currentSopId = 0;

// ============================================================
// BUKA SOP
// ============================================================
function openSOP(sopId, title) {
    currentSopId = sopId;
    var modal = document.getElementById('sopModal');
    var titleEl = document.getElementById('sopModalTitle');
    var loading = document.getElementById('pdfLoading');
    var frame = document.getElementById('sopFrame');
    
    // Set judul
    titleEl.textContent = '📄 ' + (title || 'Dokumen SOP');
    
    // Tampilkan modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Reset
    loading.style.display = 'block';
    loading.innerHTML = '<div class="spinner"></div><p><?php echo __('Memuat dokumen...'); ?></p>';
    frame.style.display = 'none';
    frame.src = '';
    
    // ============================================================
    // 1. UPDATE VIEW COUNTER (AJAX)
    // ============================================================
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?p=sop&ajax=1&action=view&sop_id=' + sopId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Update view count di card
                    var items = document.querySelectorAll('.sop-item');
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        var onclickAttr = item.getAttribute('onclick');
                        if (onclickAttr && onclickAttr.indexOf('openSOP(' + sopId) !== -1) {
                            var viewSpan = item.querySelector('.sop-views');
                            if (viewSpan) {
                                viewSpan.textContent = '👁️ ' + numberFormat(response.view_count);
                            }
                            break;
                        }
                    }
                }
            } catch(e) {
                console.error('Error updating view count:', e);
            }
        }
    };
    xhr.onerror = function() {
        console.error('AJAX view counter failed');
    };
    xhr.send();
    
    // ============================================================
    // 2. GET PDF URL (AJAX)
    // ============================================================
    var pdfXhr = new XMLHttpRequest();
    pdfXhr.open('GET', 'index.php?p=sop&ajax=1&action=getpdf&sop_id=' + sopId, true);
    pdfXhr.onload = function() {
        if (pdfXhr.status === 200) {
            try {
                var data = JSON.parse(pdfXhr.responseText);
                if (data.success) {
                    // Load PDF di iframe - viewer bawaan browser
                    frame.src = data.file_url;
                    frame.style.display = 'block';
                    frame.classList.add('active');
                    loading.style.display = 'none';
                } else {
                    loading.innerHTML = '<div style="font-size:48px; color:#dc3545;">❌</div><p style="color:#dc3545;"><?php echo __('Dokumen tidak ditemukan'); ?></p>';
                }
            } catch(e) {
                console.error('Error parsing PDF response:', e);
                loading.innerHTML = '<div style="font-size:48px; color:#dc3545;">❌</div><p style="color:#dc3545;"><?php echo __('Gagal memuat dokumen'); ?></p>';
            }
        } else {
            loading.innerHTML = '<div style="font-size:48px; color:#dc3545;">❌</div><p style="color:#dc3545;"><?php echo __('Gagal memuat dokumen'); ?></p>';
        }
    };
    pdfXhr.onerror = function() {
        loading.innerHTML = '<div style="font-size:48px; color:#dc3545;">❌</div><p style="color:#dc3545;"><?php echo __('Gagal memuat dokumen'); ?></p>';
    };
    pdfXhr.send();
}

// ============================================================
// TUTUP SOP
// ============================================================
function closeSOP() {
    var modal = document.getElementById('sopModal');
    var frame = document.getElementById('sopFrame');
    
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    // Reset iframe
    frame.src = '';
    frame.style.display = 'none';
    frame.classList.remove('active');
    
    // Reset loading
    var loading = document.getElementById('pdfLoading');
    loading.style.display = 'block';
    loading.innerHTML = '<div class="spinner"></div><p><?php echo __('Memuat dokumen...'); ?></p>';
    
    currentSopId = 0;
}

// ============================================================
// UTILITY
// ============================================================
function numberFormat(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ============================================================
// KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown', function(e) {
    // ESC untuk menutup
    if (e.key === 'Escape') {
        var modal = document.getElementById('sopModal');
        if (modal.classList.contains('active')) {
            closeSOP();
        }
    }
});

// ============================================================
// CEK APAKAH IFRAME SUDAH LOAD
// ============================================================
document.getElementById('sopFrame').addEventListener('load', function() {
    // Sembunyikan loading saat iframe selesai load
    var loading = document.getElementById('pdfLoading');
    if (loading.style.display !== 'none') {
        loading.style.display = 'none';
    }
});

// ============================================================
// RESIZE HANDLER
// ============================================================
window.addEventListener('resize', function() {
    // Tidak perlu melakukan apa-apa karena iframe akan menyesuaikan otomatis
});
</script>
</body>
</html>