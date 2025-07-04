<?php
$pageTitle = 'Manajemen Praktikum';
$activePage = 'manajemen_praktikum'; // Kita akan tambahkan ini di navigasi nanti
require_once 'templates/header.php';
require_once '../config.php';

// Logika untuk mengambil semua data praktikum dari database
$result = $conn->query("SELECT * FROM mata_praktikum ORDER BY nama ASC");

?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Mata Praktikum</h2>
        <a href="praktikum_tambah.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
            + Tambah Praktikum
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Kode</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nama Praktikum</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($row['kode_praktikum']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td class="py-3 px-4 flex items-center">
                                <a href="modul.php?id_praktikum=<?php echo $row['id']; ?>" class="text-sm bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-lg mr-2">Modul</a> 
                                <a href="praktikum_edit.php?id=<?php echo $row['id']; ?>" class="text-sm bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-lg mr-2">Edit</a>
                                <a href="praktikum_hapus.php?id=<?php echo $row['id']; ?>" class="text-sm bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg" onclick="return confirm('Apakah Anda yakin ingin menghapus praktikum ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">
                            Belum ada data mata praktikum. Silakan tambahkan satu.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>