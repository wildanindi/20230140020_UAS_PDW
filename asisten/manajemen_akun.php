<?php
$pageTitle = 'Manajemen Akun';
$activePage = 'manajemen_akun'; // Akan kita tambahkan ke navigasi
require_once 'templates/header.php';
require_once '../config.php';

// Ambil semua pengguna dari database
$result = $conn->query("SELECT id, nama, email, role FROM users ORDER BY nama ASC");
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Akun Pengguna</h2>
        <a href="akun_tambah.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
            + Tambah Akun
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Nama</th>
                    <th class="py-3 px-4 text-left">Email</th>
                    <th class="py-3 px-4 text-left">Role</th>
                    <th class="py-3 px-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php while($user = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($user['nama']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="py-3 px-4">
                            <span class="capitalize px-2 py-1 text-xs font-semibold rounded-full <?php echo ($user['role'] == 'asisten') ? 'bg-indigo-200 text-indigo-800' : 'bg-green-200 text-green-800'; ?>">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="akun_edit.php?id=<?php echo $user['id']; ?>" class="text-sm bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-lg mr-2">Edit</a>
                            <?php if ($_SESSION['user_id'] != $user['id']): // Cegah admin menghapus dirinya sendiri ?>
                                <a href="akun_hapus.php?id=<?php echo $user['id']; ?>" class="text-sm bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg" onclick="return confirm('Yakin ingin menghapus akun ini?');">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>