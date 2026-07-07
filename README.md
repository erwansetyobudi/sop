```
# 📋 Plugin SOP - Standar Operasional Prosedur Perpustakaan

**Version:** 1.0.0  
**Author:** Your Name  
**License:** GPLv3

---

## 📌 Deskripsi

Plugin **SOP (Standar Operasional Prosedur)** adalah plugin untuk Sistem Informasi Perpustakaan (SLiMS) yang berfungsi untuk mengelola dan menampilkan SOP Perpustakaan. Plugin ini memungkinkan admin untuk mengelola SOP dan pengguna untuk melihat SOP dalam format PDF langsung di browser.

---

## ✨ Fitur

### 🔹 Admin Area (`Bibliography > SOP`)
- ✅ **CRUD SOP** - Tambah, Edit, Hapus SOP
- ✅ **Upload File PDF** - Unggah dokumen SOP dalam format PDF
- ✅ **Judul SOP** - Nama/identitas SOP
- ✅ **Deskripsi SOP** - Penjelasan singkat tentang SOP
- ✅ **Tanggal Pengesahan** - Tanggal pengesahan SOP (dengan datepicker)
- ✅ **View Counter** - Menghitung jumlah kali SOP dibuka
- ✅ **Datagrid** - Tabel data dengan style SLiMS
- ✅ **Pencarian** - Cari SOP berdasarkan judul atau deskripsi
- ✅ **Delete Multiple** - Hapus beberapa data sekaligus

### 🔹 OPAC Area (`index.php?p=sop`)
- ✅ **Daftar SOP** - Menampilkan semua SOP dalam bentuk card yang rapi
- ✅ **Informasi SOP** - Judul, deskripsi singkat, tanggal pengesahan
- ✅ **View Counter** - Menampilkan jumlah pembaca SOP
- ✅ **Modal Viewer** - SOP dibuka dalam modal popup
- ✅ **PDF Viewer** - Menggunakan viewer bawaan browser dengan fitur:
  - 🔍 Search / Cari teks
  - 🖨️ Print / Cetak
  - ⬇️ Download / Unduh
  - 🔍 Zoom in/out
  - 📑 Thumbnail halaman
  - 🔄 Rotate halaman
  - 📐 Fit to page
- ✅ **Keyboard Shortcut** - Tekan `ESC` untuk menutup modal
- ✅ **Responsive Design** - Tampilan menyesuaikan di semua perangkat

---

## 📁 Struktur Folder

```
plugins/sop/
├── sop.plugin.php          # File utama plugin
├── helper.php              # Fungsi helper
├── admin/
│   └── sop_admin.inc.php   # Halaman admin
├── opac/
│   └── sop.inc.php         # Halaman OPAC
└── migration/
    └── 1_CreateSopTable.php # Migrasi database
