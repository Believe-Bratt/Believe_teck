<?php
require_once  __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once  __DIR__ . '/classes/Job.php';
require_once  __DIR__ . '/classes/JobApplication.php';

$db = Database::getInstance();
$job = new Job($db);
$application = new JobApplication($db);

// Get job ID from URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job details
$job_details = $job->getJobById($job_id);

if (!$job_details) {
    header('Location: careers.php');
    exit;
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $cover_letter = $_POST['cover_letter'] ?? '';
    
    // Handle file upload
    $resume_path = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['pdf', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            $upload_dir = 'uploads/resumes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_path)) {
                $resume_path = $target_path;
            } else {
                $error = 'Failed to upload resume. Please try again.';
            }
        } else {
            $error = 'Invalid file type. Please upload PDF, DOC, or DOCX files only.';
        }
    } else {
        $error = 'Please upload your resume.';
    }
    
    if (empty($error)) {
        try {
            $application_data = [
                'job_id' => $job_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'cover_letter' => $cover_letter,
                'resume_path' => $resume_path
            ];
            
            $application->createApplication($application_data);
            $message = 'Your application has been submitted successfully!';
            
            // Redirect to thank you page after 3 seconds
            header('refresh:3;url=careers.php');
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}

// Set page title for header
$page_title = "Apply for " . htmlspecialchars($job_details['title']) . " - Believe Teckk";
$active_page = "careers";

// Include header
include 'includes/header.php';
?>

<!-- Application Form -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-8">Apply for <?php echo htmlspecialchars($job_details['title']); ?></h1>
            
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-8">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Job Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600"><strong>Location:</strong> <?php echo htmlspecialchars($job_details['location']); ?></p>
                            <p class="text-gray-600"><strong>Type:</strong> <?php echo htmlspecialchars($job_details['type']); ?></p>
                            <?php if ($job_details['salary_range']): ?>
                                <p class="text-gray-600"><strong>Salary Range:</strong> <?php echo htmlspecialchars($job_details['salary_range']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-gray-600"><strong>Department:</strong> <?php echo htmlspecialchars($job_details['career_title']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Requirements</h2>
                    <div class="prose max-w-none">
                        <?php echo nl2br(htmlspecialchars($job_details['requirements'])); ?>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Responsibilities</h2>
                    <div class="prose max-w-none">
                        <?php echo nl2br(htmlspecialchars($job_details['responsibilities'])); ?>
                    </div>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" id="name" name="name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                    
                    <div>
                        <label for="resume" class="block text-sm font-medium text-gray-700">Resume (PDF, DOC, DOCX) *</label>
                        <input type="file" id="resume" name="resume" required accept=".pdf,.doc,.docx"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    </div>
                    
                    <div>
                        <label for="cover_letter" class="block text-sm font-medium text-gray-700">Cover Letter</label>
                        <textarea id="cover_letter" name="cover_letter" rows="6"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 