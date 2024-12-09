<?php
// order.php - Halaman untuk melihat pesanan pengguna

require_once('../includes/config.php');

// Cek jika session belum ada, redirect ke halaman login
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Inisialisasi cURL untuk mengambil data order dari API Laravel berdasarkan user_id
$curl = curl_init();

// Set opsi cURL untuk melakukan GET request ke API Laravel untuk mengambil pesanan berdasarkan user_id
curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/orders/user/' . $_SESSION['id'], // API untuk mengambil pesanan berdasarkan user_id
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
    ),
));

// Eksekusi request cURL dan simpan respons
$response = curl_exec($curl);

// Cek jika ada error pada cURL
if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
}

// Tutup koneksi cURL
curl_close($curl);

// Cek apakah response dari API berhasil diterima
if ($response) {
    $orderData = json_decode($response, true); // Decode response JSON
    $orders = $orderData['data']; // Ambil data orders dari response
} else {
    $orders = [];
    $error_message = "Gagal mengambil data pesanan!";
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - OrderApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">OrderApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Menu</a> <!-- Menu Button -->
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order.php">Order</a> <!-- Order Button -->
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2>Daftar Pesanan</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Menampilkan Daftar Order -->
        <div class="row">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Pesanan ID: <?php echo $order['id']; ?></h5>
                                <p class="card-text"><strong>Tanggal:</strong> <?php echo $order['created_at']; ?></p>
                                <p class="card-text"><strong>Status:</strong> <?php echo $order['status']; ?></p>
                                <ul>
                                    <?php foreach ($order['items'] as $item):
                                        $menu = $item['menu']; ?>
                                        <li>

                                            <?php echo $menu['name']; ?> - <?php echo $item['quantity']; ?> x Rp
                                            <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <p class="card-text"><strong>Total Harga:</strong> Rp
                                    <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Tidak ada pesanan yang ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>