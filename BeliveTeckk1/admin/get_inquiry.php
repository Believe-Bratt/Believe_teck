<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ .  '/../classes/Admin.php';
require_once __DIR__ .'includes/auth_check.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Inquiry ID is required']);
    exit;
}

$db = Database::getInstance();
$admin = new Admin($db);

$inquiry = $admin->getInquiry($_GET['id']);

if (!$inquiry) {
    http_response_code(404);
    echo json_encode(['error' => 'Inquiry not found']);
    exit;
}

echo json_encode($inquiry); 