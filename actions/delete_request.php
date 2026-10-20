<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$mrModel = new MaintenanceRequest($pdo);

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $mrModel->delete($id);
}

header('Location: ../index.php?msg=deleted');
exit;
