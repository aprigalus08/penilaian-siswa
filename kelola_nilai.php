<?php
session_start();
include "koneksi.php";

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION['admin'];

// Check filters
$filter_kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($koneksi, $_GET['kelas']) : null;
$filter_mapel = isset($_GET['mapel']) ? mysqli_real_escape_string($koneksi, $_GET['mapel']) : null;
$filter_semester = isset($_GET['semester']) ? mysqli_real_escape_string($koneksi, $_GET['semester']) : null;
$filter_tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($koneksi, $_GET['tahun']) : null;

$nama_kelas_filter = null;
$nama_mapel_filter = null;

if ($filter_kelas) {
    $query_nama_kelas = "SELECT nama_kelas FROM kelas WHERE id_kelas='$filter_kelas'";
    $result_nama_kelas = mysqli_query($koneksi, $query_nama_kelas);
    if ($result_nama_kelas && mysqli_num_rows($result_nama_kelas) > 0) {
        $nama_kelas_filter = mysqli_fetch_assoc($result_nama_kelas)['nama_kelas'];
    }
}

if ($filter_mapel) {
    $query_nama_mapel = "SELECT nama_mapel FROM mapel WHERE id_mapel='$filter_mapel'";
    $result_nama_mapel = mysqli_query($koneksi, $query_nama_mapel);
    if ($result_nama_mapel && mysqli_num_rows($result_nama_mapel) > 0) {
        $nama_mapel_filter = mysqli_fetch_assoc($result_nama_mapel)['nama_mapel'];
    }
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $id_siswa = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);
        $id_mapel = mysqli_real_escape_string($koneksi, $_POST['id_mapel']);
        $semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
        $tahun_ajaran = mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']);
        $ulangan_harian = floatval($_POST['ulangan_harian']);
        $uts = floatval($_POST['uts']);
        $uas = floatval($_POST['uas']);
        $keterampilan = floatval($_POST['keterampilan']);
        $sikap = mysqli_real_escape_string($koneksi, $_POST['sikap']);
        $mode_rapor = mysqli_real_escape_string($koneksi, $_POST['mode_rapor']);

        // Calculate nilai_rapor if mode is otomatis
        $nilai_rapor = null;
        if ($mode_rapor == 'otomatis') {
            $nilai_rapor = ($ulangan_harian * 0.3) + ($uts * 0.3) + ($uas * 0.4);
        } else {
            $nilai_rapor = floatval($_POST['nilai_rapor']);
        }

        $query = "INSERT INTO nilai (id_siswa, id_mapel, semester, tahun_ajaran, ulangan_harian, uts, uas, keterampilan, sikap, nilai_rapor, mode_rapor)
                  VALUES ('$id_siswa', '$id_mapel', '$semester', '$tahun_ajaran', '$ulangan_harian', '$uts', '$uas', '$keterampilan', '$sikap', '$nilai_rapor', '$mode_rapor')";
        mysqli_query($koneksi, $query);

        $redirect_params = [];
        if ($filter_kelas) $redirect_params[] = "kelas=$filter_kelas";
        if ($filter_mapel) $redirect_params[] = "mapel=$filter_mapel";
        if ($filter_semester) $redirect_params[] = "semester=$filter_semester";
        if ($filter_tahun) $redirect_params[] = "tahun=$filter_tahun";
        $redirect_url = "kelola_nilai.php" . (count($redirect_params) > 0 ? "?" . implode("&", $redirect_params) : "");
        header("Location: $redirect_url");
        exit;
    }

    if (isset($_POST['edit'])) {
        $id_nilai = mysqli_real_escape_string($koneksi, $_POST['id_nilai']);
        $id_siswa = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);
        $id_mapel = mysqli_real_escape_string($koneksi, $_POST['id_mapel']);
        $semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
        $tahun_ajaran = mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']);
        $ulangan_harian = floatval($_POST['ulangan_harian']);
        $uts = floatval($_POST['uts']);
        $uas = floatval($_POST['uas']);
        $keterampilan = floatval($_POST['keterampilan']);
        $sikap = mysqli_real_escape_string($koneksi, $_POST['sikap']);
        $mode_rapor = mysqli_real_escape_string($koneksi, $_POST['mode_rapor']);

        // Calculate nilai_rapor if mode is otomatis
        $nilai_rapor = null;
        if ($mode_rapor == 'otomatis') {
            $nilai_rapor = ($ulangan_harian * 0.3) + ($uts * 0.3) + ($uas * 0.4);
        } else {
            $nilai_rapor = floatval($_POST['nilai_rapor']);
        }

        $query = "UPDATE nilai SET id_siswa='$id_siswa', id_mapel='$id_mapel', semester='$semester', tahun_ajaran='$tahun_ajaran',
                  ulangan_harian='$ulangan_harian', uts='$uts', uas='$uas', keterampilan='$keterampilan', sikap='$sikap',
                  nilai_rapor='$nilai_rapor', mode_rapor='$mode_rapor' WHERE id_nilai='$id_nilai'";
        mysqli_query($koneksi, $query);

        $redirect_params = [];
        if ($filter_kelas) $redirect_params[] = "kelas=$filter_kelas";
        if ($filter_mapel) $redirect_params[] = "mapel=$filter_mapel";
        if ($filter_semester) $redirect_params[] = "semester=$filter_semester";
        if ($filter_tahun) $redirect_params[] = "tahun=$filter_tahun";
        $redirect_url = "kelola_nilai.php" . (count($redirect_params) > 0 ? "?" . implode("&", $redirect_params) : "");
        header("Location: $redirect_url");
        exit;
    }
}

