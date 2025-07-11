<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Newsletter.php';

$newsletter = new Newsletter($db);
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id, status FROM newsletter_subscriptions WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['status'] === 'unsubscribed') {
                    // Reactivate unsubscribed email
                    $stmt = $db->prepare("UPDATE newsletter_subscriptions SET status = 'active', name = ?, updated_at = NOW() WHERE email = ?");
                    $stmt->execute([$name, $email]);
                    $message = 'Welcome back! Your subscription has been reactivated.';
                    $messageType = 'success';
                } else {
                    $message = 'This email is already subscribed to our newsletter.';
                    $messageType = 'info';
                }
            } else {
                // Insert new subscription
                $stmt = $db->prepare("INSERT INTO newsletter_subscriptions (email, name, status) VALUES (?, ?, 'active')");
                $stmt->execute([$email, $name]);
                $message = 'Thank you for subscribing to our newsletter!';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            error_log("Newsletter subscription error: " . $e->getMessage());
            $message = 'An error occurred. Please try again later.';
            $messageType = 'error';
        }
    }
}
?>

<div class="bg-black text-white">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-red-500 mb-4">Stay Updated</h3>
                <p class="text-gray-300">Subscribe to our newsletter for the latest updates and exclusive content.</p>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg transform transition-all duration-300 <?php 
                    echo $messageType === 'success' ? 'bg-green-900/50 border border-green-500 text-green-200' : 
                        ($messageType === 'error' ? 'bg-red-900/50 border border-red-500 text-red-200' : 
                        'bg-blue-900/50 border border-blue-500 text-blue-200'); ?>">
                    <div class="flex items-center">
                        <?php if ($messageType === 'success'): ?>
                            <i class="fas fa-check-circle text-green-400 mr-2"></i>
                        <?php elseif ($messageType === 'error'): ?>
                            <i class="fas fa-exclamation-circle text-red-400 mr-2"></i>
                        <?php else: ?>
                            <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6" id="newsletterForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="relative">
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="name" id="name" 
                                   class="pl-10 block w-full rounded-lg bg-gray-900 border-gray-700 text-white placeholder-gray-400 focus:border-red-500 focus:ring-red-500 transition-colors duration-300"
                                   placeholder="Your name">
                        </div>
                    </div>
                    
                    <div class="relative">
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email" required 
                                   class="pl-10 block w-full rounded-lg bg-gray-900 border-gray-700 text-white placeholder-gray-400 focus:border-red-500 focus:ring-red-500 transition-colors duration-300"
                                   placeholder="your@email.com">
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="privacy" name="privacy" required 
                           class="h-4 w-4 text-red-500 focus:ring-red-500 border-gray-700 rounded bg-gray-900">
                    <label for="privacy" class="ml-2 block text-sm text-gray-300">
                        I agree to the <a href="localhost/privacy-policy" class="text-white hover:text-red-500 transition duration-300">Privacy Policy</a> and 
                        <a href="/terms" class="text-white hover:text-red-500 transition duration-300">Terms of Service</a>
                    </label>
                </div>
                
                <button type="submit" 
                        class="w-full bg-red-500 text-white py-3 px-6 rounded-lg font-medium hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-black transform transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                    <span class="flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Subscribe Now
                    </span>
                </button>
            </form>
            
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-400">
                    You can unsubscribe at any time. For more information, please review our 
                    <a href="/privacy-policy" class="text-white hover:text-red-500 transition duration-300">Privacy Policy</a>.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('newsletterForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        // Disable the submit button to prevent double submission
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <span class="flex items-center justify-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Subscribing...
            </span>
        `;
    });

    // Add form validation feedback
    const inputs = form.querySelectorAll('input');
    
    inputs.forEach(input => {
        input.addEventListener('invalid', function(e) {
            e.preventDefault();
            this.classList.add('border-red-500');
            submitButton.disabled = false;
            submitButton.innerHTML = `
                <span class="flex items-center justify-center">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Subscribe Now
                </span>
            `;
        });

        input.addEventListener('input', function() {
            this.classList.remove('border-red-500');
        });
    });
});
</script>