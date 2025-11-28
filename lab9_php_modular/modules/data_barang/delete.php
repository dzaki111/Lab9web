<?php
// modules/data_barang/delete.php (Isi hapus.php lama)

// Fungsi untuk menangani redirect setelah selesai
function redirect_list() {
    header('location: index.php?page=data_barang/list'); // Redirect ke halaman list
    exit;
}

// Cek apakah ID ada di URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $sql = "DELETE FROM data_barang WHERE id_barang = '{$id}'"; // Query DELETE
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Gagal menghapus data: " . mysqli_error($conn));
    }
}

// Redirect kembali ke index.php?page=data_barang/list setelah selesai
redirect_list();
?>