<?php
// edit_menu.php

require_once('../includes/config.php');

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

// Ambil data menu berdasarkan ID
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/menus/' . $id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);
if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
}

curl_close($curl);

$menu = json_decode($response, true)['data'];

if (!$menu) {
    header('Location: manage_menus.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];
    $category_id = $_POST['category_id'];

    $data = array(
        'name' => $name,
        'category_id' => $category_id,
        'description' => $description,
        'price' => $price,
        'image_url' => $image_url
    );

    // Inisialisasi cURL untuk update menu
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => API_URL . '/menus/' . $id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PUT',
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
        $responseData = json_decode($response, true);
        if ($responseData['message'] === 'Menu berhasil diubah') {
            header('Location: manage_menus.php');
            exit();
        } else {
            $error_message = 'Gagal memperbarui menu.' . $responseData['message'];
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
    <title>Edit Menu - Admin Dashboard</title>
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
        <h2>Edit Menu</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="edit_menu.php?id=<?php echo $menu['id']; ?>" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nama Menu</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $menu['name']; ?>"
                    required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"
                    required><?php echo $menu['description']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Harga</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo $menu['price']; ?>"
                    required>
            </div>
            <!-- Dropdown Category -->
            <div class="mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $menu['category_id']) ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">Tidak ada kategori tersedia</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="image_url" class="form-label">URL Gambar</label>
                <input type="text" class="form-control" id="image_url" name="image_url"
                    value="<?php echo $menu['image_url']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Menu</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>