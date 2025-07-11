<?php
// Get settings from database
require_once(__DIR__ . '/../config/config.php');
require_once __DIR__ . '/../classes/Database.php';
require_once(__DIR__ . '/../classes/Admin.php');

$db = Database::getInstance();
$admin = new Admin($db);
$settings = $admin->getSettings();
?>
    </main>
    <!-- Footer -->
    <footer class="bg-black text-white mt-16">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <h3 class="text-xl font-bold text-red-500 mb-4"><?php echo htmlspecialchars($settings['site_name'] ?? 'Believe Teckk'); ?></h3>
                    <p class="mb-4"><?php echo htmlspecialchars($settings['site_description'] ?? 'Your Trusted Technology Partner'); ?></p>
                    <div class="flex space-x-4">
                        <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_facebook']); ?>" class="text-white hover:text-red-500 transition duration-300" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_twitter']); ?>" class="text-white hover:text-red-500 transition duration-300" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($settings['social_linkedin']); ?>" class="text-white hover:text-red-500 transition duration-300" target="_blank">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="hover:text-red-500 transition duration-300">About Us</a></li>
                        <li><a href="services.php" class="hover:text-red-500 transition duration-300">Services</a></li>
                        <li><a href="portfolio.php" class="hover:text-red-500 transition duration-300">Portfolio</a></li>
                        <li><a href="careers.php" class="hover:text-red-500 transition duration-300">Careers</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Services</h3>
                    <ul class="space-y-2">
                        <?php
                        $services = $admin->getServices();
                        foreach ($services as $service): ?>
                            <li><a href="services.php#<?php echo htmlspecialchars($service['id']); ?>" class="hover:text-red-500 transition duration-300"><?php echo htmlspecialchars($service['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <?php if (!empty($settings['contact_phone'])): ?>
                        <li><i class="fas fa-phone mr-2"></i> <?php echo htmlspecialchars($settings['contact_phone']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($settings['contact_email'])): ?>
                        <li><i class="fas fa-envelope mr-2"></i> <?php echo htmlspecialchars($settings['contact_email']); ?></li>
                        <?php endif; ?>
                        <?php if (!empty($settings['contact_address'])): ?>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($settings['contact_address']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <p class="text-center text-sm text-gray-400 mt-8 md:col-span-4">.<a href="admin/login.php">.</a></p>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'Believe Teckk'); ?>. All rights reserved.</p>
                <p class="text-sm">Designed by <a href="https://believe-teckk.com" class="text-red-500 hover:underline" target="_blank">Believe Teckk</a></p>
            </div>
        </div>
        <!-- Additional Footer Sections -->
        <div class="border-t border-gray-800 mt-8 pt-8">
            <div class="container mx-auto px-4">
                <div class="flex flex-wrap justify-between items-center">
                    <div class="text-sm text-gray-400">
                        © <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'Believe Teckk'); ?>. All rights reserved.
                    </div>
                    <div class="flex space-x-4 text-sm text-gray-400">
                        <a href="privacy-policy.php" class="hover:text-white">Privacy Policy</a>
                        <a href="terms-of-service.php" class="hover:text-white">Terms of Service</a>
                        <a href="sitemap.php" class="hover:text-white">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuButton = document.getElementById("mobile-menu-button");
        const closeButton = document.getElementById("close-mobile-menu");
        const mobileMenu = document.getElementById("mobile-menu");
        const body = document.body;

        function openMenu() {
            mobileMenu.classList.remove("hidden");
            body.style.overflow = "hidden";
            // Add a small delay to ensure the transition works
            setTimeout(() => {
                mobileMenu.style.opacity = "1";
                mobileMenu.style.transform = "translateY(0)";
            }, 10);
        }

        function closeMenu() {
            mobileMenu.style.opacity = "0";
            mobileMenu.style.transform = "translateY(-100%)";
            setTimeout(() => {
                mobileMenu.classList.add("hidden");
                body.style.overflow = "";
            }, 300);
        }

        menuButton.addEventListener("click", function () {
            openMenu();
            menuButton.setAttribute("aria-expanded", "true");
        });

        closeButton.addEventListener("click", function () {
            closeMenu();
            menuButton.setAttribute("aria-expanded", "false");
        });

        // Close menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll("a");
        mobileLinks.forEach(link => {
            link.addEventListener("click", function() {
                closeMenu();
                menuButton.setAttribute("aria-expanded", "false");
            });
        });

        // Close menu when pressing Escape key
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape" && !mobileMenu.classList.contains("hidden")) {
                closeMenu();
                menuButton.setAttribute("aria-expanded", "false");
            }
        });
    });
    </script>

    <?php include 'includes/chat-bubble.php'; ?>
</body>
</html>