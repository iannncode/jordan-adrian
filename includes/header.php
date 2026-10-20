<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' · CFMTS' : 'CFMTS' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= APP_BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <header class="topbar">
    <div class="brand">
      <div class="brand-mark"><i class="fa-solid fa-screwdriver-wrench"></i></div>
      <div class="brand-text">
        <div class="title">CFMTS</div>
        <div class="subtitle">Campus Facility Maintenance Tracking</div>
      </div>
    </div>
    <div class="topbar-right">
      <form method="post" action="<?= e($_SERVER['REQUEST_URI'] ?? 'index.php') ?>" id="personaForm" style="margin:0;">
        <select name="switch_user_id" onchange="document.getElementById('personaForm').submit()"
                style="background:rgba(255,255,255,0.08); color:#fff; border:1px solid rgba(255,255,255,0.25); border-radius:8px; padding:0.4rem 0.6rem; font-size:0.8rem;">
          <?php foreach ($allUsers as $u): ?>
            <option value="<?= (int)$u['user_id'] ?>" <?= (isset($currentUser) && (int)$currentUser['user_id'] === (int)$u['user_id']) ? 'selected' : '' ?>>
              <?= e($u['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
      <div class="persona">
        <strong><?= e($currentUser['full_name'] ?? 'Guest') ?></strong>
        <span>Acting as this user</span>
      </div>
    </div>
  </header>
  <div class="main-content">
