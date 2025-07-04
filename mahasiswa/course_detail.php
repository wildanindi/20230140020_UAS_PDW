<?php
$pageTitle = 'Detail Praktikum';
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

$id_praktikum = $_GET['id_praktikum'] ?? null;
$id_mahasiswa = $_SESSION['user_id'];
if (!$id_praktikum) {
    echo "ID Praktikum tidak valid.";
    exit;
}

// Logika untuk upload laporan
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_modul'])) {
    $id_modul = $_POST['id_modul'];
    
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == UPLOAD_ERR_OK) {
        $file_info = $_FILES['file_laporan'];
        $file_name = basename($file_info['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $upload_dir = '../laporan/';
        $new_file_name = 'laporan_' . $id_mahasiswa . '_' . $id_modul . '.' . $file_ext;
        $destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_info['tmp_name'], $destination)) {
            $stmt_insert = $conn->prepare("INSERT INTO laporan (id_modul, id_mahasiswa, file_laporan) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("iis", $id_modul, $id_mahasiswa, $new_file_name);
            if ($stmt_insert->execute()) {
                $message = "Laporan berhasil diunggah!";
            } else {
                $error = "Gagal menyimpan data laporan.";
                unlink($destination); // Hapus file jika gagal simpan ke DB
            }
            $stmt_insert->close();
        } else {
            $error = "Gagal memindahkan file.";
        }
    } else {
        $error = "Tidak ada file yang diunggah atau terjadi error.";
    }
}


// Ambil info praktikum
$stmt_praktikum = $conn->prepare("SELECT nama FROM mata_praktikum WHERE id = ?");
$stmt_praktikum->bind_param("i", $id_praktikum);
$stmt_praktikum->execute();
$result_praktikum = $stmt_praktikum->get_result();
$praktikum = $result_praktikum->fetch_assoc();
$namaPraktikum = $praktikum['nama'] ?? 'Tidak Ditemukan';

// Ambil modul terkait
$stmt_modul = $conn->prepare("SELECT m.*, l.id as id_laporan, l.file_laporan, l.nilai, l.status FROM modul m LEFT JOIN laporan l ON m.id = l.id_modul AND l.id_mahasiswa = ? WHERE m.id_praktikum = ? ORDER BY m.created_at ASC");
$stmt_modul->bind_param("ii", $id_mahasiswa, $id_praktikum);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
?>

<div class="container mx-auto">
    <a href="my_courses.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Praktikum Saya
    </a>

    <h1 class="text-3xl font-bold text-gray-800 mb-2">Detail Praktikum: <?php echo htmlspecialchars($namaPraktikum); ?></h1>

    <?php if ($message): ?> <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4"><?php echo $message; ?></div> <?php endif; ?>
    <?php if ($error): ?> <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4"><?php echo $error; ?></div> <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Modul</h2>
        <div class="space-y-6">
            <?php if ($result_modul->num_rows > 0): ?>
                <?php while($modul = $result_modul->fetch_assoc()): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($modul['nama_modul']); ?></h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center mt-4">
                            <div>
                                <h4 class="font-semibold mb-2">Materi Praktikum</h4>
                                <?php if (!empty($modul['file_materi'])): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($modul['file_materi']); ?>" download class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Unduh Materi</span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">Tidak tersedia</span>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold mb-2">Pengumpulan Tugas</h4>
                                <?php if ($modul['id_laporan']): ?>
                                    <div class="flex items-center text-green-600">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Sudah Mengumpulkan</span>
                                    </div>
                                <?php else: ?>
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_modul" value="<?php echo $modul['id']; ?>">
                                        <input type="file" name="file_laporan" required class="text-sm text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <button type="submit" class="mt-2 text-sm bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-full">Upload</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Nilai</h4>
                                <?php if ($modul['status'] == 'Dinilai'): ?>
                                    <span class="text-2xl font-bold text-blue-600"><?php echo htmlspecialchars($modul['nilai']); ?></span>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">Belum Dinilai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-gray-500">Belum ada modul yang ditambahkan untuk praktikum ini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>