<?php
/**
 * bootstrap.php
 * Included at the top of every page: starts the session and loads
 * the config, PDO connection, OOP classes, and helper functions.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/MaintenanceRequest.php';
require_once __DIR__ . '/../classes/LookupData.php';
require_once __DIR__ . '/functions.php';

$pdo = Database::getInstance()->getConnection();

$lookup = new LookupData($pdo);
$allUsers = $lookup->getUsers();

if (isset($_POST['switch_user_id'])) {
    $_SESSION['current_user_id'] = (int) $_POST['switch_user_id'];
    $redirectBack = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header('Location: ' . $redirectBack);
    exit;
}

if (!isset($_SESSION['current_user_id']) && !empty($allUsers)) {
    $_SESSION['current_user_id'] = (int) $allUsers[0]['user_id'];
}

$currentUser = null;
foreach ($allUsers as $u) {
    if ((int) $u['user_id'] === (int) ($_SESSION['current_user_id'] ?? 0)) {
        $currentUser = $u;
        break;
    }
}
