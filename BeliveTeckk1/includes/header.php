<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Believe Teckk is a leading technology company providing innovative solutions for businesses worldwide. We specialize in software development, web development, and IT consulting.">
    <meta name="keywords" content="Believe Teckk, technology, IT solutions, software development, web development, mobile app development, IT consulting, digital transformation">
    <meta name="author" content="Evans Adu Oppong">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="Believe Teckk - Professional IT Solutions">
    <meta property="og:description" content="Leading technology company providing innovative solutions for businesses worldwide.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>assets/images/beltekk.png">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Believe Teckk - Professional IT Solutions">
    <meta name="twitter:description" content="Leading technology company providing innovative solutions for businesses worldwide.">
    <meta name="twitter:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>assets/images/beltekk.png">
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Believe Teckk - Professional IT Solutions'; ?></title>
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
    <link rel="preload" href="assets/css/style.css" as="style">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Deferred JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="assets/js/main.js" defer></script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
</head>
<body class="font-sans">
    <!-- Navigation -->
    <nav class="bg-black text-white fixed w-full z-50 top-0 transition-all duration-300">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="assets/images/b-logo.png" alt="Believe Teckk" class="h-20 w-auto" loading="lazy">
                    <span class="text-2xl font-bold">
                        Believe<span class="text-red-500">Teckk</span>
                    </span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex space-x-8">
                    <?php
                    $navItems = [
                        'index.php' => 'Home',
                        'about.php' => 'About',
                        'services.php' => 'Services',
                        'portfolio.php' => 'Portfolio',
                        'blog.php' => 'Blog',
                        'training.php' => 'Training',
                        'careers.php' => 'Careers',
                        'contact.php' => 'Contact'
                    ];
                    
                    foreach ($navItems as $url => $label) {
                        $isActive = basename($_SERVER['PHP_SELF']) === $url;
                        echo '<a href="' . $url . '" class="hover:text-red-500 transition duration-300' . 
                             ($isActive ? ' text-red-500' : '') . '">' . $label . '</a>';
                    }
                    ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white focus:outline-none" aria-label="Toggle menu">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="hidden md:hidden fixed inset-0 bg-black bg-opacity-95 z-50 transform transition-all duration-300 ease-in-out">
                <div class="flex flex-col h-full">
                    <div class="flex justify-end p-4">
                        <button id="close-mobile-menu" class="text-white focus:outline-none" aria-label="Close menu">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                    <div class="flex-1 flex flex-col justify-center items-center space-y-6 px-4">
                        <?php
                        foreach ($navItems as $url => $label) {
                            $isActive = basename($_SERVER['PHP_SELF']) === $url;
                            echo '<a href="' . $url . '" class="text-2xl font-semibold text-white hover:text-red-500 transition duration-300 transform hover:scale-105' . 
                                 ($isActive ? ' text-red-500' : '') . '">' . $label . '</a>';
                        }
                        ?>
                    </div>
                    <div class="p-4 mt-20px text-center text-gray-400">
                        <p>Believe<span class="text-red-500">Teckk</span></p>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Main Content Container -->
    <main class="mt-16">