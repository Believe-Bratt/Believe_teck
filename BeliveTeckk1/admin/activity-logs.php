<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/ActivityLogger.php';

// Add export functionality
if (isset($_POST['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date/Time', 'User', 'Action', 'Details', 'IP Address']);
    
    $export_logs = $activityLogger->getActivityLogs($user_id, $action, $start_date, $end_date);
    foreach ($export_logs as $log) {
        $user = $admin->getAdminById($log['user_id']);
        $details = is_string($log['details']) ? $log['details'] : json_encode($log['details']);
        fputcsv($output, [
            $log['created_at'],
            $user['username'] ?? 'Unknown',
            $log['action'],
            $details,
            $log['ip_address']
        ]);
    }
    fclose($output);
    exit();
}

// Initialize database connection
$db = Database::getInstance();

// Initialize Admin and ActivityLogger
$admin = new Admin($db);
$activityLogger = new ActivityLogger($db);

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get activity logs
$logs = $activityLogger->getActivityLogs($user_id, $action, $start_date, $end_date, $per_page, $offset);

// Get total count for pagination
$total_logs = $activityLogger->getActivityLogs($user_id, $action, $start_date, $end_date);
$total_pages = ceil(count($total_logs) / $per_page);

// Get all admins for filter dropdown
$admins = $admin->getAllAdmins();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .main-content {
            padding: 20px 30px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .filter-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .table-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .export-btn {
            background-color: white;
            border: 1px solid #dee2e6;
            padding: 8px 16px;
            border-radius: 4px;
        }
        .filter-btn {
            min-width: 100px;
        }
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('../includes/admin_sidebar.php'); ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="page-header">
                    <h1 class="h2">Activity Logs</h1>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="export" class="btn export-btn">
                            <i class="bi bi-download"></i> Export CSV
                        </button>
                    </form>
                </div>

                <!-- Filters -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">User</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="">All Users</option>
                                <?php foreach ($admins as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user['id']); ?>"
                                                <?php echo isset($_GET['user_id']) && $_GET['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="action" class="form-label">Action</label>
                                <input type="text" class="form-control" id="action" name="action" 
                                       value="<?php echo isset($_GET['action']) ? htmlspecialchars($_GET['action']) : ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2 filter-btn">Filter</button>
                                <a href="activity-logs.php" class="btn btn-secondary filter-btn">Reset</a>
                            </div>
                        </form>
                    </div>

                <!-- Logs Table -->
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                        <td>
                                            <?php
                                            $user = $admin->getAdminById($log['user_id']);
                                            echo htmlspecialchars($user['username'] ?? 'Unknown');
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                        <td>
                                            <?php
                                            if ($log['details']) {
                                                $details = is_string($log['details']) ? $log['details'] : json_encode($log['details'], JSON_PRETTY_PRINT);
                                                echo '<pre class="mb-0" style="max-height: 100px; overflow-y: auto;">';
                                                echo htmlspecialchars($details);
                                                echo '</pre>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No activity logs found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Add Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&user_id=<?php echo htmlspecialchars($user_id); ?>&action=<?php echo htmlspecialchars($action); ?>&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>">Previous</a>
                            </li>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&user_id=<?php echo htmlspecialchars($user_id); ?>&action=<?php echo htmlspecialchars($action); ?>&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&user_id=<?php echo htmlspecialchars($user_id); ?>&action=<?php echo htmlspecialchars($action); ?>&start_date=<?php echo htmlspecialchars($start_date); ?>&end_date=<?php echo htmlspecialchars($end_date); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>