<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['page_slug'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Page slug is required']);
    exit();
}

$db = getDBConnection();
$page_slug = $_GET['page_slug'];

try {
    $stmt = $db->prepare("SELECT * FROM page_contents WHERE page_slug = ?");
    $stmt->execute([$page_slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($page) {
        echo json_encode($page);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Page not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
} 