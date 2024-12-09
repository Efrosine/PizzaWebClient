<?php
// add_menu.php

require_once('../includes/config.php');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $category_id = (int) $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

    $data = array(
        'category_id' => $category_id,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'image_url' => $image_url
    );



    // Inisialisasi cURL untuk menambah menu
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => API_URL . '/menus',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
        CURLOPT_POSTFIELDS => json_encode($data),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo 'Curl error: ' . curl_error($curl);
    }

    curl_close($curl);

    if ($response) {
        // var_dump($response);
        $responseData = json_decode($response, true);
        // var_dump($responseData);
        if ($responseData['message'] === 'Menu berhasil ditambahkan') {
            header('Location: manage_menus.php'); // Redirect ke halaman manajemen menu setelah berhasil
            exit();
        } else {
            $error_message = 'Gagal menambahkan menu.' . $data . $responseData['message'];
        }
    } else {
        $error_message = 'Tidak ada respon dari server.';
    }


}
// Inisialisasi cURL untuk mengambil data kategori dari API Laravel
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/categories', // Gantilah dengan URL API Laravel yang sesuai
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

$response = curl_exec($curl);

// Cek jika ada error pada cURL
if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
}

// Tutup koneksi cURL
curl_close($curl);

// Cek apakah response dari API berhasil diterima
if ($response) {
    $categoriesData = json_decode($response, true); // Decode response JSON
    $categories = $categoriesData['data']; // Ambil data kategori dari response
} else {
    $categories = [];
    $error_message = "Gagal mengambil data kategori!";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="manage_menus.php">Manage Menus</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_orders.php">Manage Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">Manage Users</a>
                    </li>

                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Tambah Menu</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="add_menu.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Menu</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <input type="number" class="form-control" id="price" name="price" required>
            </div>
            <!-- Dropdown Category -->
            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['id'] . $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">Tidak ada kategori tersedia</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="image_url" class="form-label">URL Gambar</label>
                <input type="text" class="form-control" id="image_url" name="image_url" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Menu</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>