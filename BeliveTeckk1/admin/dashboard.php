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

// Fetch statistics
$stats = [
    'portfolio_items' => 0,
    'blog_posts' => 0,
    'team_members' => 0,
    'job_applications' => 0,
    'total_visitors' => 0,
    'testimonials' => 0,
    'clients' => 0
];

// Safely fetch counts with error handling
try {
    $stats['portfolio_items'] = $db->query("SELECT COUNT(*) as count FROM portfolio_items")->fetch()['count'];
} catch (PDOException $e) {
    error_log("Error counting portfolio items: " . $e->getMessage());
}

try {
    $stats['blog_posts'] = $db->query("SELECT COUNT(*) as count FROM blog_posts")->fetch()['count'];
} catch (PDOException $e) {
    error_log("Error counting blog posts: " . $e->getMessage());
}

try {
    $stats['team_members'] = $db->query("SELECT COUNT(*) as count FROM team_members")->fetch()['count'];
} catch (PDOException $e) {
    error_log("Error counting team members: " . $e->getMessage());
}

try {
    $stats['job_applications'] = $db->query("SELECT COUNT(*) as count FROM job_applications WHERE status = 'pending'")->fetch()['count'];
} catch (PDOException $e) {
    error_log("Error counting job applications: " . $e->getMessage());
}

try {
    // Check if visitor_logs table exists before querying
    $tableExists = $db->query("SHOW TABLES LIKE 'visitor_logs'")->rowCount() > 0;
    if ($tableExists) {
        $stats['total_visitors'] = $db->query("SELECT COUNT(*) as count FROM visitor_logs")->fetch()['count'];
    }
} catch (PDOException $e) {
    error_log("Error counting visitors: " . $e->getMessage());
}

try {
    $stats['testimonials'] = $db->query("SELECT COUNT(*) as count FROM testimonials")->fetch()['count'];
} catch (PDOException $e) {
    error_log("Error counting testimonials: " . $e->getMessage());
}

try {
    $stats['clients'] = $db->query("SELECT COUNT(*) as count FROM clients")->fetch()['count'];
} catch (PDOException $e) {
    error_log("Error counting clients: " . $e->getMessage());
}

// Get recent activities (combined from different tables)
$recentActivities = [];