```

---

## 🗄️ Struktur Database

### Tabel `sop`

| Field | Type | Keterangan |
|-------|------|------------|
| `sop_id` | int(11) AUTO_INCREMENT | Primary Key |
| `title` | varchar(255) NOT NULL | Judul SOP |
| `description` | text | Deskripsi SOP |
| `approval_date` | date | Tanggal Pengesahan |
| `file_name` | varchar(255) | Nama file PDF |
| `file_original` | varchar(255) | Nama asli file PDF |
| `file_size` | int(11) | Ukuran file (bytes) |
| `view_count` | int(11) DEFAULT 0 | Jumlah pembaca |
| `upload_date` | datetime | Tanggal upload |
| `last_update` | datetime | Tanggal update terakhir |
| `uid` | int(11) | ID User penginput |

---

## 🔧 Instalasi

### 1. Download Plugin
- Download file plugin SOP
- Ekstrak folder `sop` ke direktori `plugins/` SLiMS Anda

### 2. Aktifkan Plugin
- Login ke SLiMS sebagai admin
- Buka menu **System > Plugins**
- Cari plugin **SOP (Standar Operasional Prosedur)**
- Klik tombol **Activate**

### 3. Buat Folder Upload
- Pastikan folder `files/sop/` ada dan memiliki permission 777
- Jika belum ada, buat folder tersebut

### 4. Akses Plugin
- **Admin**: Buka menu **Bibliography > SOP**
- **OPAC**: Buka `index.php?p=sop`

---

## 📝 Cara Penggunaan

### Admin

1. **Tambah SOP Baru**
   - Buka **Bibliography > SOP**
   - Klik tombol **Add New SOP**
   - Isi form:
     - **SOP Title**: Judul SOP (wajib)
     - **Description**: Deskripsi SOP
     - **Approval Date**: Tanggal pengesahan
     - **PDF File**: Upload file PDF (wajib untuk baru)
   - Klik **Save**

2. **Edit SOP**
   - Klik tombol **Edit** (icon pensil) pada data yang ingin diubah
   - Ubah data yang diperlukan
   - Klik **Update**

3. **Hapus SOP**
   - Centang data yang ingin dihapus
   - Klik tombol **Delete Selected**
   - Konfirmasi penghapusan

4. **Cari SOP**
   - Masukkan kata kunci pada kolom pencarian
   - Klik **Search**

### Pengguna (OPAC)

1. **Melihat Daftar SOP**
   - Buka `index.php?p=sop`
   - Semua SOP akan ditampilkan dalam bentuk card

2. **Membuka SOP**
   - Klik pada card SOP yang ingin dibaca
   - Modal akan muncul dengan tampilan PDF

3. **Membaca PDF**
   - Gunakan toolbar viewer bawaan browser untuk:
     - Mencari teks
     - Mencetak
     - Mengunduh
     - Zoom in/out
     - Melihat thumbnail
   - Tekan `ESC` atau klik ✕ untuk menutup modal

---

## 🖥️ Tampilan

### Admin Area

```
┌─────────────────────────────────────────────────────────────┐
│  📋 SOP Perpustakaan                                       │
│  [SOP List] [Add New SOP]   🔍 [Search...]                 │
├─────────────────────────────────────────────────────────────┤
│  # │ Title         │ Description │ Date      │ File │ Views│
├─────────────────────────────────────────────────────────────┤
│  1 │ SOP Membaca   │ Prosedur... │ 2024-01-01 │ PDF  │  150 │
│  2 │ SOP Pinjam    │ Prosedur... │ 2024-01-02 │ PDF  │  200 │
│  3 │ SOP Kembali   │ Prosedur... │ 2024-01-03 │ PDF  │  180 │
└─────────────────────────────────────────────────────────────┘
```

### OPAC Area

```
┌─────────────────────────────────────────────────────────────┐
│  📋 Standar Operasional Prosedur                           │
│  Berikut adalah daftar SOP Perpustakaan yang dapat diakses │
├─────────────────────────────────────────────────────────────┤
│  📄 SOP Membaca                                      👁️ 150│
│     Prosedur membaca di perpustakaan...                    │
│     Disahkan: 01 Jan 2024                                 │
│  ────────────────────────────────────────────────────────── │
│  📄 SOP Peminjaman Buku                              👁️ 200│
│     Prosedur peminjaman buku...                           │
│     Disahkan: 02 Jan 2024                                 │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔒 Hak Akses

| Role | Hak Akses |
|------|-----------|
| **Admin** | CRUD SOP, Upload PDF, Hapus Data |
| **Pengguna (OPAC)** | Melihat daftar SOP, Membaca PDF |

---

## 🛠️ Teknologi

- **PHP 7.4+** - Backend
- **MySQL/MariaDB** - Database
- **HTML5 + CSS3** - Frontend
- **JavaScript** - Interaktif
- **SLiMS Framework** - Integrasi

---

## 📦 Dependensi

- SLiMS 9.3.0 atau lebih baru
- PHP dengan ekstensi:
  - PDO MySQL
  - JSON
  - File Upload

---

## 📄 Lisensi

Plugin ini dirilis di bawah lisensi **GPLv3** - silahkan gunakan, modifikasi, dan distribusikan dengan tetap mencantumkan lisensi asli.

---

