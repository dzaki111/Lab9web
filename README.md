# Lab9web
#### Nama   = DZAKI ARIF RAHMAN  
#### Kelas  = TI.24.A4  
#### NIM    = 312410312  
#### Matkul  = Pemograman Web 1 


Project ini mengimplementasikan konsep Modularisasi dan Routing dalam PHP, mengubah struktur program yang sebelumnya terfragmentasi menjadi arsitektur yang terpusat dan terorganisir. Implementasi ini mencakup pemisahan kode koneksi, tampilan (Header/Footer), logika bisnis (Modules), dan penambahan fitur Otentikasi (Login/Logout) untuk melindungi halaman manajemen data. Database yang digunakan adalah **latihan2** pada MySQL.
## Struktur Proyek

![WhatsApp Image 2025-11-27 at 22 20 50_889eff33](https://github.com/user-attachments/assets/4ba32e89-4ba3-473b-9946-3fa389b0db03)




## 1\. Koneksi Database

Langkah pertama dalam modularisasi adalah menyusun *file* ke dalam direktori yang logis (`config`, `views`, `modules`, `assets`) dan mengonsolidasikan pengaturan koneksi. File **`config/database.php`** bertanggung jawab untuk menyimpan variabel koneksi (*host*, *user*, *pass*, *db*) dan memastikan koneksi ke *database* **latihan2** berhasil, sementara *router* utama (`index.php`) akan memuatnya pada awal eksekusi program.

### 📁 `config/database.php`

```php
<?php
// config/database.php
$host = "localhost"; 
$user = "root";    
$pass = "";      
$db = "latihan2";  // Nama database yang sudah dibuat

$conn = mysqli_connect($host, $user, $pass, $db); // Melakukan koneksi

if ($conn == false)
{
    // Hentikan eksekusi jika koneksi gagal
    die("Koneksi ke server gagal: " . mysqli_connect_error());
}
?>
```

-----

## 2\. Router Utama (`index.php`) dan Otentikasi

*Router* utama (**`index.php`**) adalah *file* terpenting yang menentukan halaman mana yang harus dimuat berdasarkan parameter URL (`?page=...`). Pada implementasi ini, `index.php` juga berfungsi sebagai **Akses Guard**, yang bertugas memeriksa status sesi (`$_SESSION['is_login']`). Jika *user* belum *login* dan mencoba mengakses halaman privat (seperti `data_barang/list`), *router* akan secara otomatis mengarahkannya ke halaman *login* (`auth/login`), memastikan data terlindungi.

### 📄 `index.php`

```php
<?php
// index.php (Router Utama, dengan Auth)

// Aktifkan session harus paling atas
session_start(); 

require_once 'config/database.php'; // Koneksi database dimuat

// Definisikan halaman yang bisa diakses publik (tanpa login)
$public_pages = [
    'auth/login', 
    'auth/logout' 
];

// 1. Logika Routing
$page = $_GET['page'] ?? 'data_barang/list'; // Default ke list data

// Bersihkan input
$page = preg_replace('/[^a-zA-Z0-9_\/]/', '', $page);
$module_path = 'modules/' . $page . '.php';

// 2. Pemeriksaan Akses (Guard)
$is_public = in_array($page, $public_pages);

if (!$is_public && !isset($_SESSION['is_login'])) {
    // Jika tidak login DAN mencoba mengakses halaman privat, paksa ke login
    header('location: index.php?page=auth/login');
    exit;
} else if ($page == 'auth/login' && isset($_SESSION['is_login'])) {
    // Jika sudah login tapi mengakses halaman login, arahkan ke home
    header('location: index.php');
    exit;
}

// 3. Load Module
if (file_exists($module_path)) {
    // Set Title
    $title = ucwords(str_replace(['/', '_'], ' ', $page)); 
    
    require 'views/header.php';
    
    // Muat konten module
    require $module_path;
    
    require 'views/footer.php';
} else {
    // Halaman 404 sederhana
    require 'views/header.php';
    echo '<h2 style="color: #dc3545; text-align: center;">404 Not Found</h2>';
    echo '<p style="text-align: center;">Halaman modul yang Anda cari tidak ditemukan: ' . htmlspecialchars($module_path) . '</p>';
    require 'views/footer.php';
}
?>
```

-----

## 3\. Komponen Tampilan (Views)

Komponen *Views* (**`views/header.php`** dan **`views/footer.php`**) berfungsi sebagai *template* tampilan yang dibagikan oleh semua modul. **`header.php`** menampung tag `<head>`, *link* CSS, struktur awal HTML, *header* visual (kotak biru), dan logika navigasi Login/Logout. Sementara itu, **`footer.php`** menutup tag HTML yang terbuka dan berisi informasi hak cipta. Pemisahan ini memastikan konsistensi tampilan di seluruh aplikasi.

### 📄 `views/header.php`

```php
<?php
// views/header.php (Sudah dimodifikasi untuk Auth dan penempatan pesan sambutan)

// Pastikan session sudah dimulai di index.php
$is_logged_in = isset($_SESSION['is_login']) && $_SESSION['is_login'];
$title = $title ?? 'Aplikasi Data Barang';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title); ?></title>
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" /> 
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>Data Barang (Modular)</h1>
            </div>
            
            <?php if ($is_logged_in): ?>
            <div class="welcome-message">
                Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>!
            </div>
            <?php endif; ?>

            <nav style="text-align: center; margin-bottom: 20px;">
                <?php if ($is_logged_in): ?>
                    <a href="index.php?page=data_barang/list" class="btn-tambah" style="margin-right: 10px;">Lihat Data</a>
                    <a href="index.php?page=data_barang/add" class="btn-tambah">Tambah Data</a>
                    <a href="index.php?page=auth/logout" class="btn-tambah" style="background-color: var(--danger-color);">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=auth/login" class="btn-tambah">Login Admin</a>
                <?php endif; ?>
            </nav>
        </header>
        <div class="main">
```

### 📄 `views/footer.php`

```php
<?php
// views/footer.php
?>
        </div>
        <footer>
            <p style="text-align: center; margin-top: 20px;">&copy; 2025, Modularisasi PHP - Lab 9</p>
        </footer>
    </div>
</body>
</html>
```

-----

## 4\. Modul Data Barang (`modules/data_barang/`)

Modul ini menampung semua logika CRUD (Create, Read, Update, Delete) yang sebelumnya tersebar di `index.php`, `tambah.php`, `ubah.php`, dan `hapus.php`. Semua modul ini kini menggunakan variabel koneksi global (`$conn`) yang dimuat oleh `index.php` dan semua *link* diarahkan menggunakan skema *routing* (`index.php?page=module/action`).

### 📄 `modules/data_barang/list.php` (Read)

```php
<?php
// modules/data_barang/list.php (Menampilkan data)

$sql  = 'SELECT * FROM data_barang ORDER BY id_barang DESC'; 
$result  = mysqli_query($conn, $sql); 
?>

<table>
    <tr>
        <th>Gambar</th>
        <th>Nama Barang</th>
        <th>Kategori</th>
        <th>Harga Jual</th>
        <th>Harga Beli</th>
        <th>Stok</th>
        <th>Aksi</th>
    </tr>
    <?php if($result && mysqli_num_rows($result) > 0): ?>
    <?php while($row = mysqli_fetch_array($result)): ?>
    <tr>
        <td>
            <?php if ($row['gambar']): ?>
                <img src="assets/gambar/<?= htmlspecialchars($row['gambar']);?>" alt="<?= htmlspecialchars($row['nama']);?>">
            <?php else: ?>
                Tidak Ada Gambar
            <?php endif; ?>
        </td> 
        
        <td><?= htmlspecialchars($row['nama']);?></td>
        <td><?= htmlspecialchars($row['kategori']);?></td>
        <td><?= htmlspecialchars($row['harga_jual']);?></td>
        <td><?= htmlspecialchars($row['harga_beli']);?></td>
        <td><?= htmlspecialchars($row['stok']);?></td>
        <td>
            <a href="index.php?page=data_barang/edit&id=<?= $row['id_barang'];?>">Ubah</a> 
            
            <a href="index.php?page=data_barang/delete&id=<?= $row['id_barang'];?>" onclick="return confirm('Yakin akan menghapus data ini?')">Hapus</a>
        </td>
    </tr>
    <?php endwhile; else: ?>
    <tr>
        <td colspan="7">Belum ada data di database.</td>
    </tr>
    <?php endif; ?>
</table>
```

### 📄 `modules/data_barang/add.php` (Create)

```php
<?php
// modules/data_barang/add.php (Menambah data)

function redirect_list() {
    header('location: index.php?page=data_barang/list'); 
    exit;
}

if (isset($_POST['submit']))
{
    // ... (Logika pengambilan data form) ...
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga_jual = mysqli_real_escape_string($conn, $_POST['harga_jual']);
    $harga_beli = mysqli_real_escape_string($conn, $_POST['harga_beli']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $file_gambar = $_FILES['file_gambar'];
    $gambar  = null;

    // Proses upload gambar
    if ($file_gambar ['error'] == 0) 
    {
        $filename  = str_replace(' ', '_', $file_gambar ['name']);
        // Path upload disesuaikan ke folder assets/gambar/ dari root project
        $destination = dirname(dirname(dirname(__FILE__))) . '/assets/gambar/' . $filename; 

        if(move_uploaded_file($file_gambar ['tmp_name'], $destination)) 
        {
            $gambar = $filename;
        }
    }
    
    // Query INSERT
    $sql = "INSERT INTO data_barang (nama, kategori, harga_jual, harga_beli, stok, gambar) 
            VALUES ('{$nama}', '{$kategori}', '{$harga_jual}', '{$harga_beli}', '{$stok}', '{$gambar}')";
    
    $result  = mysqli_query($conn, $sql);
    
    if ($result) {
        redirect_list(); 
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>

<h1 style="text-align: center;">Tambah Barang</h1>
<form method="post" action="index.php?page=data_barang/add" enctype="multipart/form-data">
    <div class="input">
        <label>Nama Barang</label>
        <input type="text" name="nama" required/>
    </div>
    <div class="input">
        <label>Kategori</label>
        <select name="kategori" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="Komputer">Komputer</option>
            <option value="Elektronik">Elektronik</option>
            <option value="Hand Phone">Hand Phone</option>
        </select>
    </div>
    <div class="input">
        <label>Harga Jual</label>
        <input type="number" name="harga_jual" required/>
    </div>
    <div class="input">
        <label>Harga Beli</label>
        <input type="number" name="harga_beli" required/>
    </div>
    <div class="input">
        <label>Stok</label>
        <input type="number" name="stok" required/>
    </div>
    <div class="input">
        <label>File Gambar</label>
        <input type="file" name="file_gambar" />
    </div>
    <div class="submit">
        <input type="submit" name="submit" value="Simpan" />
    </div>
</form>
```

### 📄 `modules/data_barang/edit.php` (Update)

```php
<?php
// modules/data_barang/edit.php (Mengubah data)

function is_select($val, $var) {
    if ($var == $val) return 'selected="selected"';
    return '';
}

function redirect_list() {
    header('location: index.php?page=data_barang/list'); 
    exit;
}

// 1. Logika Pemrosesan Form UPDATE saat di-submit
if (isset($_POST['submit']))
{
    // ... (Logika pengambilan data form dan upload gambar) ...
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga_jual = mysqli_real_escape_string($conn, $_POST['harga_jual']);
    $harga_beli = mysqli_real_escape_string($conn, $_POST['harga_beli']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $file_gambar = $_FILES['file_gambar'];
    $gambar  = null;

    // Proses upload gambar baru (jika ada)
    if ($file_gambar ['error'] == 0)
    {
        $filename  = str_replace(' ', '_', $file_gambar['name']);
        $destination = dirname(dirname(dirname(__FILE__))) . '/assets/gambar/' . $filename;

        if (move_uploaded_file($file_gambar['tmp_name'], $destination))
        {
            $gambar = $filename; 
        }
    }
    
    // Query UPDATE
    $sql = 'UPDATE data_barang SET ';
    $sql.= "nama = '{$nama}', kategori = '{$kategori}', ";
    $sql.= "harga_jual = '{$harga_jual}', harga_beli = '{$harga_beli}', stok = '{$stok}' ";
    
    if (!empty($gambar)) 
    {
        $sql.=", gambar = '{$gambar}' ";
    }
    
    $sql.= "WHERE id_barang = '{$id}'"; 
    
    $result  = mysqli_query($conn, $sql);
    
    if ($result) {
        redirect_list();
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($conn);
    }
}

// 2. Logika Pengambilan Data untuk ditampilkan di form
if (!isset($_GET['id'])) {
    redirect_list();
}
$id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT * FROM data_barang WHERE id_barang = '{$id}'";
$result  = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die('Error: Data tidak ditemukan.');
}
$data  = mysqli_fetch_array($result);

?>
<h1 style="text-align: center;">Ubah Barang</h1>
<form method="post" action="index.php?page=data_barang/edit" enctype="multipart/form-data">
    <div class="input">
        <label>Nama Barang</label>
        <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']);?>" required/>
    </div>
    <div class="input">
        <label>Kategori</label>
        <select name="kategori" required>
            <option <?php echo is_select($data['kategori'], 'Komputer'); ?> value="Komputer">Komputer</option>
            <option <?php echo is_select($data['kategori'], 'Elektronik');?> value="Elektronik">Elektronik</option>
            <option <?php echo is_select($data['kategori'], 'Hand Phone'); ?> value="Hand Phone">Hand Phone</option>
        </select>
    </div>
    <div class="input">
        <label>Harga Jual</label>
        <input type="number" name="harga_jual" value="<?php echo htmlspecialchars($data['harga_jual']);?>" required/>
    </div>
    <div class="input">
        <label>Harga Beli</label>
        <input type="number" name="harga_beli" value="<?php echo htmlspecialchars($data['harga_beli']);?>" required/>
    </div>
    <div class="input">
        <label>Stok</label>
        <input type="number" name="stok" value="<?php echo htmlspecialchars($data['stok']);?>" required/>
    </div>
    <div class="input">
        <label>File Gambar (Kosongkan jika tidak diubah)</label>
        <input type="file" name="file_gambar" />
        <?php if ($data['gambar']): ?>
            <p>Gambar Saat Ini: 
                <img src="assets/gambar/<?php echo htmlspecialchars($data['gambar']);?>" style="max-width: 100px; max-height: 100px; display: block; margin-top: 10px;">
            </p>
        <?php endif; ?>
    </div>
    <div class="submit">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id_barang']);?>" />
        <input type="submit" name="submit" value="Simpan Perubahan" />
    </div>
</form>
```

### 📄 `modules/data_barang/delete.php` (Delete)

```php
<?php
// modules/data_barang/delete.php (Menghapus data)

function redirect_list() {
    header('location: index.php?page=data_barang/list'); 
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $sql = "DELETE FROM data_barang WHERE id_barang = '{$id}'"; // Query DELETE
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Gagal menghapus data: " . mysqli_error($conn));
    }
}

redirect_list();
?>
```

-----

## 5\. Modul Otentikasi Lanjutan (`modules/auth/`)

Selain *Login* dan *Logout*, modul ini memastikan manajemen sesi dilakukan dengan benar. *Router* utama sudah melindungi semua halaman selain Auth, menjadikan *project* lebih aman.

### 🔑 `modules/auth/logout.php`

```php
<?php
// modules/auth/logout.php

session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Arahkan ke halaman login
header('location: index.php?page=auth/login');
exit;
?>
```

-----


## Screenshot Tampilan Akhir

Berikut adalah tampilan akhir *project* setelah *login* berhasil, menunjukkan *header* biru-putih dan pesan sambutan yang sudah diatur penempatannya.

![WhatsApp Image 2025-11-28 at 18 14 34_45816ba7](https://github.com/user-attachments/assets/41a997c3-cd70-4683-a414-574706af215b)


![WhatsApp Image 2025-11-27 at 22 23 12_46d2cd74](https://github.com/user-attachments/assets/2e37d53d-6f47-4a40-8c13-9126f036d6c4)

![WhatsApp Image 2025-11-28 at 18 16 16_d4acd467](https://github.com/user-attachments/assets/c44f5398-5d46-4065-bb10-11304fee3fd3)
