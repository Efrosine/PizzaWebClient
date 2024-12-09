<?php
// manageOrder.php

require_once('../includes/config.php');

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Ambil data order melalui cURL
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/orders', // Gantilah dengan URL API yang sesuai
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);
curl_close($curl);

// Cek apakah response berhasil diterima
if ($response) {
    $responseData = json_decode($response, true);
    if ($responseData['message'] === 'Berhasil menampilkan semua order') {
        $orders = $responseData['data'];
    } else {
        $error_message = 'Gagal mengambil data order.';
    }
} else {
    $error_message = "Tidak ada respon dari server.";
}

// Jika form submit untuk update status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Kirimkan data ke API untuk update status order
    $data = array('status' => $status);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => API_URL . '/orders/' . $order_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
        CURLOPT_POSTFIELDS => json_encode($data),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    if ($response) {
        $responseData = json_decode($response, true);
        if ($responseData['message'] === 'Order berhasil diubah') {
            header('Location: manage_orders.php'); // Redirect ke halaman yang sama setelah update
            exit();
        } else {
            $error_message = 'Gagal memperbarui status order.' . $responseData['message'];
        }
    } else {
        $error_message = 'Tidak ada respon dari server saat update status.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
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
        <h2>Manage Orders</h2>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    <th>Order Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($orders) && !empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['user']['name']; ?></td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select class="form-control" name="status">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="prosessed" <?php echo $order['status'] == 'prosessed' ? 'selected' : ''; ?>>
                                            Prosessed</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>
                                            Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>
                                            Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-2">Update Status</button>
                                </form>
                            </td>
                            <td><?php echo number_format($order['total_price'], 0, ',', '.'); ?> IDR</td>
                            <td>
                                <ul>
                                    <?php foreach ($order['details'] as $detail): ?>
                                        <li>Menu ID: <?php echo $detail['menu_id']; ?>, Quantity: <?php echo $detail['quantity']; ?>, Price: <?php echo number_format($detail['price'], 0, ',', '.'); ?> IDR</li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <!-- Additional actions can go here if needed -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data order.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
