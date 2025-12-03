<?php
session_start();
include "koneksi.php";

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION['admin'];

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);

        $query = "INSERT INTO mapel (nama_mapel) VALUES ('$nama_mapel')";
        mysqli_query($koneksi, $query);
        header("Location: kelola_mapel.php");
        exit;
    }

    if (isset($_POST['edit'])) {
        $id_mapel = mysqli_real_escape_string($koneksi, $_POST['id_mapel']);
        $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);

        $query = "UPDATE mapel SET nama_mapel='$nama_mapel' WHERE id_mapel='$id_mapel'";
        mysqli_query($koneksi, $query);
        header("Location: kelola_mapel.php");
        exit;
    }
}

if (isset($_GET['hapus'])) {
    $id_mapel = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $query = "DELETE FROM mapel WHERE id_mapel='$id_mapel'";
    mysqli_query($koneksi, $query);
    header("Location: kelola_mapel.php");
    exit;
}

// Ambil data mapel
$query_mapel = "SELECT * FROM mapel ORDER BY nama_mapel ASC";
$result_mapel = mysqli_query($koneksi, $query_mapel);

// Jika edit, ambil data mapel
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_mapel = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $query_edit = "SELECT * FROM mapel WHERE id_mapel='$id_mapel'";
    $result_edit = mysqli_query($koneksi, $query_edit);
    $edit_data = mysqli_fetch_assoc($result_edit);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mata Pelajaran - Sistem Penilaian Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 18px;
            font-weight: 600;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 5px;
        }

        .menu {
            list-style: none;
        }

        .menu li {
            margin: 5px 0;
        }

        .menu-item {
            display: block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #fff;
            padding-left: 25px;
        }

        .menu-item i {
            margin-right: 10px;
            width: 20px;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-left h1 {
            font-size: 24px;
            color: #333;
            font-weight: 600;
        }

        .topbar-left p {
            font-size: 14px;
            color: #666;
            margin-top: 2px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .content {
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .page-title p {
            color: #666;
            font-size: 14px;
        }

        .btn-add {
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add:hover {
            background: #218838;
        }

        .data-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: #ffc107;
            color: #212529;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .modal-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 15px 20px;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Sistem Penilaian</h2>
        <p>Kelola Mata Pelajaran</p>
    </div>
    <ul class="menu">
         <li><a href="dashboard.php" class="menu-item active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="kelola_siswa.php" class="menu-item"><i class="fas fa-users"></i> Kelola Siswa</a></li>
        <li><a href="kelola_mapel.php" class="menu-item"><i class="fas fa-book"></i> Kelola Mata Pelajaran</a></li>
        <li><a href="kelola_kelas.php" class="menu-item"><i class="fas fa-school"></i> Kelola Kelas</a></li>
        <li><a href="kelola_nilai.php" class="menu-item"><i class="fas fa-chart-line"></i> Kelola Nilai</a></li>
        <li><a href="rapor.php" class="menu-item"><i class="fas fa-user-graduate"></i> Rapor Siswa</a></li>
        <li><a href="laporan.php" class="menu-item"><i class="fas fa-file-alt"></i> Laporan</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h1>Sistem Penilaian Sekolah</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($admin['nama_lengkap']); ?>!</p>
        </div>
        <div class="topbar-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($admin['nama_lengkap'], 0, 1)); ?>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2><i class="fas fa-book"></i> Kelola Mata Pelajaran</h2>
                <p>Kelola data mata pelajaran sekolah</p>
            </div>
            <a href="#" class="btn-add" onclick="openModal()">
                <i class="fas fa-plus"></i> Tambah Mata Pelajaran
            </a>
        </div>

        <div class="data-table">
            <div class="table-header">
                <h3>Data Mata Pelajaran</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mata Pelajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_mapel) > 0) {
                            $i = 1;
                            while ($mapel = mysqli_fetch_assoc($result_mapel)) {
                                echo "<tr>";
                                echo "<td>$i</td>";
                                echo "<td>{$mapel['nama_mapel']}</td>";
                                echo "<td class='action-buttons'>";
                                echo "<a href='?edit={$mapel['id_mapel']}' class='btn-edit' onclick='openModal({$mapel['id_mapel']})'><i class='fas fa-edit'></i> Edit</a>";
                                echo "<a href='?hapus={$mapel['id_mapel']}' class='btn-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus mata pelajaran ini?\")'><i class='fas fa-trash'></i> Hapus</a>";
                                echo "</td>";
                                echo "</tr>";
                                $i++;
                            }
                        } else {
                            echo "<tr><td colspan='3' class='empty-state'>";
                            echo "<i class='fas fa-book'></i>";
                            echo "<p>Belum ada data mata pelajaran</p>";
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Mata Pelajaran -->
<div id="mapelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Mata Pelajaran</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="id_mapel" id="id_mapel">
            <div class="form-group">
                <label for="nama_mapel">Nama Mata Pelajaran</label>
                <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" name="tambah" id="submitBtn" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id = null) {
    const modal = document.getElementById('mapelModal');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');
    const form = modal.querySelector('form');

    if (id) {
        modalTitle.textContent = 'Edit Mata Pelajaran';
        submitBtn.name = 'edit';
        submitBtn.textContent = 'Update';

        // Fetch data and populate form (simplified - in real app, use AJAX)
        <?php if ($edit_data): ?>
        document.getElementById('id_mapel').value = '<?php echo $edit_data['id_mapel']; ?>';
        document.getElementById('nama_mapel').value = '<?php echo $edit_data['nama_mapel']; ?>';
        <?php endif; ?>
    } else {
        modalTitle.textContent = 'Tambah Mata Pelajaran';
        submitBtn.name = 'tambah';
        submitBtn.textContent = 'Simpan';
        form.reset();
    }

    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('mapelModal');
    modal.style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('mapelModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Auto-open modal if edit parameter exists
<?php if (isset($_GET['edit'])): ?>
window.onload = function() {
    openModal(<?php echo $_GET['edit']; ?>);
}
<?php endif; ?>
</script>

</body>
</html>
