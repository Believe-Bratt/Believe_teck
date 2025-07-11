<?php
// Visitor tracking script
function trackVisitor() {
    // Skip tracking for admin pages
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        return;
    }
    
    require_once __DIR__ . '/../classes/Database.php';
    
    try {
        $db = Database::getInstance();
        
        // Get visitor information
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $page_visited = $_SERVER['REQUEST_URI'];
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        $visit_time = date('Y-m-d H:i:s');
        
        // Insert into visitor_logs table
        $stmt = $db->prepare("
            INSERT INTO visitor_logs 
            (ip_address, user_agent, page_visited, referrer, visit_time) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$ip_address, $user_agent, $page_visited, $referrer, $visit_time]);
    } catch (PDOException $e) {
        // Silently log error without affecting user experience
        error_log("Error tracking visitor: " . $e->getMessage());
    }
}

// Track the current visit
trackVisitor();