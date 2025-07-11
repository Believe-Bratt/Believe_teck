<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once('../config/config.php');
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ .'/../classes/Admin.php';
require_once __DIR__ .'/../classes/Job.php';

$db = Database::getInstance();
$admin = new Admin($db);
$job = new Job($db);

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $data = [
                    'career_id' => $_POST['career_id'],
                    'title' => $_POST['title'],
                    'location' => $_POST['location'],
                    'type' => $_POST['type'],
                    'description' => $_POST['description'],
                    'requirements' => $_POST['requirements'],
                    'responsibilities' => $_POST['responsibilities'],
                    'salary_range' => $_POST['salary_range'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if ($job->createJob($data)) {
                    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Job created successfully!</div>';
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error creating job.</div>';
                }
                break;
                
            case 'update':
                $data = [
                    'career_id' => $_POST['career_id'],
                    'title' => $_POST['title'],
                    'location' => $_POST['location'],
                    'type' => $_POST['type'],
                    'description' => $_POST['description'],
                    'requirements' => $_POST['requirements'],
                    'responsibilities' => $_POST['responsibilities'],
                    'salary_range' => $_POST['salary_range'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if ($job->updateJob($_POST['id'], $data)) {
                    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Job updated successfully!</div>';
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error updating job.</div>';
                }
                break;
                
            case 'delete':
                if ($job->deleteJob($_POST['id'])) {
                    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Job deleted successfully!</div>';
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error deleting job.</div>';
                }
                break;
                
            case 'toggle_active':
                if ($job->toggleActive($_POST['id'])) {
                    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Job status updated successfully!</div>';
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Error updating job status.</div>';
                }
                break;
        }
    }
}

// Fetch all jobs and careers
$jobs = $job->getAllJobs();
$careers = $job->getAllCareers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Careers - Believe Teckk</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Manage Careers</h1>
            <button onclick="document.getElementById('addJobModal').classList.remove('hidden')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-plus mr-2"></i>Add New Job
            </button>
        </div>

        <?php echo $message; ?>

        <!-- Jobs Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Career</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($job['title']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($job['career_title']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($job['location']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($job['type']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $job['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editJob(<?php echo htmlspecialchars(json_encode($job)); ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="toggleJobStatus(<?php echo $job['id']; ?>)" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                <i class="fas fa-toggle-on"></i>
                            </button>
                            <button onclick="deleteJob(<?php echo $job['id']; ?>)" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Job Modal -->
    <div id="addJobModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium" id="modalTitle">Add New Job</h3>
                <button onclick="document.getElementById('addJobModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="jobForm" method="POST" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="jobId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Career</label>
                    <select name="career_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php foreach ($careers as $career): ?>
                        <option value="<?php echo $career['id']; ?>"><?php echo htmlspecialchars($career['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="full-time">Full Time</option>
                        <option value="part-time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="internship">Internship</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" required rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Requirements</label>
                    <textarea name="requirements" id="requirements" required rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Responsibilities</label>
                    <textarea name="responsibilities" id="responsibilities" required rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Salary Range</label>
                    <input type="text" name="salary_range" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="isActive" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="isActive" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('addJobModal').classList.add('hidden')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editJob(job) {
            document.getElementById('modalTitle').textContent = 'Edit Job';
            document.getElementById('formAction').value = 'update';
            document.getElementById('jobId').value = job.id;
            document.getElementById('jobForm').elements['career_id'].value = job.career_id;
            document.getElementById('jobForm').elements['title'].value = job.title;
            document.getElementById('jobForm').elements['location'].value = job.location;
            document.getElementById('jobForm').elements['type'].value = job.type;
            document.getElementById('jobForm').elements['salary_range'].value = job.salary_range;
            document.getElementById('isActive').checked = job.is_active == 1;

            // Set textarea content
            document.getElementById('description').value = job.description || '';
            document.getElementById('requirements').value = job.requirements || '';
            document.getElementById('responsibilities').value = job.responsibilities || '';

            document.getElementById('addJobModal').classList.remove('hidden');
        }

        function toggleJobStatus(id) {
            if (confirm('Are you sure you want to toggle this job\'s status?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteJob(id) {
            if (confirm('Are you sure you want to delete this job?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 