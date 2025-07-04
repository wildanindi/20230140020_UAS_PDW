<?php
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

// --- LANGKAH 1: DAPATKAN DAFTAR PRAKTIKUM YANG SUDAH DIIKUTI PENGGUNA ---
$praktikumTerdaftarIds = [];
if (isset($_SESSION['user_id'])) {
    $mahasiswa_id = $_SESSION['user_id'];
    
    // Query untuk mengambil semua ID praktikum yang sudah didaftarkan oleh mahasiswa ini
    $stmtTerdaftar = $conn->prepare("SELECT id_praktikum FROM pendaftaran WHERE id_mahasiswa = ?");
    $stmtTerdaftar->bind_param("i", $mahasiswa_id);
    $stmtTerdaftar->execute();
    $resultTerdaftar = $stmtTerdaftar->get_result();
    
    // Simpan semua ID ke dalam sebuah array sederhana
    while ($row = $resultTerdaftar->fetch_assoc()) {
        $praktikumTerdaftarIds[] = $row['id_praktikum'];
    }
}

// --- LANGKAH 2: DAPATKAN SEMUA PRAKTIKUM YANG TERSEDIA ---
$query = "SELECT 
            mp.id, 
            mp.nama, 
            mp.deskripsi, 
            u.nama AS nama_asisten 
          FROM mata_praktikum mp
          LEFT JOIN users u ON mp.id_asisten = u.id
          ORDER BY mp.nama ASC";

$result = $conn->query($query);

$daftarPraktikum = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $daftarPraktikum[] = $row;
    }
}
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Katalog Mata Praktikum</h1>
    <p class="text-gray-600 mb-8">Berikut adalah daftar semua mata praktikum yang tersedia. Klik tombol "Daftar" untuk mendaftar.</p>

    <?php if (!empty($daftarPraktikum)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($daftarPraktikum as $praktikum) : ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-transform duration-300 flex flex-col">
                    <div class="p-6 flex-grow">
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($praktikum['nama']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Asisten: <?php echo htmlspecialchars($praktikum['nama_asisten'] ?? 'Belum Ditentukan'); ?>
                        </p>
                        <p class="text-gray-700 mb-6 h-20 overflow-hidden"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
                    </div>
                    <div class="p-6 pt-0">
                        <?php
                        // --- LANGKAH 3: TAMPILKAN TOMBOL BERDASARKAN KONDISI ---
                        // Cek apakah ID praktikum saat ini ada di dalam array praktikum yang sudah terdaftar
                        if (in_array($praktikum['id'], $praktikumTerdaftarIds)) {
                            // Jika ya, tampilkan tombol non-aktif "Sudah Terdaftar"
                            ?>
                            <button disabled class="w-full text-center bg-gray-400 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed">
                                Sudah Terdaftar
                            </button>
                            <?php
                        } else {
                            // Jika tidak, tampilkan link untuk mendaftar
                            ?>
                            <a href="daftar_action.php?id_praktikum=<?php echo $praktikum['id']; ?>" class="w-full text-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300 block">
                                Daftar Praktikum
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16">
            <p class="text-gray-500 text-lg">Saat ini belum ada mata praktikum yang tersedia.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>
