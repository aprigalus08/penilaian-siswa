<?php
session_start();
include "koneksi.php";

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION['admin'];

// Handle report generation
$report_type = isset($_GET['type']) ? $_GET['type'] : 'dashboard';
$filter_semester = isset($_GET['semester']) ? mysqli_real_escape_string($koneksi, $_GET['semester']) : null;
$filter_tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($koneksi, $_GET['tahun']) : null;
$filter_siswa = isset($_GET['siswa']) ? mysqli_real_escape_string($koneksi, $_GET['siswa']) : null;

$data = null;
$report_title = '';

// Generate report based on type
switch ($report_type) {
    case 'siswa':
        $report_title = 'Rapor Siswa';
        if ($filter_siswa && $filter_semester && $filter_tahun) {
            $query = "SELECT n.*, m.nama_mapel, k.nama_kelas, s.nama_siswa, s.nis
                     FROM nilai n
                     LEFT JOIN mapel m ON n.id_mapel = m.id_mapel
                     LEFT JOIN siswa s ON n.id_siswa = s.id_siswa
                     LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                     WHERE n.id_siswa = '$filter_siswa' AND n.semester = '$filter_semester' AND n.tahun_ajaran = '$filter_tahun'
                     ORDER BY m.nama_mapel ASC";
            $result = mysqli_query($koneksi, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        break;

    case 'rekap':
        $report_title = 'Rekap Nilai Keseluruhan';
        $query = "SELECT k.nama_kelas, m.nama_mapel, n.semester, n.tahun_ajaran,
                         COUNT(n.id_nilai) as jumlah_siswa,
                         AVG(n.nilai_rapor) as rata_rata
                 FROM nilai n
                 LEFT JOIN siswa s ON n.id_siswa = s.id_siswa
                 LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                 LEFT JOIN mapel m ON n.id_mapel = m.id_mapel
                 GROUP BY k.id_kelas, m.id_mapel, n.semester, n.tahun_ajaran
                 ORDER BY k.nama_kelas ASC, m.nama_mapel ASC";
        $result = mysqli_query($koneksi, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        break;

    default:
        $report_title = 'Ringkasan Laporan';
        // Get summary statistics
        $query_stats = "SELECT
                        COUNT(DISTINCT s.id_siswa) as total_siswa_dinilai,
                        COUNT(DISTINCT n.id_mapel) as total_mapel,
                        COUNT(n.id_nilai) as total_penilaian,
                        AVG(n.nilai_rapor) as rata_rata_umum,
                        MAX(n.nilai_rapor) as nilai_tertinggi,
                        MIN(n.nilai_rapor) as nilai_terendah
                       FROM nilai n
                       LEFT JOIN siswa s ON n.id_siswa = s.id_siswa";
        $result_stats = mysqli_query($koneksi, $query_stats);
        $data = mysqli_fetch_assoc($result_stats);
        break;
}

// Ambil data untuk dropdown siswa
$query_siswa = "SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id_kelas ORDER BY s.nama_siswa ASC";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$siswa_options = "";
while ($siswa = mysqli_fetch_assoc($result_siswa)) {
    $selected = ($filter_siswa == $siswa['id_siswa']) ? "selected" : "";
    $siswa_options .= "<option value='{$siswa['id_siswa']}' $selected>{$siswa['nama_siswa']} ({$siswa['nis']}) - {$siswa['nama_kelas']}</option>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Penilaian Sekolah</title>
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

        .menu-item:hover, .menu-item.active {
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

        .report-types {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .report-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .report-type-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .report-type-card:hover, .report-type-card.active {
            background: #667eea;
            color: white;
            border-color: #5a67d8;
        }

        .report-type-card i {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .report-type-card h4 {
            font-size: 14px;
            font-weight: 600;
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

        .report-header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-title h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .report-title p {
            color: #666;
            font-size: 14px;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-export {
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-export:hover {
            background: #218838;
        }

        .report-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .report-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-table tr:hover {
            background: #f8f9fa;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stats-card .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stats-card .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

            .report-type-grid {
                grid-template-columns: 1fr;
            }

            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                min-width: auto;
            }

            .report-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .export-buttons {
                justify-content: center;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .sidebar, .topbar, .report-types, .filters, .export-buttons {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .report-content {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Sistem Penilaian</h2>
        <p>Laporan</p>
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
        <!-- Report Types -->
        <div class="report-types">
            <h3>Pilih Jenis Laporan</h3>
            <div class="report-type-grid">
                <div class="report-type-card <?php echo ($report_type == 'dashboard') ? 'active' : ''; ?>" onclick="changeReportType('dashboard')">
                    <i class="fas fa-chart-bar"></i>
                    <h4>Ringkasan</h4>
                </div>
                <div class="report-type-card <?php echo ($report_type == 'siswa') ? 'active' : ''; ?>" onclick="changeReportType('siswa')">
                    <i class="fas fa-user-graduate"></i>
                    <h4>Rapor Siswa</h4>
                </div>
                <div class="report-type-card <?php echo ($report_type == 'rekap') ? 'active' : ''; ?>" onclick="changeReportType('rekap')">
                    <i class="fas fa-table"></i>
                    <h4>Rekap Nilai</h4>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <?php if ($report_type == 'siswa'): ?>
        <div class="filters">
            <form method="GET">
                <input type="hidden" name="type" value="siswa">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="siswa">Pilih Siswa</label>
                        <select class="filter-control" id="siswa" name="siswa" required>
                            <option value="">Pilih Siswa</option>
                            <?php echo $siswa_options; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="semester">Semester</label>
                        <select class="filter-control" id="semester" name="semester" required>
                            <option value="">Pilih Semester</option>
                            <option value="Ganjil" <?php echo ($filter_semester == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                            <option value="Genap" <?php echo ($filter_semester == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="tahun">Tahun Ajaran</label>
                        <input type="text" class="filter-control" id="tahun" name="tahun" placeholder="2023/2024" value="<?php echo htmlspecialchars($filter_tahun); ?>" required>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Generate Rapor
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Report Header -->
        <div class="report-header">
            <div class="report-title">
                <h2><i class="fas fa-file-alt"></i> <?php echo $report_title; ?></h2>
                <p>
                    <?php
                    if ($report_type == 'dashboard') {
                        echo 'Ringkasan data penilaian keseluruhan';
                    } elseif ($report_type == 'rekap') {
                        echo 'Rekapitulasi nilai keseluruhan';
                    } else {
                        $filters = [];
                        if ($filter_siswa && $report_type == 'siswa') {
                            $siswa_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT s.nama_siswa, s.nis FROM siswa s WHERE id_siswa='$filter_siswa'"));
                            $filters[] = $siswa_data['nama_siswa'] . ' (' . $siswa_data['nis'] . ')';
                        }
                        if ($filter_semester) $filters[] = "Semester $filter_semester";
                        if ($filter_tahun) $filters[] = "Tahun $filter_tahun";
                        echo implode(' - ', $filters);
                    }
                    ?>
                </p>
            </div>
            <div class="export-buttons">
                <a href="#" onclick="window.print()" class="btn-export">
                    <i class="fas fa-print"></i> Print
                </a>
                <a href="#" onclick="exportToExcel()" class="btn-export">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>

        <!-- Report Content -->
        <div class="report-content">
            <?php if ($report_type == 'dashboard'): ?>
                <!-- Dashboard Stats -->
                <div class="stats-cards">
                    <div class="stats-card">
                        <div class="stat-number"><?php echo $data['total_siswa_dinilai'] ?? 0; ?></div>
                        <div class="stat-label">Siswa Dinilai</div>
                    </div>
                    <div class="stats-card">
                        <div class="stat-number"><?php echo $data['total_mapel'] ?? 0; ?></div>
                        <div class="stat-label">Mata Pelajaran</div>
                    </div>
                    <div class="stats-card">
                        <div class="stat-number"><?php echo $data['total_penilaian'] ?? 0; ?></div>
                        <div class="stat-label">Total Penilaian</div>
                    </div>
                    <div class="stats-card">
                        <div class="stat-number"><?php echo number_format($data['rata_rata_umum'] ?? 0, 1); ?></div>
                        <div class="stat-label">Rata-rata Umum</div>
                    </div>
                </div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Statistik</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Rata-rata Nilai Keseluruhan</td>
                            <td><?php echo number_format($data['rata_rata_umum'] ?? 0, 1); ?></td>
                        </tr>
                        <tr>
                            <td>Nilai Tertinggi</td>
                            <td><?php echo number_format($data['nilai_tertinggi'] ?? 0, 1); ?></td>
                        </tr>
                        <tr>
                            <td>Nilai Terendah</td>
                            <td><?php echo number_format($data['nilai_terendah'] ?? 0, 1); ?></td>
                        </tr>
                        <tr>
                            <td>Total Siswa yang Dinilai</td>
                            <td><?php echo $data['total_siswa_dinilai'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <td>Total Mata Pelajaran</td>
                            <td><?php echo $data['total_mapel'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <td>Total Data Penilaian</td>
                            <td><?php echo $data['total_penilaian'] ?? 0; ?></td>
                        </tr>
                    </tbody>
                </table>

            <?php elseif ($report_type == 'siswa' && $data): ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Sikap</th>
                            <th>Keterampilan</th>
                            <th>Nilai Rapor</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_nilai = 0;
                        $total_keterampilan = 0;
                        $count = 0;
                        foreach ($data as $row):
                            $total_nilai += $row['nilai_rapor'];
                            $total_keterampilan += $row['keterampilan'];
                            $count++;
                            $grade_class = '';
                            if ($row['nilai_rapor'] >= 85) $grade_class = 'grade-a';
                            elseif ($row['nilai_rapor'] >= 75) $grade_class = 'grade-b';
                            elseif ($row['nilai_rapor'] >= 65) $grade_class = 'grade-c';
                            else $grade_class = 'grade-d';
                        ?>
                        <tr>
                            <td><?php echo $row['nama_mapel']; ?></td>
                            <td><?php echo $row['sikap']; ?></td>
                            <td><?php echo number_format($row['keterampilan'], 1); ?></td>
                            <td><?php echo number_format($row['nilai_rapor'], 1); ?></td>
                            <td><span class="grade-badge <?php echo $grade_class; ?>"><?php echo number_format($row['nilai_rapor'], 1); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if ($count > 0): ?>
                        <tr style="background: #f8f9fa; font-weight: bold;">
                            <td>Rata-rata</td>
                            <td>-</td>
                            <td><?php echo number_format($total_keterampilan / $count, 1); ?></td>
                            <td><?php echo number_format($total_nilai / $count, 1); ?></td>
                            <td>-</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($report_type == 'rekap' && $data): ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Semester</th>
                            <th>Tahun Ajaran</th>
                            <th>Jumlah Siswa</th>
                            <th>Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo $row['nama_kelas']; ?></td>
                            <td><?php echo $row['nama_mapel']; ?></td>
                            <td><?php echo $row['semester']; ?></td>
                            <td><?php echo $row['tahun_ajaran']; ?></td>
                            <td><?php echo $row['jumlah_siswa']; ?></td>
                            <td><?php echo number_format($row['rata_rata'], 1); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-line"></i>
                    <p>Silakan pilih jenis laporan dan filter yang diperlukan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function changeReportType(type) {
    window.location.href = 'laporan.php?type=' + type;
}

function exportToExcel() {
    // Simple CSV export
    const table = document.querySelector('.report-table');
    if (!table) {
        alert('Tidak ada data untuk diekspor');
        return;
    }

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].textContent.replace(/<[^>]*>/g, '').trim() + '"');
        }

        csv.push(row.join(','));
    }

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'laporan_nilai.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>

</body>
</html>
