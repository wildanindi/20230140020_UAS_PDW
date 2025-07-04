<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$id_modul = $_GET['id'] ?? null;

if (!$id_modul) {
    header("Location: manajemen_praktikum.php");
    exit();
}

$stmt_get = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt_get->bind_param("i", $id_modul);
$stmt_get->execute();
$result_get = $stmt_get->get_result();
$modul = $result_get->fetch_assoc();
$stmt_get->close();

if (!$modul) {
    echo "Modul tidak ditemukan.";
    exit;
}

$id_praktikum = $modul['id_praktikum'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_modul = trim($_POST['nama_modul']);
    $deskripsi = trim($_POST['deskripsi']);
    $file_materi_path = $modul['file_materi'];

    if (empty($nama_modul)) {
        $error = "Nama modul wajib diisi!";
    } else {
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == UPLOAD_ERR_OK) {
            if (!empty($modul['file_materi']) && file_exists('../uploads/' . $modul['file_materi'])) {
                unlink('../uploads/' . $modul['file_materi']);
            }

            $file_info = $_FILES['file_materi'];
            $file_name = $file_info['name'];
            $file_tmp_name = $file_info['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $upload_dir = '../uploads/';
            $new_file_name = uniqid('modul_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;
            
            $allowed_ext = ['pdf', 'doc', 'docx'];
            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Format file tidak diizinkan.";
            } else if (move_uploaded_file($file_tmp_name, $destination)) {
                $file_materi_path = $new_file_name;
            } else {
                $error = "Terjadi kesalahan saat mengunggah file baru.";
            }
        }

        if (empty($error)) {
            $stmt_update = $conn->prepare("UPDATE modul SET nama_modul = ?, deskripsi = ?, file_materi = ? WHERE id = ?");
            $stmt_update->bind_param("sssi", $nama_modul, $deskripsi, $file_materi_path, $id_modul);
            
            if ($stmt_update->execute()) {
                header("Location: modul.php?id_praktikum=" . $id_praktikum . "&status=edit_sukses");
                exit();
            } else {
                $error = "Gagal memperbarui data: " . $stmt_update->error;
            }
            $stmt_update->close();
        }
    }
}

$pageTitle = 'Edit Modul';
$activePage = 'manajemen_praktikum';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <a href="modul.php?id_praktikum=<?php echo $id_praktikum; ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Daftar Modul
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Edit Modul</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form action="modul_edit.php?id=<?php echo $id_modul; ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="nama_modul" class="block text-gray-700 text-sm font-bold mb-2">Nama Modul</label>
            <input type="text" id="nama_modul" name="nama_modul" value="<?php echo htmlspecialchars($modul['nama_modul']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>
        <div class="mb-4">
            <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Singkat</label>
            <textarea id="deskripsi" name="deskripsi" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"><?php echo htmlspecialchars($modul['deskripsi']); ?></textarea>
        </div>
        <div class="mb-6">
            <label for="file_materi" class="block text-gray-700 text-sm font-bold mb-2">Ganti File Materi (Opsional)</label>
            <?php if (!empty($modul['file_materi'])): ?>
                <p class="text-sm text-gray-600 mb-2">File saat ini: <a href="../uploads/<?php echo htmlspecialchars($modul['file_materi']); ?>" target="_blank" class="text-blue-500"><?php echo htmlspecialchars($modul['file_materi']); ?></a></p>
            <?php endif; ?>
            <input type="file" id="file_materi" name="file_materi" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php 
$conn->close();
require_once 'templates/footer.php'; 
?>