// Recent job applications
try {
    $recentApplications = $db->query("
        SELECT 'job_application' as type, ja.id, ja.name, ja.created_at, j.title as details
        FROM job_applications ja 
        JOIN jobs j ON ja.job_id = j.id 
        ORDER BY ja.created_at DESC 
        LIMIT 5
    ")->fetchAll();

    foreach($recentApplications as $item) {
        $recentActivities[] = [
            'type' => 'job_application',
            'id' => $item['id'],
            'name' => $item['name'],
            'details' => 'Applied for ' . $item['details'],
            'created_at' => $item['created_at'],
            'icon' => 'fa-file-alt',
            'color' => 'red'
        ];
    }
} catch (PDOException $e) {
    error_log("Error fetching recent applications: " . $e->getMessage());
}

// Recent blog posts
try {
    $recentPosts = $db->query("
        SELECT 'blog_post' as type, id, title as name, created_at, 'Published new blog post' as details
        FROM blog_posts
        ORDER BY created_at DESC
        LIMIT 3
    ")->fetchAll();

    foreach($recentPosts as $item) {
        $recentActivities[] = [
            'type' => 'blog_post',
            'id' => $item['id'],
            'name' => $item['name'],
            'details' => $item['details'],
            'created_at' => $item['created_at'],
            'icon' => 'fa-blog',
            'color' => 'green'
        ];
    }
} catch (PDOException $e) {
    error_log("Error fetching recent blog posts: " . $e->getMessage());
}

// Recent testimonials
try {
    $recentTestimonials = $db->query("
        SELECT 'testimonial' as type, id, client_name as name, created_at, 'Added new testimonial' as details
        FROM testimonials
        ORDER BY created_at DESC
        LIMIT 3
    ")->fetchAll();

    foreach($recentTestimonials as $item) {
        $recentActivities[] = [
            'type' => 'testimonial',
            'id' => $item['id'],
            'name' => $item['name'],
            'details' => $item['details'],
            'created_at' => $item['created_at'],
            'icon' => 'fa-quote-right',
            'color' => 'blue'
        ];
    }
} catch (PDOException $e) {
    error_log("Error fetching recent testimonials: " . $e->getMessage());
}

// Sort all activities by date
if (!empty($recentActivities)) {
    usort($recentActivities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Limit to 10 most recent
    $recentActivities = array_slice($recentActivities, 0, 10);
}

// Calculate some analytics
$stats['conversion_rate'] = '3.2%'; // Example value
$stats['avg_session_time'] = '4:32'; // Example value
$stats['bounce_rate'] = '42%'; // Example value
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Believe Teckk</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        .dashboard-card {
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .activity-item {
            transition: all 0.2s ease;
        }
        .activity-item:hover {
            background-color: #f9fafb;
        }
        @media (max-width: 768px) {
            .ml-64 {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="ml-64 min-h-screen bg-gray-100 transition-all duration-300 md:ml-64 ml-0">
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Dashboard Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-lg shadow p-2 flex items-center">
                        <i class="fas fa-calendar-alt text-gray-500 mr-2"></i>
                        <span class="text-gray-700"><?php echo date('F d, Y'); ?></span>
                    </div>
                    <a href="profile.php" class="bg-white rounded-lg shadow p-2 flex items-center hover:bg-gray-50 transition">
                        <i class="fas fa-user-circle text-gray-500 mr-2"></i>
                        <span class="text-gray-700">Profile</span>
                    </a>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Portfolio Items -->
                <div class="bg-white rounded-lg shadow p-6 dashboard-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-briefcase text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Portfolio Items</h3>
                            <p class="text-2xl font-semibold"><?php echo $stats['portfolio_items']; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="portfolio.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                            <span>Manage Portfolio</span>
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Blog Posts -->
                <div class="bg-white rounded-lg shadow p-6 dashboard-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-blog text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Blog Posts</h3>
                            <p class="text-2xl font-semibold"><?php echo $stats['blog_posts']; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="blog.php" class="text-green-600 hover:text-green-800 text-sm font-medium flex items-center">
                            <span>Manage Blog</span>
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Team Members -->
                <div class="bg-white rounded-lg shadow p-6 dashboard-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-users text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Team Members</h3>
                            <p class="text-2xl font-semibold"><?php echo $stats['team_members']; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="team.php" class="text-purple-600 hover:text-purple-800 text-sm font-medium flex items-center">
                            <span>Manage Team</span>
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Applications -->
                <div class="bg-white rounded-lg shadow p-6 dashboard-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-file-alt text-red-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Pending Applications</h3>
                            <p class="text-2xl font-semibold"><?php echo $stats['job_applications']; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="applications.php" class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                            <span>View Applications</span>
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Recent Activity</h3>
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
                    </div>
                    
                    <div class="space-y-4">
                        <?php if (empty($recentActivities)): ?>
                            <p class="text-gray-500 text-center py-4">No recent activities found.</p>
                        <?php else: ?>
                            <?php foreach($recentActivities as $activity): ?>
                                <div class="flex items-center justify-between py-3 border-b activity-item">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-<?php echo $activity['color']; ?>-100 rounded-full">
                                            <i class="fas <?php echo $activity['icon']; ?> text-<?php echo $activity['color']; ?>-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium"><?php echo htmlspecialchars($activity['name']); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($activity['details']); ?></p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($activity['created_at'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-6">Quick Actions</h3>
                    <div class="space-y-4">
                        <a href="blog-post.php?action=new" class="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-full">
                                    <i class="fas fa-plus text-blue-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">New Blog Post</p>
                                    <p class="text-sm text-gray-500">Create a new article</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="portfolio.php?action=new" class="block p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-full">
                                    <i class="fas fa-plus text-green-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">New Portfolio Item</p>
                                    <p class="text-sm text-gray-500">Add project to showcase</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="testimonials.php?action=new" class="block p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                            <div class="flex items-center">
                                <div class="p-2 bg-purple-100 rounded-full">
                                    <i class="fas fa-plus text-purple-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">New Testimonial</p>
                                    <p class="text-sm text-gray-500">Add client feedback</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="jobs.php?action=new" class="block p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
                            <div class="flex items-center">
                                <div class="p-2 bg-red-100 rounded-full">
                                    <i class="fas fa-plus text-red-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium">New Job Posting</p>
                                    <p class="text-sm text-gray-500">Create job opportunity</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Analytics Overview -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-6">Analytics Overview</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">
                                <?php echo $stats['total_visitors'] ?? 0; ?>
                            </div>
                            <div class="text-gray-500 text-sm">Total Visitors</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">
                                <?php echo $stats['conversion_rate'] ?? '0%'; ?>
                            </div>
                            <div class="text-gray-500 text-sm">Conversion Rate</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">
                                <?php echo $stats['avg_session_time'] ?? '0:00'; ?>
                            </div>
                            <div class="text-gray-500 text-sm">Avg. Session Time</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">
                                <?php echo $stats['bounce_rate'] ?? '0%'; ?>
                            </div>
                            <div class="text-gray-500 text-sm">Bounce Rate</div>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <a href="analytics.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center justify-center">
                            <span>View Detailed Analytics</span>
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Traffic Chart -->
                <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold mb-6">Website Traffic</h3>
                    <canvas id="trafficChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Traffic Chart
        const trafficCtx = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(trafficCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trafficLabels); ?>,
                datasets: [
                    {
                        label: 'Total Visitors',
                        data: <?php echo json_encode($trafficData); ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Unique Visitors',
                        data: <?php echo json_encode($uniqueVisitorsData); ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleColor: '#fff',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyColor: '#fff',
                        bodyFont: {
                            size: 13
                        },
                        displayColors: true,
                        usePointStyle: true
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(200, 200, 200, 0.15)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            color: 'rgba(200, 200, 200, 0.15)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.ml-64');
            
            if (sidebarToggle && sidebar && mainContent) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('translate-x-0');
                    sidebar.classList.toggle('-translate-x-full');
                    mainContent.classList.toggle('ml-0');
                    mainContent.classList.toggle('ml-64');
                });
            }
        });
    </script>
</body>
</html>