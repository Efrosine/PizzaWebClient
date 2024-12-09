<?php
// index.php - Halaman utama untuk menampilkan menu

require_once('../includes/config.php');

// Cek jika session belum ada, redirect ke halaman login
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Inisialisasi cURL untuk mengambil data menu dari API Laravel
$curl = curl_init();

// Set opsi cURL untuk melakukan GET request ke API Laravel
curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/menus', // Gantilah dengan URL API Laravel yang sesuai
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
    $menuData = json_decode($response, true); // Decode response JSON
    $menu = $menuData['data']; // Ambil data menu dari response
} else {
    $menu = [];
    $error_message = "Gagal mengambil data menu!";
}

// Menambahkan cURL untuk mengambil data pengguna
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => API_URL . '/users/' . $_SESSION['id'], // API untuk mengambil data pengguna berdasarkan session ID
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

$responseUser = curl_exec($curl);

// Cek jika ada error pada cURL untuk user
if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
}

// Tutup koneksi cURL untuk user
curl_close($curl);

// Cek apakah response dari API pengguna berhasil diterima
if ($responseUser) {
    $userData = json_decode($responseUser, true); // Decode response JSON
    $user = $userData['data']; // Ambil data user dari response
} else {
    $user = [];
    $userName = 'Guest'; // Default jika gagal mendapatkan data pengguna
    $userEmail = 'N/A';
    $userRole = 'N/A';
}

// Extract user info
$userName = isset($user['name']) ? $user['name'] : 'Guest';
$userEmail = isset($user['email']) ? $user['email'] : 'N/A';
$userRole = isset($user['role']) ? $user['role'] : 'N/A';
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Utama - OrderApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">OrderApp</a>
            <span class="navbar-text ms-1">Nama: <?php echo $userName; ?></span>
            <span class="navbar-text ms-1">Email: <?php echo $userEmail; ?></span>
            <span class="navbar-text ms-1">Role: <?php echo $userRole; ?></span>
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
        <h2>Menu</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Menampilkan Menu dalam Bentuk Card -->
        <div class="row">
            <?php if (!empty($menu)): ?>
                <?php foreach ($menu as $item): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo $item['image_url']; ?>" class="card-img-top"
                                alt="<?php echo $item['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $item['name']; ?></h5>
                                <p class="card-text"><?php echo $item['description']; ?></p>
                                <p class="card-text"><strong>Rp
                                        <?php echo number_format($item['price'], 0, ',', '.'); ?></strong></p>
                                <button class="btn btn-primary w-100"
                                    onclick="addToOrder(<?php echo $item['id']; ?>, '<?php echo $item['name']; ?>', <?php echo $item['price']; ?>)">
                                    Tambahkan ke Order
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Tidak ada menu yang tersedia.</p>
            <?php endif; ?>
        </div>

        <!-- Daftar Order -->
        <h3 class="mt-4">Order List</h3>
        <ul id="orderList" class="list-group">
            <!-- Order items will appear here -->
        </ul>

        <!-- Total dan Tombol -->
        <div class="d-flex justify-content-between m-3">
            <h4>Total Harga: <span id="totalPrice">Rp 0</span></h4>
            <button class="btn btn-success" onclick="createOrder()">Buat Pesanan</button>
        </div>
    </div>


    <script>
        // Array untuk menyimpan pesanan
        let orderList = [];

        // Fungsi untuk menambahkan item ke order list
        function addToOrder(itemId, itemName, itemPrice) {
            // Cek apakah item sudah ada dalam daftar order
            let found = false;
            for (let i = 0; i < orderList.length; i++) {
                if (orderList[i].id === itemId) {
                    orderList[i].quantity += 1; // Jika ada, tambahkan jumlahnya
                    found = true;
                    break;
                }
            }

            // Jika item belum ada, tambahkan ke daftar
            if (!found) {
                orderList.push({
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    quantity: 1
                });
            }

            // Update tampilan order list
            updateOrderList();
        }

        // Fungsi untuk memperbarui tampilan daftar order
        function updateOrderList() {
            const orderListElement = document.getElementById('orderList');
            orderListElement.innerHTML = ''; // Bersihkan daftar order

            let totalPrice = 0;

            orderList.forEach(item => {
                const listItem = document.createElement('li');
                listItem.classList.add('list-group-item');
                listItem.innerHTML = ` 
                ${item.name} - ${item.quantity} x Rp ${item.price} = Rp ${item.quantity * item.price}
                <button class="btn btn-danger btn-sm float-end" onclick="removeFromOrder(${item.id})">Hapus</button>
            `;
                orderListElement.appendChild(listItem);

                // Hitung total harga
                totalPrice += item.quantity * item.price;
            });

            // Update total harga
            document.getElementById('totalPrice').textContent = `Rp ${totalPrice.toLocaleString()}`;
        }

        // Fungsi untuk menghapus item dari order
        function removeFromOrder(itemId) {
            // Hapus item dari order list
            orderList = orderList.filter(item => item.id !== itemId);

            // Update tampilan order list
            updateOrderList();
        }

        // Fungsi untuk membuat pesanan (send to API)
        function createOrder() {
            // Ambil data user_id dari session atau variable lain yang relevan
            const userId = <?php echo $_SESSION["id"]; ?>; // Misalnya menggunakan PHP session untuk mendapatkan user_id

            // Mengonversi daftar order menjadi format yang sesuai dengan API
            const orderData = orderList.map(item => ({
                menu_id: item.id,        // Gunakan menu_id yang ada dalam order
                quantity: item.quantity  // Gunakan quantity yang ada dalam order
            }));

            // Membuat request POST untuk membuat pesanan
            fetch('http://172.21.155.41:9004/api/createorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // 'Authorization': 'Bearer <?php echo $_SESSION["id"]; ?>' // Jika perlu menggunakan token
                },
                body: JSON.stringify({
                    user_id: userId,   // Kirimkan user_id untuk mengenali pengguna
                    items: orderData   // Kirimkan items yang berisi menu_id dan quantity
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        // Tampilkan pesan sukses jika pesanan berhasil
                        alert(data.message);

                        // Kosongkan daftar order setelah pesanan dibuat
                        orderList = [];
                        updateOrderList();
                    } else {
                        // Tampilkan error jika tidak ada pesan sukses
                        alert('Gagal membuat pesanan. Coba lagi!');
                    }
                })
                .catch(error => {
                    // Tampilkan pesan error jika terjadi kesalahan dalam fetch
                    console.error('Error:', error);
                    alert('Gagal membuat pesanan. Coba lagi!');
                });
        }

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>