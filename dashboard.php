<?php
session_start();
include "koneksi.php";

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION['admin'];

// Ambil data statistik
$query_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa");
$data_siswa = mysqli_fetch_assoc($query_siswa);

$query_mapel = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mapel");
$data_mapel = mysqli_fetch_assoc($query_mapel);

$query_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kelas");
$data_kelas = mysqli_fetch_assoc($query_kelas);

$query_nilai = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM nilai");
$data_nilai = mysqli_fetch_assoc($query_nilai);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Admin - Sistem Penilaian Siswa RPL</title>
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

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 16px;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .recent-activity {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .recent-activity h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .activity-content h4 {
            font-size: 14px;
            color: #333;
            margin-bottom: 2px;
        }

        .activity-content p {
            font-size: 12px;
            color: #666;
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

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Sistem Penilaian</h2>
        <p>Dashboard Admin</p>
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
            <h1>Sistem Penilaian Siswa RPL</h1>
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
        <div class="welcome-section">
            <h2>Selamat Datang di Dashboard Admin</h2>
            <p>Kelola data siswa, nilai, mata pelajaran, dan kelas dengan mudah dan efisien.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #667eea; color: white;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $data_siswa['total']; ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #764ba2; color: white;">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $data_mapel['total']; ?></div>
                <div class="stat-label">Mata Pelajaran</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #f093fb; color: white;">
                        <i class="fas fa-school"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $data_kelas['total']; ?></div>
                <div class="stat-label">Kelas</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: #4facfe; color: white;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $data_nilai['total']; ?></div>
                <div class="stat-label">Data Nilai</div>
            </div>
        </div>
</body>
</html>
