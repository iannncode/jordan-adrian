<?php
require_once __DIR__ . '/includes/bootstrap.php';

$mrModel = new MaintenanceRequest($pdo);

$id = (int) ($_GET['id'] ?? 0);
$request = $mrModel->getById($id);

if (!$request) {
    header('Location: index.php?error=Request not found');
    exit;
}

$rooms       = $lookup->getRooms();
$categories  = $lookup->getCategories();
$techRoleId  = $lookup->getRoleIdByName('Technician');
$technicians = $lookup->getUsers($techRoleId);
$assignedTechId = $mrModel->getAssignedTechnicianId($id);
$logs = $mrModel->getActivityLogs($id);

$pageTitle = 'Edit Request #' . $id;
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Request #<?= (int)$request['request_id'] ?></h1>
    <p>Submitted by <?= e($request['requester_name'] ?? 'Unknown') ?> on <?= format_dt($request['date_submitted']) ?></p>
  </div>
  <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
</div>

<?= flash_message() ?>

<div class="panel">
  <div class="panel-title"><span class="bar"></span> Update Request</div>
  <form method="post" action="actions/save_request.php">
    <input type="hidden" name="request_id" value="<?= (int)$request['request_id'] ?>">
    <input type="hidden" name="requester_id" value="<?= (int)$request['requester_id'] ?>">

    <div class="form-grid">
      <div class="field">
        <label>Category</label>
        <select name="category_id" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['category_id'] ?>" <?= (int)$c['category_id'] === (int)$request['category_id'] ? 'selected' : '' ?>>
              <?= e($c['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Room</label>
        <select name="room_id" required>
          <?php foreach ($rooms as $r): ?>
            <option value="<?= (int)$r['room_id'] ?>" <?= (int)$r['room_id'] === (int)$request['room_id'] ? 'selected' : '' ?>>
              <?= e($r['building_name'] . ' — ' . $r['room_number'] . ' (' . $r['floor_level'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Priority Level</label>
        <select name="priority_level" required>
          <?php foreach (['Low','Medium','High','Urgent'] as $p): ?>
            <option value="<?= $p ?>" <?= $request['priority_level'] === $p ? 'selected' : '' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Status</label>
        <select name="status" required>
          <?php foreach (['Pending','In Progress','Completed','Cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $request['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Assign Technician</label>
        <select name="technician_id">
          <option value="">— Unassigned —</option>
          <?php foreach ($technicians as $t): ?>
            <option value="<?= (int)$t['user_id'] ?>" <?= (int)$t['user_id'] === (int)$assignedTechId ? 'selected' : '' ?>>
              <?= e($t['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Date Resolved</label>
        <input type="text" value="<?= $request['date_resolved'] ? format_dt($request['date_resolved']) : 'Not yet resolved' ?>" disabled>
      </div>

      <div class="field full">
        <label>Description</label>
        <textarea name="description" required><?= e($request['description']) ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <a href="index.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-gold"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
    </div>
  </form>
</div>

<div class="panel">
  <div class="panel-title"><span class="bar"></span> Activity Log</div>
  <?php if (empty($logs)): ?>
    <p style="color:var(--muted); font-size:0.88rem;">No activity recorded yet.</p>
  <?php else: ?>
    <ul class="activity-list">
      <?php foreach ($logs as $log): ?>
        <li>
          <div class="action"><span class="actor"><?= e($log['full_name'] ?? 'System') ?></span> — <?= e($log['action_taken']) ?></div>
          <div class="time"><?= format_dt($log['timestamp']) ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
