<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: manajemen_praktikum.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode_praktikum = trim($_POST['kode_praktikum']);
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);

    if (empty($kode_praktikum) || empty($nama)) {
        $error = "Kode dan Nama Praktikum tidak boleh kosong!";
    } else {
        $stmt = $conn->prepare("UPDATE mata_praktikum SET kode_praktikum = ?, nama = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("sssi", $kode_praktikum, $nama, $deskripsi, $id);
        
        if ($stmt->execute()) {
            header("Location: manajemen_praktikum.php?status=edit_sukses");
            exit();
        } else {
            $error = "Gagal memperbarui data: " . $stmt->error;
        }
        $stmt->close();
    }
}

$stmt_select = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?");
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$praktikum = $result->fetch_assoc();
$stmt_select->close();

if (!$praktikum) {
    echo "Data tidak ditemukan.";
    exit;
}

$pageTitle = 'Edit Praktikum';
$activePage = 'manajemen_praktikum';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <a href="manajemen_praktikum.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Daftar Praktikum
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Edit Mata Praktikum</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form action="praktikum_edit.php?id=<?php echo $id; ?>" method="POST">
        <div class="mb-4">
            <label for="kode_praktikum" class="block text-gray-700 text-sm font-bold mb-2">Kode Praktikum</label>
            <input type="text" id="kode_praktikum" name="kode_praktikum" value="<?php echo htmlspecialchars($praktikum['kode_praktikum']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Praktikum</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($praktikum['nama']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></textarea>
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>