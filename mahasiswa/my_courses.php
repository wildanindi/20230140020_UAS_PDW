<?php
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

$id_mahasiswa = $_SESSION['user_id'];

// Query untuk mengambil praktikum yang sudah didaftarkan oleh mahasiswa yang login
$stmt = $conn->prepare("SELECT mp.* FROM mata_praktikum mp JOIN pendaftaran p ON mp.id = p.id_praktikum WHERE p.id_mahasiswa = ? ORDER BY mp.nama ASC");
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Praktikum yang Anda Ikuti</h1>
    
    <?php if (isset($_GET['status']) && $_GET['status'] == 'daftar_sukses'): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
            Anda berhasil terdaftar di praktikum!
        </div>
    <?php endif; ?>

    <div class="space-y-6">
        <?php if ($result->num_rows > 0): ?>
            <?php while($praktikum = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col md:flex-row items-center justify-between">
                    <div class="flex-grow mb-4 md:mb-0">
                        <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($praktikum['nama']); ?></h3>
                        <p class="text-sm text-gray-500">Kode: <?php echo htmlspecialchars($praktikum['kode_praktikum']); ?></p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="course_detail.php?id_praktikum=<?php echo $praktikum['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                            Lihat Detail & Tugas
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-white p-8 rounded-xl shadow-md text-center">
                <p class="text-gray-500">Anda belum mendaftar di mata praktikum manapun.</p>
                <a href="courses.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                    Cari Praktikum Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>