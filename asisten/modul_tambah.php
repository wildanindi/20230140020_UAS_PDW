<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id_praktikum = $_GET['id_praktikum'] ?? null;
if (!$id_praktikum) {
    echo "ID Praktikum tidak valid.";
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_modul = trim($_POST['nama_modul']);
    $deskripsi = trim($_POST['deskripsi']);
    $file_materi_path = null;

    if (empty($nama_modul)) {
        $error = "Nama modul wajib diisi!";
    } else {
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == UPLOAD_ERR_OK) {
            $file_info = $_FILES['file_materi'];
            $file_name = $file_info['name'];
            $file_tmp_name = $file_info['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $upload_dir = '../uploads/';
            $new_file_name = uniqid('modul_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            $allowed_ext = ['pdf', 'doc', 'docx'];
            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Format file tidak diizinkan. Harap unggah file PDF, DOC, atau DOCX.";
            } else if (move_uploaded_file($file_tmp_name, $destination)) {
                $file_materi_path = $new_file_name;
            } else {
                $error = "Terjadi kesalahan saat mengunggah file. Cek izin folder 'uploads'.";
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO modul (id_praktikum, nama_modul, deskripsi, file_materi) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_praktikum, $nama_modul, $deskripsi, $file_materi_path);

            if ($stmt->execute()) {
                header("Location: modul.php?id_praktikum=" . $id_praktikum . "&status=tambah_sukses");
                exit();
            } else {
                $error = "Gagal menyimpan data ke database: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$pageTitle = 'Tambah Modul';
$activePage = 'manajemen_praktikum';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <a href="modul.php?id_praktikum=<?php echo $id_praktikum; ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Daftar Modul
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Tambah Modul Baru</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form action="modul_tambah.php?id_praktikum=<?php echo $id_praktikum; ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="nama_modul" class="block text-gray-700 text-sm font-bold mb-2">Nama Modul</label>
            <input type="text" id="nama_modul" name="nama_modul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>
        <div class="mb-4">
            <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Singkat</label>
            <textarea id="deskripsi" name="deskripsi" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
        </div>
        <div class="mb-6">
            <label for="file_materi" class="block text-gray-700 text-sm font-bold mb-2">File Materi (PDF/DOCX, maks 5MB)</label>
            <input type="file" id="file_materi" name="file_materi" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Simpan Modul
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>