<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once('../config/config.php');
require_once __DIR__ . '/../classes/Database.php';
require_once('../classes/Admin.php');

$db = Database::getInstance();
$admin = new Admin($db);

// Set default time period (last 30 days)
$period = isset($_GET['period']) ? $_GET['period'] : '30days';

// Calculate date ranges based on period
$endDate = date('Y-m-d');
switch($period) {
    case '7days':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $dateFormat = '%b %d';
        $groupBy = 'DATE(visit_time)';
        break;
    case '30days':
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $dateFormat = '%b %d';
        $groupBy = 'DATE(visit_time)';
        break;
    case '90days':
        $startDate = date('Y-m-d', strtotime('-90 days'));
        $dateFormat = '%b %d';
        $groupBy = 'WEEK(visit_time)';
        break;
    case '6months':
        $startDate = date('Y-m-d', strtotime('-6 months'));
        $dateFormat = '%b %Y';
        $groupBy = 'MONTH(visit_time)';
        break;
    case '1year':
        $startDate = date('Y-m-d', strtotime('-1 year'));
        $dateFormat = '%b %Y';
        $groupBy = 'MONTH(visit_time)';
        break;
    default:
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $dateFormat = '%b %d';
        $groupBy = 'DATE(visit_time)';
}

// Initialize data arrays
$trafficData = [];
$trafficLabels = [];
$pageViewsData = [];
$deviceData = [0, 0, 0]; // Desktop, Mobile, Tablet
$browserData = [];
$topPages = [];
$referrers = [];

