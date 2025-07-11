<?php
require_once 'Database.php';

class ActivityLogger {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Log an activity
     * @param int $userId User ID performing the action
     * @param string $action Description of the action
     * @param array|string|null $details Additional details about the action
     * @return bool Whether logging was successful
     */
    public function log($userId, $action, $details = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, action, details, ip_address, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $ipAddress = $this->getClientIP();
            $detailsJson = $details ? json_encode($details) : null;
            
            return $stmt->execute([$userId, $action, $detailsJson, $ipAddress]);
        } catch (PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get activity logs with optional filtering
     * @param array $filters Associative array of filters
     * @param int $limit Number of records to return
     * @param int $offset Starting point
     * @return array Activity logs
     */
    public function getActivityLogs($filters = [], $limit = 50, $offset = 0) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['user_id'])) {
                $where[] = "user_id = ?";
                $params[] = $filters['user_id'];
            }

            if (!empty($filters['action'])) {
                $where[] = "action LIKE ?";
                $params[] = "%{$filters['action']}%";
            }

            if (!empty($filters['start_date'])) {
                $where[] = "created_at >= ?";
                $params[] = $filters['start_date'];
            }

            if (!empty($filters['end_date'])) {
                $where[] = "created_at <= ?";
                $params[] = $filters['end_date'];
            }

            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $sql = "
                SELECT * FROM activity_logs 
                {$whereClause}
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching activity logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client IP address
     * @return string IP address
     */
    private function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
} 