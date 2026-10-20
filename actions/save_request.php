<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$mrModel = new MaintenanceRequest($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$description   = trim($_POST['description'] ?? '');
$roomId        = (int) ($_POST['room_id'] ?? 0);
$categoryId    = (int) ($_POST['category_id'] ?? 0);
$priorityLevel = $_POST['priority_level'] ?? 'Medium';
$requesterId   = (int) ($_POST['requester_id'] ?? 0);
$requestId     = (int) ($_POST['request_id'] ?? 0);
$isUpdate      = $requestId > 0;

if ($description === '' || $roomId <= 0 || $categoryId <= 0 || $requesterId <= 0) {
    $target = $isUpdate ? "../edit.php?id={$requestId}" : '../add.php';
    header('Location: ' . $target . '?error=' . urlencode('Please fill in all required fields.'));
    exit;
}

$data = [
    'requester_id'   => $requesterId,
    'room_id'        => $roomId,
    'category_id'    => $categoryId,
    'description'    => $description,
    'priority_level' => $priorityLevel,
    'status'         => $_POST['status'] ?? 'Pending',
];

if ($isUpdate) {
    $mrModel->update($requestId, $data);
    $mrModel->logActivity(
        $requestId,
        $currentUser['user_id'] ?? $requesterId,
        'Updated request — status set to "' . $data['status'] . '", priority "' . $data['priority_level'] . '"'
    );

    $technicianId = (int) ($_POST['technician_id'] ?? 0);
    if ($technicianId > 0) {
        $mrModel->assignTechnician($requestId, $technicianId);
        $mrModel->logActivity($requestId, $currentUser['user_id'] ?? $requesterId, 'Technician assignment updated');
    }

    header('Location: ../index.php?msg=updated');
    exit;
}

$newId = $mrModel->create($data);
$mrModel->logActivity($newId, $requesterId, 'Request submitted');

header('Location: ../index.php?msg=added');
exit;
