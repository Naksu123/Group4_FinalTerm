<?php
// ============================================================
//  header.php
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | <?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../src/style/global.css">
    <link rel="stylesheet" href="../src/style/admin-styles.css?v=<?= time() ?>">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="layout">

    <!-- ==================== SIDEBAR ==================== -->
    <aside class="sidebar">

        <div class="sidebar-brand">
            <div class="school-badge">
                <div class="brand-icon"><i class="bi bi-tencent-qq"></i></div>
                <div class="brand-text">
                    <span class="brand-name">CIT11333Z</span>
                    <span class="brand-sub">S.Y. 2025-2026</span>
                </div>
            </div>
        </div>

        
        <div class="sidebar-profile">
            <div class="avatar"><img src="../src/assets/images/hiro-avatar.png" alt="Avatar"></div>
            <div class="profile-info">
                <div class="name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'User') ?></div>
                <div class="id"><?= htmlspecialchars($_SESSION['user']['id'] ?? 'N/A') ?></div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Navigation</div>
            <a href="index.php"  class="nav-link <?= $active_page === 'profile'  ? 'active' : '' ?>">
                <i class="bi bi-person-fill"></i> Profile
            </a>
            <a href="subjects.php" class="nav-link <?= $active_page === 'subjects' ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i> Subjects
            </a>
            <a href="grades.php"   class="nav-link <?= $active_page === 'grades'   ? 'active' : '' ?>">
                <i class="bi bi-trophy-fill"></i> Grades
            </a>
            <div class="nav-label" style="margin-top:8px;">Account</div>
            <a href="logout.php" class="nav-link logout">
                <span class="nav-icon"><i class="bi bi-box-arrow-right"></i> </span> Logout
            </a>
        </nav>

        <div class="sidebar-footer">
            PHP Student Dashboard v2.0<br>
            &copy; <?= date('Y') ?> CIT11333Z Final Term Project
        </div>
    </aside>

    <!-- ==================== MAIN ==================== -->
    <div class="main">
        <header class="topbar">
            <div class="topbar-left">
                <div class="page-title">
                    <?= ($page_icon ?? '') ?> <?= htmlspecialchars($page_title ?? 'Dashboard') ?>
                </div>
                <div class="breadcrumb">Dashboard / <?= htmlspecialchars($page_title ?? 'Dashboard') ?></div>
            </div>
            <span class="badge-pill"><?= date('F d, Y') ?></span>
        </header>

        <main class="content">
