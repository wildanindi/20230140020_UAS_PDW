<?php
$pageTitle = 'Manajemen Modul';
$activePage = 'manajemen_praktikum';
require_once 'templates/header.php';
require_once '../config.php';

// Ambil ID praktikum dari URL
$id_praktikum = $_GET['id_praktikum'] ?? null;
if (!$id_praktikum) {
    echo "ID Praktikum tidak valid.";
    exit;
}

// Ambil nama praktikum untuk judul halaman
$stmt_praktikum = $conn->prepare("SELECT nama FROM mata_praktikum WHERE id = ?");
$stmt_praktikum->bind_param("i", $id_praktikum);
$stmt_praktikum->execute();
$result_praktikum = $stmt_praktikum->get_result();
$praktikum = $result_praktikum->fetch_assoc();
$nama_praktikum = $praktikum['nama'];

// Ambil semua modul yang terkait dengan praktikum ini
$stmt_modul = $conn->prepare("SELECT * FROM modul WHERE id_praktikum = ? ORDER BY created_at ASC");
$stmt_modul->bind_param("i", $id_praktikum);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();

?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <a href="manajemen_praktikum.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
        &larr; Kembali ke Daftar Praktikum
    </a>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daftar Modul</h2>
            <p class="text-gray-600">Untuk Praktikum: <span class="font-semibold"><?php echo htmlspecialchars($nama_praktikum); ?></span></p>
        </div>
        <a href="modul_tambah.php?id_praktikum=<?php echo $id_praktikum; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
            + Tambah Modul
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="w-1/3 text-left py-3 px-4 uppercase font-semibold text-sm">Nama Modul</th>
                    <th class="w-1/3 text-left py-3 px-4 uppercase font-semibold text-sm">File Materi</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($result_modul->num_rows > 0): ?>
                    <?php while($modul = $result_modul->fetch_assoc()): ?>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($modul['nama_modul']); ?></td>
                            <td class="py-3 px-4">
                                <?php if (!empty($modul['file_materi'])): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($modul['file_materi']); ?>" target="_blank" class="text-blue-500 hover:underline">
                                        <?php echo htmlspecialchars($modul['file_materi']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">Belum diunggah</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <a href="modul_edit.php?id=<?php echo $modul['id']; ?>" class="text-sm bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-lg mr-2">Edit</a>
                                <a href="modul_hapus.php?id=<?php echo $modul['id']; ?>" class="text-sm bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg" onclick="return confirm('Yakin ingin menghapus modul ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">Belum ada modul untuk praktikum ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt_praktikum->close();
$stmt_modul->close();
$conn->close();
require_once 'templates/footer.php';
?>