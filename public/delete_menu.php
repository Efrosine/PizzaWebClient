<?php
// delete_menu.php

require_once('../includes/config.php');

// Cek apakah admin sudah login
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Cek apakah ada parameter 'id' dalam URL
if (!isset($_GET['id'])) {
    header('Location: manage_menus.php');
    exit();
}

$id = $_GET['id'];

// Inisialisasi cURL untuk menghapus menu berdasarkan ID
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/menus/' . $id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);
if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
}

curl_close($curl);

// Proses response dari API
if ($response) {
    $responseData = json_decode($response, true);
    if ($responseData['message'] === 'Menu berhasil dihapus') {
        // Redirect ke halaman manajemen menu setelah berhasil menghapus
        header('Location: manage_menus.php');
        exit();
    } else {
        $error_message = 'Gagal menghapus menu.';
    }
} else {
    $error_message = 'Tidak ada respon dari server.';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Menu - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>


    <div class="container mt-5">
        <h2>Hapus Menu</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php else: ?>
            <div class="alert alert-success">Menu berhasil dihapus.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>