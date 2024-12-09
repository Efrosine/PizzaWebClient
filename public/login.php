<?php
// login.php - Halaman untuk login

require_once('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Inisialisasi cURL
    $curl = curl_init();

    // Set opsi cURL untuk melakukan POST request ke API Laravel
    curl_setopt_array($curl, array(
        CURLOPT_URL => API_URL . '/login', // Gantilah dengan URL API Laravel yang sesuai
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'email' => $email,
            'password' => $password
        ]),
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
    ));

    // Eksekusi request cURL
    $response = curl_exec($curl);

    // Cek apakah ada error saat request
    if (curl_errno($curl)) {
        echo 'Curl error: ' . curl_error($curl);
    }

    // Tutup koneksi cURL
    curl_close($curl);

    // Cek apakah ada respons dari API
    if ($response) {
        // Parse response JSON dari API
        $json = json_decode($response, true);
        $data = $json['data'];

        if (isset($data['id']) && isset($data['role'])) {
            // Simpan id dan role di session
            $_SESSION['id'] = $data['id'];
            $_SESSION['role'] = $data['role'];

            // Redirect ke halaman utama setelah login berhasil
            // Redirect berdasarkan role
            if ($data['role'] == 'admin') {
                header('Location: admin_dashboard.php'); // Halaman admin
            } else {
                header('Location: index.php'); // Halaman customer
            }
            exit();
        } else {
            // Jika tidak ada id atau role pada response, anggap login gagal
            $error_message = "Login gagal, periksa kembali email atau password!" . $json['message'] . $json;
        }
    } else {
        // Jika tidak ada response dari API
        $error_message = "Tidak ada respons dari server API.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OrderApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <h2>Login</h2>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div class="mt-3">
            <span>Belum punya akun? <a href="register.php">Daftar sekarang</a></span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>