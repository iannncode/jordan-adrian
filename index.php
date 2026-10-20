<?php
require_once __DIR__ . '/includes/bootstrap.php';

$mrModel = new MaintenanceRequest($pdo);

$filters = [
    'building_id'    => $_GET['building_id'] ?? '',
    'category_id'    => $_GET['category_id'] ?? '',
    'status'         => $_GET['status'] ?? '',
    'priority_level' => $_GET['priority_level'] ?? '',
    'search'         => trim($_GET['search'] ?? ''),
];

$requests = $mrModel->getAll($filters);
$counts   = $mrModel->countByStatus();

$buildings  = $lookup->getBuildings();
$categories = $lookup->getCategories();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Maintenance Requests</h1>
    <p>Track, assign, and resolve facility issues across campus in one place.</p>
  </div>
  <a href="add.php" class="btn btn-gold"><i class="fa-solid fa-plus"></i> New Request</a>
</div>

<?= flash_message() ?>

<div class="stat-grid">
  <div class="stat-card pending">
    <div class="label">Pending</div>
    <div class="value"><?= (int)$counts['Pending'] ?></div>
  </div>
  <div class="stat-card progress">
    <div class="label">In Progress</div>
    <div class="value"><?= (int)$counts['In Progress'] ?></div>
  </div>
  <div class="stat-card completed">
    <div class="label">Completed</div>
    <div class="value"><?= (int)$counts['Completed'] ?></div>
  </div>
  <div class="stat-card cancelled">
    <div class="label">Cancelled</div>
    <div class="value"><?= (int)$counts['Cancelled'] ?></div>
  </div>
</div>

<div class="panel">
  <div class="panel-title"><span class="bar"></span> Search &amp; Filter</div>
  <form method="get" action="index.php" class="filter-bar">
    <div class="field">
      <label>Building</label>
      <select name="building_id">
        <option value="">All Buildings</option>
        <?php foreach ($buildings as $b): ?>
          <option value="<?= (int)$b['building_id'] ?>" <?= $filters['building_id'] == $b['building_id'] ? 'selected' : '' ?>>
            <?= e($b['building_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Category</label>
      <select name="category_id">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['category_id'] ?>" <?= $filters['category_id'] == $c['category_id'] ? 'selected' : '' ?>>
            <?= e($c['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Status</label>
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach (['Pending','In Progress','Completed','Cancelled'] as $s): ?>
          <option value="<?= e($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Priority</label>
      <select name="priority_level">
        <option value="">All Priorities</option>
        <?php foreach (['Urgent','High','Medium','Low'] as $p): ?>
          <option value="<?= e($p) ?>" <?= $filters['priority_level'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Search</label>
      <input type="search" name="search" placeholder="Description, requester, room..." value="<?= e($filters['search']) ?>">
    </div>
    <div class="field">
      <button type="submit" class="btn btn-emerald"><i class="fa-solid fa-filter"></i> Apply</button>
    </div>
  </form>
</div>

<div class="panel">
  <div class="panel-title"><span class="bar"></span> All Requests (<?= count($requests) ?>)</div>

  <?php if (empty($requests)): ?>
    <div class="empty-state">
      <div class="icon"><i class="fa-regular fa-folder-open"></i></div>
      <p>No maintenance requests match your filters yet.</p>
      <a href="add.php" class="btn btn-gold btn-sm"><i class="fa-solid fa-plus"></i> Submit the first request</a>
    </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Request</th>
          <th>Requester</th>
          <th>Location</th>
          <th>Category</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Technician</th>
          <th>Submitted</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td class="cell-desc">
              <div class="req-id">#<?= (int)$r['request_id'] ?></div>
              <?= e(mb_strimwidth($r['description'], 0, 70, '...')) ?>
            </td>
            <td><?= e($r['requester_name'] ?? '—') ?></td>
            <td><?= e(($r['building_name'] ?? '—') . ' / ' . ($r['room_number'] ?? '—')) ?></td>
            <td><?= e($r['category_name'] ?? '—') ?></td>
            <td><span class="badge <?= priority_badge_class($r['priority_level']) ?>"><?= e($r['priority_level']) ?></span></td>
            <td><span class="badge <?= status_badge_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
            <td><?= e($r['technician_name'] ?? '— unassigned —') ?></td>
            <td><?= format_dt($r['date_submitted']) ?></td>
            <td>
              <div class="row-actions">
                <a href="edit.php?id=<?= (int)$r['request_id'] ?>" class="btn btn-outline btn-icon" title="Edit / Assign">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <button type="button" class="btn btn-danger btn-icon"
                        data-delete-url="actions/delete_request.php?id=<?= (int)$r['request_id'] ?>"
                        data-delete-label="request #<?= (int)$r['request_id'] ?>"
                        title="Delete">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
