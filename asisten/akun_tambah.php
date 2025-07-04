<?php
// Mulai sesi dan panggil config di atas
session_start();
require_once '../config.php';

// Validasi sesi asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$error = '';

// Proses form SEBELUM mencetak HTML
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $error = "Semua field wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "Email sudah terdaftar.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt_insert = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);
            if ($stmt_insert->execute()) {
                header("Location: manajemen_akun.php?status=tambah_sukses");
                exit();
            } else {
                $error = "Gagal membuat akun.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

// Setelah semua logika selesai, baru panggil header
$pageTitle = 'Tambah Akun';
$activePage = 'manajemen_akun';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <a href="manajemen_akun.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Daftar Akun
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Tambah Akun Baru</h2>

    <?php if ($error): ?> <div class="bg-red-100 text-red-700 p-3 mb-4 rounded-lg"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>

    <form action="akun_tambah.php" method="POST">
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="shadow border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" name="email" id="email" class="shadow border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
            <input type="password" name="password" id="password" class="shadow border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-6">
            <label for="role" class="block text-gray-700 font-bold mb-2">Role</label>
            <select name="role" id="role" class="shadow border rounded w-full py-2 px-3" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Simpan Akun
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>