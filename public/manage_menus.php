<?php
// manage_menus.php

require_once('../includes/config.php');

// Cek jika session belum ada atau bukan admin, redirect ke halaman login
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Inisialisasi cURL untuk mengambil data menu dari API Laravel
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/menus',
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

if ($response) {
    $menuData = json_decode($response, true);
    $menus = $menuData['data'];
} else {
    $menus = [];
    $error_message = "Gagal mengambil data menu!";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menus - Admin Dashboard</title>
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
        <h2>Manage Menus</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <a href="add_menu.php" class="btn btn-primary mb-3">Add New Menu</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($menus)): ?>
                    <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td><?php echo $menu['id']; ?></td>
                            <td><?php echo $menu['name']; ?></td>
                            <td><?php echo $menu['description']; ?></td>
                            <td>Rp <?php echo number_format($menu['price'], 0, ',', '.'); ?></td>
                            <td><img src="<?php echo $menu['image_url']; ?>" alt="<?php echo $menu['name']; ?>" width="50"></td>
                            <td>
                                <a href="edit_menu.php?id=<?php echo $menu['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_menu.php?id=<?php echo $menu['id']; ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this menu?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No menus available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>