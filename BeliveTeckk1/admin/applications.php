<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ .'/../classes/Admin.php';
require_once __DIR__ .'/../classes/Job.php';
require_once __DIR__ .'/../classes/JobApplication.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = Database::getInstance();
$admin = new Admin($db);
$job = new Job($db);
$application = new JobApplication($db);

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Invalid request.</div>';
    } else if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'update_status':
                    // Validate status
                    $valid_statuses = ['pending', 'reviewed', 'shortlisted', 'rejected'];
                    $status = trim($_POST['status'] ?? '');
                    
                    if (!in_array($status, $valid_statuses)) {
                        throw new Exception("Invalid status value");
                    }
                    
                    if ($application->updateStatus($_POST['id'], $status)) {
                        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">Application status updated successfully!</div>';
                        // Log the activity
                        $admin->logActivity($_SESSION['admin_id'], 'update_application_status', "Updated application ID: {$_POST['id']} to status: {$status}");
                    } else {
                        throw new Exception("Failed to update application status");
                    }
                    break;
                    
                case 'delete':
                    if ($application->deleteApplication($_POST['id'])) {
                        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">Application deleted successfully!</div>';
                        // Log the activity
                        $admin->logActivity($_SESSION['admin_id'], 'delete_application', "Deleted application ID: {$_POST['id']}");
                    } else {
                        throw new Exception("Failed to delete application");
                    }
                    break;
                    
                default:
                    throw new Exception("Invalid action");
            }
        } catch (Exception $e) {
            error_log("Application management error: " . $e->getMessage());
            $error = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Fetch all applications with job details
try {
    $applications = $application->getAllApplications();
} catch (Exception $e) {
    error_log("Error fetching applications: " . $e->getMessage());
    $error = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Error loading applications. Please try again later.</div>';
    $applications = [];
}

// Set page title
$page_title = "Manage Applications - Believe Teckk";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage job applications for Believe Teckk">
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preload" href="https://cdn.tailwindcss.com" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>
    
    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Manage Applications</h1>
            <div class="flex space-x-4">
                <button onclick="exportApplications()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>

        <?php echo $error; ?>
        <?php echo $message; ?>

        <!-- Applications Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" role="table" aria-label="Applications">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No applications found.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['email']); ?></div>
                                    <?php if ($app['phone']): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['job_type']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch ($app['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'reviewed':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'shortlisted':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                        }
                                        ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewApplication(<?php echo htmlspecialchars(json_encode($app)); ?>)" 
                                            class="text-blue-600 hover:text-blue-900 mr-3" 
                                            aria-label="View application details">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                    <button onclick="updateStatus(<?php echo $app['id']; ?>)" 
                                            class="text-yellow-600 hover:text-yellow-900 mr-3"
                                            aria-label="Update application status">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </button>
                                    <button onclick="deleteApplication(<?php echo $app['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900"
                                            aria-label="Delete application">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View Application Modal -->
    <div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-medium">Application Details</h3>
                <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-500" aria-label="Close modal">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div id="applicationDetails" class="space-y-4">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" role="dialog" aria-labelledby="statusModalTitle" aria-modal="true">
        <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 id="statusModalTitle" class="text-lg font-medium">Update Application Status</h3>
                <button onclick="closeModal('statusModal')" class="text-gray-400 hover:text-gray-500" aria-label="Close modal">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <form id="statusForm" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" id="applicationId">
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('statusModal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Close modal function
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            // Remove focus from modal
            document.activeElement.blur();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.fixed').forEach(modal => {
                    modal.classList.add('hidden');
                });
            }
        });

        function viewApplication(application) {
            const details = document.getElementById('applicationDetails');
            details.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium">Applicant Information</h4>
                        <p><strong>Name:</strong> ${application.name}</p>
                        <p><strong>Email:</strong> ${application.email}</p>
                        <p><strong>Phone:</strong> ${application.phone || 'N/A'}</p>
                    </div>
                    <div>
                        <h4 class="font-medium">Job Information</h4>
                        <p><strong>Position:</strong> ${application.job_title}</p>
                        <p><strong>Type:</strong> ${application.job_type}</p>
                        <p><strong>Applied:</strong> ${new Date(application.created_at).toLocaleDateString()}</p>
                    </div>
                    <div class="col-span-2">
                        <h4 class="font-medium">Cover Letter</h4>
                        <div class="bg-gray-50 p-4 rounded">${application.cover_letter || 'No cover letter provided'}</div>
                    </div>
                    <div class="col-span-2">
                        <h4 class="font-medium">Resume</h4>
                        ${application.resume_path ? 
                            `<a href="../${application.resume_path}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-file-pdf mr-2"></i>View Resume
                            </a>` : 
                            '<p class="text-gray-500">No resume uploaded</p>'
                        }
                    </div>
                </div>
            `;
            document.getElementById('viewModal').classList.remove('hidden');
            // Focus on the modal
            document.getElementById('viewModal').focus();
        }

        function updateStatus(id) {
            document.getElementById('applicationId').value = id;
            document.getElementById('statusModal').classList.remove('hidden');
            // Focus on the modal
            document.getElementById('statusModal').focus();
        }

        function deleteApplication(id) {
            if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportApplications() {
            window.location.href = 'export_applications.php';
        }
    </script>
</body>
</html> 