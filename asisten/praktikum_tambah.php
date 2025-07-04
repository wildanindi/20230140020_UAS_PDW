<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode_praktikum = trim($_POST['kode_praktikum']);
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    if (empty($kode_praktikum) || empty($nama)) {
        $error = "Kode Praktikum dan Nama Praktikum wajib diisi!";
    } else {
        $stmt = $conn->prepare("INSERT INTO mata_praktikum (kode_praktikum, nama, deskripsi) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $kode_praktikum, $nama, $deskripsi);

        if ($stmt->execute()) {
            header("Location: manajemen_praktikum.php?status=tambah_sukses");
            exit();
        } else {
            if ($conn->errno == 1062) {
                $error = "Gagal menyimpan: Kode Praktikum '{$kode_praktikum}' sudah ada.";
            } else {
                $error = "Terjadi kesalahan saat menyimpan data: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$pageTitle = 'Tambah Praktikum';
$activePage = 'manajemen_praktikum';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <a href="manajemen_praktikum.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Daftar Praktikum
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Tambah Mata Praktikum</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form action="praktikum_tambah.php" method="POST">
        <div class="mb-4">
            <label for="kode_praktikum" class="block text-gray-700 text-sm font-bold mb-2">Kode Praktikum</label>
            <input type="text" id="kode_praktikum" name="kode_praktikum" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contoh: PW-01" required>
        </div>
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Praktikum</label>
            <input type="text" id="nama" name="nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contoh: Pemrograman Web" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Jelaskan singkat mengenai mata praktikum ini"></textarea>
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Simpan Praktikum
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>