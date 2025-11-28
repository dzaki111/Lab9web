# Lab9web



*Project* ini mengimplementasikan konsep Modularisasi dan Routing dalam PHP, mengubah struktur program yang sebelumnya terfragmentasi menjadi arsitektur yang terpusat dan terorganisir. Implementasi ini mencakup pemisahan kode koneksi, tampilan (Header/Footer), logika bisnis (Modules), dan penambahan fitur Otentikasi (Login/Logout) untuk melindungi halaman manajemen data. Database yang digunakan adalah **latihan2** pada MySQL.
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

## 3\. Modul Otentikasi (`auth/login.php` & `auth/logout.php`)

Modul Login (**`modules/auth/login.php`**) berfungsi menampilkan *form* dan memproses data *username* dan *password* dari *user*. Setelah berhasil login (dengan user: **admin** dan *password*: **admin123**), *session* akan dibuat (`$_SESSION['is_login'] = true`), dan *user* diarahkan ke halaman utama. Sebaliknya, modul Logout (**`modules/auth/logout.php`**) bertugas menghapus semua data *session* dan mengarahkan *user* kembali ke halaman *login*, efektif mengakhiri sesi.

### 🔑 `modules/auth/login.php`

```php
<?php
// modules/auth/login.php

// Cek jika user sudah login, arahkan ke halaman utama
if (isset($_SESSION['is_login'])) {
    header('location: index.php');
    exit;
}

$error = '';

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 

    // Query untuk mencari user
    $sql = "SELECT * FROM user WHERE username = '{$username}' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Cek password (DEMO: Cek teks biasa 'admin123')
        if ($password === 'admin123' && $user['username'] === 'admin') { 
            session_start();
            $_SESSION['is_login'] = true; 
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];

            header('location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Username tidak ditemukan.';
    }
}
?>

<h1 style="text-align: center;">Login Administrator</h1>

<form method="post" action="index.php?page=auth/login">
    <?php if ($error): ?>
        <p style="color: var(--danger-color); text-align: center; margin-bottom: 20px; border: 1px solid var(--danger-color); padding: 10px; border-radius: 5px;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <div class="input">
        <label>Username</label>
        <input type="text" name="username" required/>
    </div>
    <div class="input">
        <label>Password</label>
        <input type="password" name="password" required/>
    </div>
    <div class="submit">
        <input type="submit" name="submit" value="Login" />
    </div>
</form>

<p style="text-align: center; margin-top: 20px;">
    *Untuk demo: Username: admin, Password: admin123
</p>
```

-----

## 4\. Tampilan dan Navigasi (`views/header.php`)

*File* **`views/header.php`** tidak hanya memuat *link* ke *stylesheet* (`assets/css/style.css`), tetapi kini juga memegang peran penting dalam menampilkan tampilan visual dan navigasi utama. Bagian ini bertanggung jawab untuk menampilkan kotak judul biru **"Data Barang (Modular)"** dan secara dinamis menampilkan pesan sambutan **"Selamat datang, [Username]\!"** serta tombol **Logout** atau **Login** berdasarkan status sesi *user*.

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

-----

## 📸 Screenshot Tampilan Akhir

Berikut adalah tampilan akhir *project* setelah *login* berhasil, menunjukkan *header* biru-putih dan pesan sambutan yang sudah diatur penempatannya.

![WhatsApp Image 2025-11-27 at 22 23 12_46d2cd74](https://github.com/user-attachments/assets/2e37d53d-6f47-4a40-8c13-9126f036d6c4)

