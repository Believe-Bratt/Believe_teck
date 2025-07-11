<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require  __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once  __DIR__ . '/includes/header.php';

$db = Database::getInstance();

// Fetch contact page settings
$settings = [];
$result = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'contact_%' OR setting_key = 'working_hours'")->fetchAll();
foreach ($result as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch page content
$pageContent = $db->query("SELECT * FROM page_contents WHERE page_slug = 'contact'")->fetch();
$pageTitle = $pageContent['title'] ?? 'Contact Us';
$pageSubtitle = $pageContent['mission'] ?? '';
$pageDescription = $pageContent['vision'] ?? '';

// Handle contact form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        // Clean the message content
        $message = html_entity_decode($message);
        $message = strip_tags($message);
        $message = trim($message);

        // Insert into database
        $sql = "INSERT INTO inquiries (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $db->prepare($sql);

        if ($stmt->execute([$name, $email, $phone, $subject, $message])) {
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // SMTP Configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'believebrat@gmail.com'; 
                $mail->Password = 'ewzg uxer ufio lrnh'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Sender and recipient
                $mail->setFrom('believebrat@gmail.com', 'Believe Teckk');
                $mail->addAddress($email, $name); 
                $mail->addReplyTo('believebrat@gmail.com', 'Believe Teckk');

                // Email content
                $mail->isHTML(true);
                $mail->Subject = "Inquiry Received: " . $subject;
                $mail->Body = "<p>Dear $name,</p>
                               <p>Thank you for reaching out to us. We have received your message and will respond soon.</p>
                               <p><strong>Subject:</strong> $subject</p>
                               <p><strong>Message:</strong> " . nl2br(htmlspecialchars($message)) . "</p>
                               <p>Best regards,<br>Believe Teckk Teams</p>";

                if ($mail->send()) {
                    $success_message = "Thank you for your message. We'll get back to you soon!";
                } else {
                    $error_message = "Message sent to the database, but email failed.";
                }
            } catch (Exception $e) {
                $error_message = "Email could not be sent. Error: {$mail->ErrorInfo}";
            }
        } else {
            $error_message = "Sorry, there was an error sending your message. Please try again.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}
?>


<!-- Hero Section -->
<section class="bg-black text-white py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl font-bold mb-4"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="text-xl text-gray-300 mb-8"><?php echo htmlspecialchars($pageSubtitle); ?></p>
            <div class="prose prose-invert max-w-none">
                <?php echo $pageDescription; ?>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Email -->
            <div class="text-center p-6 bg-gray-50 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-black">Email Us</h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?></p>
            </div>

            <!-- Phone -->
            <div class="text-center p-6 bg-gray-50 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-black">Call Us</h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?></p>
            </div>

            <!-- Office -->
            <div class="text-center p-6 bg-gray-50 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-map-marker-alt text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-black">Visit Us</h3>
                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($settings['contact_address'] ?? '')); ?></p>
                
            </div>
            <div class="text-center p-6 bg-gray-50 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-business-time text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2 text-black">Hours:</h3>
                <p class="text-gray-600 mt-2"> <?php echo nl2br(htmlspecialchars($settings['working_hours'] ?? '')); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto rounded-lg overflow-hidden shadow-lg">
            <?php echo $settings['contact_map_embed'] ?? ''; ?>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4 text-black"><?php echo htmlspecialchars($settings['contact_form_title'] ?? 'Send Us a Message'); ?></h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($settings['contact_form_description'] ?? ''); ?></p>
            </div>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="bg-gray-50 shadow-lg rounded-lg p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name *</label>
                        <input type="text" id="name" name="name" required 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="message">Message *</label>
                    <textarea id="message" name="message" rows="6" required 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                </div>

                <div class="mt-8">
                    <button type="submit" 
                            class="bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transition duration-300 w-full">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once('includes/footer.php'); ?>