if (isset($_GET['hapus'])) {
    $id_nilai = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $query = "DELETE FROM nilai WHERE id_nilai='$id_nilai'";
    mysqli_query($koneksi, $query);

    $redirect_params = [];
    if ($filter_kelas) $redirect_params[] = "kelas=$filter_kelas";
    if ($filter_mapel) $redirect_params[] = "mapel=$filter_mapel";
    if ($filter_semester) $redirect_params[] = "semester=$filter_semester";
    if ($filter_tahun) $redirect_params[] = "tahun=$filter_tahun";
    $redirect_url = "kelola_nilai.php" . (count($redirect_params) > 0 ? "?" . implode("&", $redirect_params) : "");
    header("Location: $redirect_url");
    exit;
}

// Build query for nilai data
$query_nilai = "SELECT n.*, s.nama_siswa, s.nis, k.nama_kelas, m.nama_mapel
                FROM nilai n
                LEFT JOIN siswa s ON n.id_siswa = s.id_siswa
                LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                LEFT JOIN mapel m ON n.id_mapel = m.id_mapel
                WHERE 1=1";

if ($filter_kelas) {
    $query_nilai .= " AND s.id_kelas = '$filter_kelas'";
}
if ($filter_mapel) {
    $query_nilai .= " AND n.id_mapel = '$filter_mapel'";
}
if ($filter_semester) {
    $query_nilai .= " AND n.semester = '$filter_semester'";
}
if ($filter_tahun) {
    $query_nilai .= " AND n.tahun_ajaran = '$filter_tahun'";
}

$query_nilai .= " ORDER BY n.tahun_ajaran DESC, n.semester DESC, s.nama_siswa ASC";
$result_nilai = mysqli_query($koneksi, $query_nilai);

// Ambil data untuk dropdown
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);
$kelas_options = "";
while ($kelas = mysqli_fetch_assoc($result_kelas)) {
    $selected = ($filter_kelas == $kelas['id_kelas']) ? "selected" : "";
    $kelas_options .= "<option value='{$kelas['id_kelas']}' $selected>{$kelas['nama_kelas']}</option>";
}

$query_mapel = "SELECT * FROM mapel ORDER BY nama_mapel ASC";
$result_mapel = mysqli_query($koneksi, $query_mapel);
$mapel_options = "";
while ($mapel = mysqli_fetch_assoc($result_mapel)) {
    $selected = ($filter_mapel == $mapel['id_mapel']) ? "selected" : "";
    $mapel_options .= "<option value='{$mapel['id_mapel']}' $selected>{$mapel['nama_mapel']}</option>";
}

$query_siswa = "SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas";
if ($filter_kelas) {
    $query_siswa .= " WHERE s.id_kelas = '$filter_kelas'";
}
$query_siswa .= " ORDER BY s.nama_siswa ASC";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$siswa_options = "";
while ($siswa = mysqli_fetch_assoc($result_siswa)) {
    $siswa_options .= "<option value='{$siswa['id_siswa']}'>{$siswa['nama_siswa']} ({$siswa['nis']}) - {$siswa['nama_kelas']}</option>";
}

