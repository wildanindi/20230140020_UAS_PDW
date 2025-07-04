<?php
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php';
require_once '../config.php';

// Bangun query dasar
$sql = "SELECT l.id, l.tgl_kumpul, l.status, u.nama as nama_mahasiswa, m.nama_modul 
        FROM laporan l 
        JOIN users u ON l.id_mahasiswa = u.id 
        JOIN modul m ON l.id_modul = m.id";

$whereClauses = [];
$params = [];
$types = '';

// Logika filter
if (!empty($_GET['id_mahasiswa'])) {
    $whereClauses[] = "l.id_mahasiswa = ?";
    $params[] = $_GET['id_mahasiswa'];
    $types .= 'i';
}
if (!empty($_GET['id_modul'])) {
    $whereClauses[] = "l.id_modul = ?";
    $params[] = $_GET['id_modul'];
    $types .= 'i';
}
if (!empty($_GET['status'])) {
    $whereClauses[] = "l.status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}
$sql .= " ORDER BY l.tgl_kumpul DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_laporan = $stmt->get_result();

// Ambil data untuk filter dropdown
$mahasiswas = $conn->query("SELECT id, nama FROM users WHERE role = 'mahasiswa' ORDER BY nama");
$moduls = $conn->query("SELECT id, nama_modul FROM modul ORDER BY nama_modul");

?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Filter Laporan Masuk</h2>

    <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div>
            <label for="id_mahasiswa" class="block text-sm font-medium text-gray-700">Mahasiswa</label>
            <select name="id_mahasiswa" id="id_mahasiswa" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Semua Mahasiswa</option>
                <?php while($row = $mahasiswas->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo (($_GET['id_mahasiswa'] ?? '') == $row['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['nama']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="id_modul" class="block text-sm font-medium text-gray-700">Modul</label>
            <select name="id_modul" id="id_modul" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Semua Modul</option>
                 <?php while($row = $moduls->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo (($_GET['id_modul'] ?? '') == $row['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['nama_modul']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Semua Status</option>
                <option value="Terkumpul" <?php echo (($_GET['status'] ?? '') == 'Terkumpul') ? 'selected' : ''; ?>>Belum Dinilai</option>
                <option value="Dinilai" <?php echo (($_GET['status'] ?? '') == 'Dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
            </select>
        </div>
        <div class="self-end">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Mahasiswa</th>
                    <th class="py-3 px-4 text-left">Modul</th>
                    <th class="py-3 px-4 text-left">Tgl Kumpul</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    <th class="py-3 px-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($result_laporan->num_rows > 0): ?>
                    <?php while($laporan = $result_laporan->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($laporan['nama_modul']); ?></td>
                            <td class="py-3 px-4"><?php echo date('d M Y, H:i', strtotime($laporan['tgl_kumpul'])); ?></td>
                            <td class="py-3 px-4">
                                <?php if ($laporan['status'] == 'Dinilai'): ?>
                                    <span class="bg-green-200 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">Sudah Dinilai</span>
                                <?php else: ?>
                                    <span class="bg-yellow-200 text-yellow-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">Belum Dinilai</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <a href="laporan_nilai.php?id=<?php echo $laporan['id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-bold py-1 px-3 rounded-lg">
                                    <?php echo ($laporan['status'] == 'Dinilai') ? 'Lihat/Edit Nilai' : 'Beri Nilai'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4">Tidak ada laporan yang cocok dengan filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>