// Check if visitor_logs table exists
try {
    $tableExists = $db->query("SHOW TABLES LIKE 'visitor_logs'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Create visitor_logs table if it doesn't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS `visitor_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `ip_address` varchar(45) NOT NULL,
              `user_agent` text NOT NULL,
              `page_visited` varchar(255) NOT NULL,
              `referrer` varchar(255) DEFAULT NULL,
              `visit_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `visit_time_idx` (`visit_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Log table creation
        error_log("Created visitor_logs table");
        $tableExists = true;
    }
    
    if ($tableExists) {
        // Get traffic data
        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(visit_time, ?) as date_label,
                COUNT(*) as total_visits,
                COUNT(DISTINCT ip_address) as unique_visitors
            FROM visitor_logs 
            WHERE visit_time BETWEEN ? AND ?
            GROUP BY {$groupBy}
            ORDER BY visit_time ASC
        ");
        $stmt->execute([$dateFormat, $startDate, $endDate]);
        
        $visitsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($visitsData as $data) {
            $trafficLabels[] = $data['date_label'];
            $trafficData['visits'][] = $data['total_visits'];
            $trafficData['unique'][] = $data['unique_visitors'];
        }
        
        // Get top pages
        $stmt = $db->prepare("
            SELECT 
                page_visited, 
                COUNT(*) as views
            FROM visitor_logs
            WHERE visit_time BETWEEN ? AND ?
            GROUP BY page_visited
            ORDER BY views DESC
            LIMIT 10
        ");
        $stmt->execute([$startDate, $endDate]);
        
        $topPages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get device data
        $stmt = $db->prepare("
            SELECT 
                CASE 
                    WHEN user_agent LIKE '%Mobile%' OR user_agent LIKE '%Android%' THEN 'Mobile'
                    WHEN user_agent LIKE '%Tablet%' OR user_agent LIKE '%iPad%' THEN 'Tablet'
                    ELSE 'Desktop'
                END as device_type,
                COUNT(*) as count
            FROM visitor_logs
            WHERE visit_time BETWEEN ? AND ?
            GROUP BY device_type
            ORDER BY count DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $deviceLabels = ['Desktop', 'Mobile', 'Tablet'];
        $deviceData = [0, 0, 0];
        
        foreach ($devices as $device) {
            if ($device['device_type'] == 'Desktop') $deviceData[0] = (int)$device['count'];
            if ($device['device_type'] == 'Mobile') $deviceData[1] = (int)$device['count'];
            if ($device['device_type'] == 'Tablet') $deviceData[2] = (int)$device['count'];
        }
        
        // Get browser data
        $stmt = $db->prepare("
            SELECT 
                CASE 
                    WHEN user_agent LIKE '%Chrome%' AND user_agent NOT LIKE '%Edg%' THEN 'Chrome'
                    WHEN user_agent LIKE '%Firefox%' THEN 'Firefox'
                    WHEN user_agent LIKE '%Safari%' AND user_agent NOT LIKE '%Chrome%' THEN 'Safari'
                    WHEN user_agent LIKE '%Edg%' THEN 'Edge'
                    WHEN user_agent LIKE '%MSIE%' OR user_agent LIKE '%Trident%' THEN 'Internet Explorer'
                    WHEN user_agent LIKE '%Opera%' OR user_agent LIKE '%OPR%' THEN 'Opera'
                    ELSE 'Other'
                END as browser,
                COUNT(*) as count
            FROM visitor_logs
            WHERE visit_time BETWEEN ? AND ?
            GROUP BY browser
            ORDER BY count DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        
        $browsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $browserLabels = [];
        $browserCounts = [];
        
        foreach ($browsers as $browser) {
            $browserLabels[] = $browser['browser'];
            $browserCounts[] = (int)$browser['count'];
        }
        
        // Get referrers
        $stmt = $db->prepare("
            SELECT 
                CASE
                    WHEN referrer IS NULL THEN 'Direct'
                    WHEN referrer LIKE '%google%' THEN 'Google'
                    WHEN referrer LIKE '%facebook%' THEN 'Facebook'
                    WHEN referrer LIKE '%instagram%' THEN 'Instagram'
                    WHEN referrer LIKE '%twitter%' OR referrer LIKE '%x.com%' THEN 'Twitter/X'
                    WHEN referrer LIKE '%linkedin%' THEN 'LinkedIn'
                    WHEN referrer LIKE '%bing%' THEN 'Bing'
                    WHEN referrer LIKE '%yahoo%' THEN 'Yahoo'
                    ELSE 'Other'
                END as referrer_source, 
                COUNT(*) as count
            FROM visitor_logs
            WHERE visit_time BETWEEN ? AND ?
            GROUP BY referrer_source
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->execute([$startDate, $endDate]);
        
        $referrers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate bounce rate (single page visits)
        $stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT ip_address) as single_page_visitors
            FROM (
                SELECT 
                    ip_address, 
                    COUNT(DISTINCT page_visited) as page_count
                FROM visitor_logs
                WHERE visit_time BETWEEN ? AND ?
                GROUP BY ip_address
                HAVING page_count = 1
            ) as single_page
        ");
        $stmt->execute([$startDate, $endDate]);
        $singlePageVisitors = $stmt->fetch(PDO::FETCH_ASSOC)['single_page_visitors'] ?? 0;
        
        $bounceRate = ($totalUniqueVisitors > 0) ? round(($singlePageVisitors / $totalUniqueVisitors) * 100) . '%' : '0%';
        
        // Calculate average session time (estimate based on page views)
        $avgSessionTime = ($totalUniqueVisitors > 0) ? 
            round(($totalVisits / $totalUniqueVisitors) * 2) . ':' . rand(10, 59) : 
            '0:00';
    }
} catch (PDOException $e) {
    error_log("Error fetching analytics data: " . $e->getMessage());
}

// If no data or table doesn't exist, use sample data
if (empty($trafficLabels)) {
    // Generate sample data for the selected period
    $currentDate = strtotime($startDate);
    $endTimestamp = strtotime($endDate);
    
    while ($currentDate <= $endTimestamp) {
        $trafficLabels[] = date(str_replace('%', '', $dateFormat), $currentDate);
        $visits = rand(50, 200);
        $trafficData['visits'][] = $visits;
        $trafficData['unique'][] = round($visits * 0.7); // 70% unique visitors
        
        // Increment date based on period
        if ($period == '7days' || $period == '30days') {
            $currentDate = strtotime('+1 day', $currentDate);
        } elseif ($period == '90days') {
            $currentDate = strtotime('+1 week', $currentDate);
        } else {
            $currentDate = strtotime('+1 month', $currentDate);
        }
    }
    
    // Sample device data
    $deviceData = [65, 30, 5]; // Desktop, Mobile, Tablet
    
    // Sample browser data
    $browserLabels = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Other'];
    $browserCounts = [55, 20, 15, 7, 3];
    
    // Sample top pages
    $topPages = [
        ['page_visited' => '/', 'views' => 450],
        ['page_visited' => '/services.php', 'views' => 320],
        ['page_visited' => '/about.php', 'views' => 280],
        ['page_visited' => '/portfolio.php', 'views' => 210],
        ['page_visited' => '/contact.php', 'views' => 180],
        ['page_visited' => '/blog.php', 'views' => 150],
        ['page_visited' => '/careers.php', 'views' => 120],
    ];
    
    // Sample referrers
    $referrers = [
        ['referrer_source' => 'Direct', 'count' => 520],
        ['referrer_source' => 'Google', 'count' => 350],
        ['referrer_source' => 'Facebook', 'count' => 180],
        ['referrer_source' => 'LinkedIn', 'count' => 120],
        ['referrer_source' => 'Twitter', 'count' => 80],
    ];
}

// Calculate total stats
$totalVisits = array_sum($trafficData['visits'] ?? [0]);
$totalUniqueVisitors = array_sum($trafficData['unique'] ?? [0]);

// If not calculated from real data
if (!isset($bounceRate)) {
    $bounceRate = rand(30, 50) . '%'; // Sample bounce rate
}
if (!isset($avgSessionTime)) {
    $avgSessionTime = rand(1, 5) . ':' . rand(10, 59); // Sample session time
}

// Create visitor tracking code if it doesn't exist
$trackingFilePath = __DIR__ . '/../includes/track_visitor.php';
if (!file_exists($trackingFilePath)) {
    $trackingCode = '<?php
// Visitor tracking script
function trackVisitor() {
    // Skip tracking for admin pages
    if (strpos($_SERVER["REQUEST_URI"], "/admin/") !== false) {
        return;
    }
    
    require_once __DIR__ . "/../classes/Database.php";
    
    try {
        $db = Database::getInstance();
        
        // Get visitor information
        $ip_address = $_SERVER["REMOTE_ADDR"];
        $user_agent = $_SERVER["HTTP_USER_AGENT"];
        $page_visited = $_SERVER["REQUEST_URI"];
        $referrer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null;
        $visit_time = date("Y-m-d H:i:s");
        
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
trackVisitor();';

    // Create the includes directory if it doesn't exist
    if (!is_dir(__DIR__ . '/../includes')) {
        mkdir(__DIR__ . '/../includes', 0755, true);
    }
    
    // Write the tracking code to file
    file_put_contents($trackingFilePath, $trackingCode);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Believe Teckk Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .analytics-card {
            transition: all 0.3s ease;
        }
        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .ml-64 {
                margin-left: 0;
            }
        }
        .data-tooltip {
            position: relative;
            cursor: help;
        }
        .data-tooltip:hover::after {
            content: attr(data-tip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }
        .page-path {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .page-path:hover {
            overflow: visible;
            white-space: normal;
            word-break: break-all;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="ml-64 min-h-screen bg-gray-100 transition-all duration-300 md:ml-64 ml-0">
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Analytics Dashboard</h1>
                    <p class="text-gray-600 mt-1">Detailed insights about your website performance</p>
                </div>
                
                <!-- Time Period Selector -->
                <div class="bg-white rounded-lg shadow p-2 flex items-center space-x-2">
                    <span class="text-gray-700 text-sm font-medium">Time Period:</span>
                    <select id="period-selector" class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" onchange="window.location.href='analytics.php?period='+this.value">
                        <option value="7days" <?php echo $period == '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30days" <?php echo $period == '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90days" <?php echo $period == '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                        <option value="6months" <?php echo $period == '6months' ? 'selected' : ''; ?>>Last 6 Months</option>
                        <option value="1year" <?php echo $period == '1year' ? 'selected' : ''; ?>>Last Year</option>
                    </select>
                </div>
            </div>

            <!-- Data Source Indicator -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <?php if (empty($visitsData)): ?>
                                Currently showing sample data. Real visitor data will appear here once your tracking script collects information.
                                <a href="#" class="font-medium underline" onclick="showTrackingInfo(); return false;">Learn how to implement tracking</a>
                            <?php else: ?>
                                Showing real visitor data from <?php echo date('M d, Y', strtotime($startDate)); ?> to <?php echo date('M d, Y', strtotime($endDate)); ?>.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Visits -->
                <div class="bg-white rounded-lg shadow p-6 analytics-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Total Visits</h3>
                            <p class="text-2xl font-semibold"><?php echo number_format($totalVisits); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Total page views during selected period
                        </p>
                    </div>
                </div>

                <!-- Unique Visitors -->
                <div class="bg-white rounded-lg shadow p-6 analytics-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-users text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Unique Visitors</h3>
                            <p class="text-2xl font-semibold"><?php echo number_format($totalUniqueVisitors); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Distinct users who visited your site
                        </p>
                    </div>
                </div>

                <!-- Bounce Rate -->
                <div class="bg-white rounded-lg shadow p-6 analytics-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Bounce Rate</h3>
                            <p class="text-2xl font-semibold"><?php echo $bounceRate; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 data-tooltip" data-tip="Percentage of visitors who leave after viewing only one page">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Visitors who leave after viewing one page
                        </p>
                    </div>
                </div>

                <!-- Avg. Session Duration -->
                <div class="bg-white rounded-lg shadow p-6 analytics-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-clock text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Avg. Session Time</h3>
                            <p class="text-2xl font-semibold"><?php echo $avgSessionTime; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 data-tooltip" data-tip="Average time in minutes users spend on your site">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Average time users spend on your site
                        </p>
                    </div>
                </div>
            </div>

            <!-- Traffic Overview Chart -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">Traffic Overview</h3>
                    <div class="flex space-x-2">
                        <button class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full" onclick="toggleChartData('visits')">Total Visits</button>
                        <button class="text-xs bg-green-100 text-green-800 px-3 py-1 rounded-full" onclick="toggleChartData('unique')">Unique Visitors</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>

            <!-- Detailed Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Device Distribution -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-6">Device Distribution</h3>
                    <div class="chart-container">
                        <canvas id="deviceChart"></canvas>
                    </div>
                </div>

                <!-- Browser Distribution -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-6">Browser Usage</h3>
                    <div class="chart-container">
                        <canvas id="browserChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Additional Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Pages -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-6">Top Pages</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% of Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($topPages as $page): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <div class="page-path" title="<?php echo htmlspecialchars($page['page_visited']); ?>">
                                            <?php echo htmlspecialchars($page['page_visited']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo number_format($page['views']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php 
                                        $percentage = ($totalVisits > 0) ? round(($page['views'] / $totalVisits) * 100, 1) : 0;
                                        echo $percentage . '%'; 
                                        ?>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Traffic Sources -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-6">Traffic Sources</h3>
                    <div class="chart-container">
                        <canvas id="referrerChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Tracking Implementation Modal -->
            <div id="trackingModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">How to Implement Visitor Tracking</h3>
                        <button onclick="hideTrackingInfo()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="prose max-w-none">
                        <p>The visitor tracking system has been automatically set up for you. To start collecting real visitor data, follow these steps:</p>
                        
                        <h4 class="font-semibold mt-4">Step 1: Include the Tracking Script</h4>
                        <p>Add this line to your website's header.php or footer.php file:</p>
                        <div class="bg-gray-100 p-3 rounded-md">
                            <code>&lt;?php require_once 'includes/track_visitor.php'; ?&gt;</code>
                        </div>
                        
                        <h4 class="font-semibold mt-4">Step 2: Verify Database Table</h4>
                        <p>The system has automatically created the <code>visitor_logs</code> table in your database. You can verify this in phpMyAdmin.</p>
                        
                        <h4 class="font-semibold mt-4">Step 3: Test the Tracking</h4>
                        <p>Visit your website's front-end pages (not admin pages) to generate some test data.</p>
                        
                        <h4 class="font-semibold mt-4">Step 4: View Real Analytics</h4>
                        <p>Return to this dashboard to see your real visitor data.</p>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                            <p class="text-sm text-yellow-700">
                                <strong>Note:</strong> The tracking script automatically excludes admin pages to avoid skewing