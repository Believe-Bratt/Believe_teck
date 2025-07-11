<?php
$files = [
    'training.php',
    'portfolio.php',
    'portfolio-detail.php',
    'includes/footer.php',
    'includes/editor.php',
    'contact.php',
    'components/newsletter-form.php',
    'careers.php',
    'blog-post.php',
    'blog.php',
    'apply.php',
    'admin/contact.php',
    'admin/dashboard.php',
    'admin/login.php',
    'admin/services.php',
    'admin/training.php',
    'admin/team.php',
    'admin/settings.php',
    'admin/portfolio.php',
    'admin/profile.php',
    'admin/page-content.php',
    'admin/newsletter.php',
    'admin/newsletter-campaigns.php',
    'admin/portfolio-categories.php',
    'admin/get_inquiry.php',
    'admin/careers.php',
    'admin/blog.php',
    'admin/applications.php',
    'admin/inquiries.php',
    'admin/about.php',
    'admin/ajax/get-page-content.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = preg_replace(
            '/require.*config\/database\.php/i',
            'require_once __DIR__ . \'/../classes/Database.php\';',
            $content
        );
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}

echo "Done updating database includes.\n";
?> 