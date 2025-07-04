<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_GET['id'] ?? null;
if (!$id_user) {
    header("Location: manajemen_akun.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    $sql = "UPDATE users SET nama = ?, email = ?, role = ?";
    $types = "sss";
    $params = [$nama, $email, $role];

    if (!empty($password)) {
        $sql .= ", password = ?";
        $types .= "s";
        $params[] = password_hash($password, PASSWORD_BCRYPT);
    }

    $sql .= " WHERE id = ?";
    $types .= "i";
    $params[] = $id_user;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        header("Location: manajemen_akun.php?status=edit_sukses");
        exit();
    } else {
        $error = "Gagal memperbarui akun: " . $stmt->error;
    }
    $stmt->close();
}

$stmt_get = $conn->prepare("SELECT nama, email, role FROM users WHERE id = ?");
$stmt_get->bind_param("i", $id_user);
$stmt_get->execute();
$user = $stmt_get->get_result()->fetch_assoc();
$stmt_get->close();

$pageTitle = 'Edit Akun';
$activePage = 'manajemen_akun';
require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <a href="manajemen_akun.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
        &larr; Kembali ke Daftar Akun
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Edit Akun</h2>

    <?php if ($error): ?> <div class="bg-red-100 text-red-700 p-3 mb-4 rounded-lg"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>

    <form action="akun_edit.php?id=<?php echo $id_user; ?>" method="POST">
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" class="shadow border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="shadow border rounded w-full py-2 px-3" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-bold mb-2">Password Baru (Opsional)</label>
            <input type="password" name="password" id="password" class="shadow border rounded w-full py-2 px-3" placeholder="Kosongkan jika tidak ingin diubah">
        </div>
        <div class="mb-6">
            <label for="role" class="block text-gray-700 font-bold mb-2">Role</label>
            <select name="role" id="role" class="shadow border rounded w-full py-2 px-3" required>
                <option value="mahasiswa" <?php echo ($user['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                <option value="asisten" <?php echo ($user['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
            </select>
        </div>
        <div class="flex justify-end">
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