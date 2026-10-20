<?php
require_once __DIR__ . '/includes/bootstrap.php';

$rooms      = $lookup->getRooms();
$categories = $lookup->getCategories();
$requesters = $lookup->getUsers();

$pageTitle = 'New Request';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1>Submit Maintenance Request</h1>
    <p>Report an issue with a room, fixture, or system on campus.</p>
  </div>
  <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
</div>

<?= flash_message() ?>

<div class="panel">
  <div class="panel-title"><span class="bar"></span> Request Details</div>
  <form method="post" action="actions/save_request.php">
    <div class="form-grid">
      <div class="field">
        <label>Requested By</label>
        <select name="requester_id" required>
          <?php foreach ($requesters as $u): ?>
            <option value="<?= (int)$u['user_id'] ?>" <?= (int)$u['user_id'] === (int)($currentUser['user_id'] ?? 0) ? 'selected' : '' ?>>
              <?= e($u['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Category</label>
        <select name="category_id" required>
          <option value="">Select category</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['category_id'] ?>"><?= e($c['category_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Building</label>
        <select id="building_id" name="building_id_ui">
          <option value="">Filter by building</option>
          <?php foreach ($lookup->getBuildings() as $b): ?>
            <option value="<?= (int)$b['building_id'] ?>"><?= e($b['building_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Room</label>
        <select id="room_id" name="room_id" required>
          <option value="">Select room</option>
          <?php foreach ($rooms as $r): ?>
            <option value="<?= (int)$r['room_id'] ?>" data-building="<?= (int)$r['building_id'] ?>">
              <?= e($r['building_name'] . ' — ' . $r['room_number'] . ' (' . $r['floor_level'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Priority Level</label>
        <select name="priority_level" required>
          <option value="Low">Low</option>
          <option value="Medium" selected>Medium</option>
          <option value="High">High</option>
          <option value="Urgent">Urgent</option>
        </select>
      </div>

      <div class="field full">
        <label>Description</label>
        <textarea name="description" placeholder="Describe the issue in detail (what's broken, since when, any safety concerns)..." required></textarea>
      </div>
    </div>

    <div class="form-actions">
      <a href="index.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-gold"><i class="fa-solid fa-paper-plane"></i> Submit Request</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
