<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once '../../config/config.php';

try {
    $db = Database::getInstance();
    
    // Get page slug from request
    $page_slug = $_GET['page_slug'] ?? '';
    if (empty($page_slug)) {
        throw new Exception('Page slug is required');
    }
    
    // Fetch page content
    $stmt = $db->prepare("SELECT * FROM page_contents WHERE page_slug = ?");
    $stmt->execute([$page_slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        throw new Exception('Page not found');
    }
    
    // Return page data as JSON
    header('Content-Type: application/json');
    echo json_encode($page);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
} 