// Jika edit, ambil data nilai
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_nilai = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $query_edit = "SELECT n.*, s.nama_siswa, s.nis, k.nama_kelas, m.nama_mapel
                   FROM nilai n
                   LEFT JOIN siswa s ON n.id_siswa = s.id_siswa
                   LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                   LEFT JOIN mapel m ON n.id_mapel = m.id_mapel
                   WHERE n.id_nilai='$id_nilai'";
    $result_edit = mysqli_query($koneksi, $query_edit);
    $edit_data = mysqli_fetch_assoc($result_edit);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nilai - Sistem Penilaian Sekolah</title>
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

        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .filter-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-filter {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .btn-filter:hover {
            background: #5a67d8;
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
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 13px;
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
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 3px;
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
            margin: 2% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow-y: auto;
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
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-row-3 {
            display: flex;
            gap: 15px;
        }

        .form-row-3 .form-group {
            flex: 1;
        }

        .form-row-4 {
            display: flex;
            gap: 15px;
        }

        .form-row-4 .form-group {
            flex: 1;
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

        .grade-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .grade-a { background: #28a745; color: white; }
        .grade-b { background: #ffc107; color: #212529; }
        .grade-c { background: #fd7e14; color: white; }
        .grade-d { background: #dc3545; color: white; }

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
                margin: 5% auto;
                width: 95%;
            }

            .form-row, .form-row-3, .form-row-4 {
                flex-direction: column;
                gap: 0;
            }

            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                min-width: auto;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Sistem Penilaian</h2>
        <p>Kelola Nilai</p>
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
        <!-- Filters -->
        <div class="filters">
            <form method="GET">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="kelas">Filter Kelas</label>
                        <select class="filter-control" id="kelas" name="kelas">
                            <option value="">Semua Kelas</option>
                            <?php echo $kelas_options; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="mapel">Filter Mata Pelajaran</label>
                        <select class="filter-control" id="mapel" name="mapel">
                            <option value="">Semua Mata Pelajaran</option>
                            <?php echo $mapel_options; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="semester">Filter Semester</label>
                        <select class="filter-control" id="semester" name="semester">
                            <option value="">Semua Semester</option>
                            <option value="Ganjil" <?php echo ($filter_semester == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                            <option value="Genap" <?php echo ($filter_semester == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="tahun">Filter Tahun Ajaran</label>
                        <input type="text" class="filter-control" id="tahun" name="tahun" placeholder="2023/2024" value="<?php echo htmlspecialchars($filter_tahun); ?>">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="page-header">
            <div class="page-title">
                <h2><i class="fas fa-chart-line"></i> Kelola Nilai</h2>
                <p>
                    <?php
                    $filter_desc = [];
                    if ($nama_kelas_filter) $filter_desc[] = "Kelas $nama_kelas_filter";
                    if ($nama_mapel_filter) $filter_desc[] = "Mapel $nama_mapel_filter";
                    if ($filter_semester) $filter_desc[] = "Semester $filter_semester";
                    if ($filter_tahun) $filter_desc[] = "Tahun $filter_tahun";
                    echo count($filter_desc) > 0 ? implode(" - ", $filter_desc) : "Kelola data nilai siswa";
                    ?>
                </p>
            </div>
            <a href="#" class="btn-add" onclick="openModal()">
                <i class="fas fa-plus"></i> Tambah Nilai
            </a>
        </div>

        <div class="data-table">
            <div class="table-header">
                <h3>Data Nilai</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Semester</th>
                            <th>Tahun Ajaran</th>
                            <th>Ulangan Harian</th>
                            <th>UTS</th>
                            <th>UAS</th>
                            <th>Keterampilan</th>
                            <th>Sikap</th>
                            <th>Nilai Rapor</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result_nilai) > 0) {
                            while ($nilai = mysqli_fetch_assoc($result_nilai)) {
                                $grade_class = '';
                                if ($nilai['nilai_rapor'] >= 85) $grade_class = 'grade-a';
                                elseif ($nilai['nilai_rapor'] >= 75) $grade_class = 'grade-b';
                                elseif ($nilai['nilai_rapor'] >= 65) $grade_class = 'grade-c';
                                else $grade_class = 'grade-d';

                                echo "<tr>";
                                echo "<td>{$nilai['nis']}</td>";
                                echo "<td>{$nilai['nama_siswa']}</td>";
                                echo "<td>{$nilai['nama_kelas']}</td>";
                                echo "<td>{$nilai['nama_mapel']}</td>";
                                echo "<td>{$nilai['semester']}</td>";
                                echo "<td>{$nilai['tahun_ajaran']}</td>";
                                echo "<td>" . number_format($nilai['ulangan_harian'], 1) . "</td>";
                                echo "<td>" . number_format($nilai['uts'], 1) . "</td>";
                                echo "<td>" . number_format($nilai['uas'], 1) . "</td>";
                                echo "<td>" . number_format($nilai['keterampilan'], 1) . "</td>";
                                echo "<td>{$nilai['sikap']}</td>";
                                echo "<td><span class='grade-badge $grade_class'>" . number_format($nilai['nilai_rapor'], 1) . "</span></td>";
                                echo "<td class='action-buttons'>";
                                echo "<a href='?edit={$nilai['id_nilai']}' class='btn-edit' onclick='openModal({$nilai['id_nilai']})'><i class='fas fa-edit'></i> Edit</a>";
                                echo "<a href='?hapus={$nilai['id_nilai']}' class='btn-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus nilai ini?\")'><i class='fas fa-trash'></i> Hapus</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='13' class='empty-state'>";
                            echo "<i class='fas fa-chart-line'></i>";
                            echo "<p>Belum ada data nilai</p>";
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Nilai -->
<div id="nilaiModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Nilai</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="id_nilai" id="id_nilai">

            <div class="form-row">
                <div class="form-group">
                    <label for="id_siswa">Siswa</label>
                    <select class="form-control" id="id_siswa" name="id_siswa" required>
                        <option value="">Pilih Siswa</option>
                        <?php echo $siswa_options; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_mapel">Mata Pelajaran</label>
                    <select class="form-control" id="id_mapel" name="id_mapel" required>
                        <option value="">Pilih Mata Pelajaran</option>
                        <?php echo $mapel_options; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select class="form-control" id="semester" name="semester" required>
                        <option value="">Pilih Semester</option>
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tahun_ajaran">Tahun Ajaran</label>
                    <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" placeholder="2023/2024" required>
                </div>
            </div>

            <div class="form-row-4">
                <div class="form-group">
                    <label for="ulangan_harian">Ulangan Harian (30%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" id="ulangan_harian" name="ulangan_harian" required>
                </div>
                <div class="form-group">
                    <label for="uts">UTS (30%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" id="uts" name="uts" required>
                </div>
                <div class="form-group">
                    <label for="uas">UAS (40%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" id="uas" name="uas" required>
                </div>
                <div class="form-group">
                    <label for="keterampilan">Keterampilan</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" id="keterampilan" name="keterampilan">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sikap">Sikap</label>
                    <select class="form-control" id="sikap" name="sikap">
                        <option value="-">-</option>
                        <option value="A">A (Sangat Baik)</option>
                        <option value="B">B (Baik)</option>
                        <option value="C">C (Cukup)</option>
                        <option value="D">D (Kurang)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="mode_rapor">Mode Perhitungan</label>
                    <select class="form-control" id="mode_rapor" name="mode_rapor" onchange="toggleNilaiRapor()" required>
                        <option value="otomatis">Otomatis (30% UH + 30% UTS + 40% UAS)</option>
                        <option value="manual">Manual</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="nilai_rapor_group" style="display: none;">
                <label for="nilai_rapor">Nilai Rapor (Manual)</label>
                <input type="number" step="0.1" min="0" max="100" class="form-control" id="nilai_rapor" name="nilai_rapor">
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
    const modal = document.getElementById('nilaiModal');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');
    const form = modal.querySelector('form');

    if (id) {
        modalTitle.textContent = 'Edit Nilai';
        submitBtn.name = 'edit';
        submitBtn.textContent = 'Update';

        // Fetch data and populate form (simplified - in real app, use AJAX)
        <?php if ($edit_data): ?>
        document.getElementById('id_nilai').value = '<?php echo $edit_data['id_nilai']; ?>';
        document.getElementById('id_siswa').value = '<?php echo $edit_data['id_siswa']; ?>';
        document.getElementById('id_mapel').value = '<?php echo $edit_data['id_mapel']; ?>';
        document.getElementById('semester').value = '<?php echo $edit_data['semester']; ?>';
        document.getElementById('tahun_ajaran').value = '<?php echo $edit_data['tahun_ajaran']; ?>';
        document.getElementById('ulangan_harian').value = '<?php echo $edit_data['ulangan_harian']; ?>';
        document.getElementById('uts').value = '<?php echo $edit_data['uts']; ?>';
        document.getElementById('uas').value = '<?php echo $edit_data['uas']; ?>';
        document.getElementById('keterampilan').value = '<?php echo $edit_data['keterampilan']; ?>';
        document.getElementById('sikap').value = '<?php echo $edit_data['sikap']; ?>';
        document.getElementById('mode_rapor').value = '<?php echo $edit_data['mode_rapor']; ?>';
        document.getElementById('nilai_rapor').value = '<?php echo $edit_data['nilai_rapor']; ?>';
        toggleNilaiRapor();
        <?php endif; ?>
    } else {
        modalTitle.textContent = 'Tambah Nilai';
        submitBtn.name = 'tambah';
        submitBtn.textContent = 'Simpan';
        form.reset();
        toggleNilaiRapor();
    }

    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('nilaiModal');
    modal.style.display = 'none';
}

function toggleNilaiRapor() {
    const mode = document.getElementById('mode_rapor').value;
    const nilaiRaporGroup = document.getElementById('nilai_rapor_group');
    const nilaiRaporInput = document.getElementById('nilai_rapor');

    if (mode === 'manual') {
        nilaiRaporGroup.style.display = 'block';
        nilaiRaporInput.required = true;
    } else {
        nilaiRaporGroup.style.display = 'none';
        nilaiRaporInput.required = false;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('nilaiModal');
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
