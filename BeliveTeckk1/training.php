<?php
require_once  __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once  __DIR__ . '/classes/Admin.php';

// Set security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:;");

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

if ($ip === 'UNKNOWN') {
    error_log("Could not determine user IP address.");
}

$db = Database::getInstance();
$admin = new Admin($db);

// Handle training registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid form submission. Please try again.";
    } else {
        try {
            // Validate and sanitize inputs
            $program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            // Validate required fields
            if (!$program_id || !$name || !$email || !$phone) {
                throw new Exception("Please fill in all required fields.");
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Please enter a valid email address.");
            }

            // Validate phone number format (basic validation)
            if (!preg_match('/^[0-9+\s\-\(\)]{10,}$/', $phone)) {
                throw new Exception("Please enter a valid phone number.");
            }

            

            // Add registration
            if ($admin->addTrainingRegistration($program_id, $name, $email, $phone, $message)) {
                $success_message = "Registration submitted successfully! We'll contact you shortly.";
                // Log successful registration
                error_log("Training registration successful: $email from IP: $ip");
                
                // Clear form data after successful submission
                $_POST = array();
            } else {
                throw new Exception("There was an error submitting your registration. Please try again.");
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            error_log("Training registration error: " . $e->getMessage() . " from IP: " . $_SERVER['REMOTE_ADDR']);
        }
    }
}

// Fetch training programs with error handling
try {
    $training_programs = $admin->getTrainingPrograms();
} catch (Exception $e) {
    error_log("Error fetching training programs: " . $e->getMessage());
    $training_programs = [];
}

// Set page title for header
$pageTitle = "Training Programs - Believe Teckk";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="Professional training programs offered by Believe Teckk. Enhance your skills with our comprehensive courses.">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
    
    <!-- Stylesheets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <div class="bg-blue-600 text-white py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold mb-4">Training Programs</h1>
            <p class="text-xl">Enhance your skills with our professional training programs</p>
        </div>
    </div>

    <!-- Training Programs Section -->
    <div class="container mx-auto px-4 py-12">
        <?php if (empty($training_programs)): ?>
            <div class="text-center py-8">
                <p class="text-gray-600">No training programs available at the moment. Please check back later.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($training_programs as $program): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($program['title']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($program['description']); ?></p>
                            <div class="flex items-center mb-4">
                                <i class="fas fa-clock text-blue-600 mr-2" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($program['duration']); ?></span>
                            </div>
                            <div class="flex items-center mb-4">
                                <i class="fa-solid fa-cedi-sign text-green-600 mr-2" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($program['price']); ?></span>
                            </div>
                            <button onclick="openRegistrationModal(<?php echo $program['id']; ?>)" 
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-300"
                                    aria-label="Register for <?php echo htmlspecialchars($program['title']); ?>">
                                Register Now
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Registration Modal -->
    <div id="registrationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h2 id="modalTitle" class="text-2xl font-bold mb-6">Register for Training</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registrationForm" class="space-y-4" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="program_id" id="program_id">
                <input type="hidden" name="register" value="1">
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Full Name <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           pattern="[A-Za-z\s]{2,50}"
                           title="Please enter a valid name (2-50 characters, letters and spaces only)"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                        Phone Number <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="tel" id="phone" name="phone" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           pattern="[0-9+\s-()]{10,}"
                           title="Please enter a valid phone number"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="message">
                        Message (Optional)
                    </label>
                    <textarea id="message" name="message" rows="3"
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                              maxlength="500"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Submit Registration
                    </button>
                    <button type="button" onclick="closeRegistrationModal()"
                            class="text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Modal functionality
        function openRegistrationModal(programId) {
            document.getElementById('program_id').value = programId;
            const modal = document.getElementById('registrationModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        }

        function closeRegistrationModal() {
            const modal = document.getElementById('registrationModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }

        // Close modal when clicking outside
        document.getElementById('registrationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRegistrationModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRegistrationModal();
            }
        });

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const message = document.getElementById('message').value;

            if (!name || !email || !phone) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }

            if (!/^[A-Za-z\s]{2,50}$/.test(name)) {
                e.preventDefault();
                alert('Please enter a valid name (2-50 characters, letters and spaces only)');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }

            if (!/^[0-9+\s-()]{10,}$/.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                return;
            }

            if (message.length > 500) {
                e.preventDefault();
                alert('Message must be less than 500 characters');
                return;
            }
        });
    </script>
</body>
</html> 