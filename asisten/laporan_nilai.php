<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id_laporan = $_GET['id'] ?? null;
if (!$id_laporan) {
    echo "ID Laporan tidak valid.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];
    $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, feedback = ?, status = 'Dinilai' WHERE id = ?");
    $stmt->bind_param("isi", $nilai, $feedback, $id_laporan);
    $stmt->execute();
    $stmt->close();
    header("Location: laporan.php?status=nilai_sukses");
    exit();
}

$stmt_get = $conn->prepare("SELECT l.*, u.nama as nama_mahasiswa, m.nama_modul 
                        FROM laporan l 
                        JOIN users u ON l.id_mahasiswa = u.id 
                        JOIN modul m ON l.id_modul = m.id 
                        WHERE l.id = ?");
$stmt_get->bind_param("i", $id_laporan);
$stmt_get->execute();
$laporan = $stmt_get->get_result()->fetch_assoc();
$stmt_get->close();

$pageTitle = 'Beri Nilai Laporan';
$activePage = 'laporan';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
    <a href="laporan.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Daftar Laporan
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Penilaian Laporan</h2>

    <div class="border-b pb-4 mb-4">
        <p><strong>Mahasiswa:</strong> <?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
        <p><strong>Modul:</strong> <?php echo htmlspecialchars($laporan['nama_modul']); ?></p>
        <p><strong>File Laporan:</strong> 
            <a href="../laporan/<?php echo htmlspecialchars($laporan['file_laporan']); ?>" download class="text-blue-500 hover:underline">
                Unduh Laporan
            </a>
        </p>
    </div>

    <form action="laporan_nilai.php?id=<?php echo $id_laporan; ?>" method="POST">
        <div class="mb-4">
            <label for="nilai" class="block text-gray-700 text-sm font-bold mb-2">Nilai (0-100)</label>
            <input type="number" name="nilai" id="nilai" min="0" max="100" value="<?php echo htmlspecialchars($laporan['nilai'] ?? ''); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>
        <div class="mb-6">
            <label for="feedback" class="block text-gray-700 text-sm font-bold mb-2">Feedback (Opsional)</label>
            <textarea name="feedback" id="feedback" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"><?php echo htmlspecialchars($laporan['feedback'] ?? ''); ?></textarea>
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                Simpan Nilai
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>