<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> Newsletter</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #2D3748; background-color: #F7FAFC;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; margin-top: 40px; margin-bottom: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <!-- Header with Logo -->
        <div style="background-color: #4A5568; padding: 32px 24px; text-align: center;">
            <?php if (defined('SITE_LOGO') && !empty(SITE_LOGO)): ?>
            <img src="<?php echo SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?> Logo" style="max-height: 60px; margin-bottom: 16px;">
            <?php endif; ?>
            <h1 style="color: #FFFFFF; margin: 0; font-size: 28px; font-weight: 600;"><?php echo SITE_NAME; ?></h1>
            <p style="color: #E2E8F0; margin: 8px 0 0 0; font-size: 16px;">Tech Innovation & Digital Solutions</p>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <!-- Featured Image -->
            <?php if (!empty($image_url)): ?>
            <div style="margin-bottom: 32px; text-align: center;">
                <img src="<?php echo SITE_URL . '/' . $image_url; ?>" alt="Featured Image" style="max-width: 100%; height: auto; border-radius: 8px;">
            </div>
            <?php endif; ?>

            <!-- Newsletter Content -->
            <div style="color: #2D3748; font-size: 16px; line-height: 1.8;">
                <?php echo $content; ?>
            </div>

            <!-- Featured Section -->
            <?php if (!empty($featured_content)): ?>
            <div style="margin-top: 32px; padding: 24px; background-color: #EDF2F7; border-radius: 6px;">
                <h3 style="color: #4A5568; font-size: 18px; margin: 0 0 16px 0;">Featured Update</h3>
                <div style="color: #2D3748;">
                    <?php echo $featured_content; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Call to Action -->
            <?php if (!empty($cta_link)): ?>
            <div style="margin-top: 32px; text-align: center;">
                <a href="<?php echo htmlspecialchars($cta_link); ?>" 
                   style="display: inline-block; padding: 12px 24px; background-color: #4299E1; color: #FFFFFF; text-decoration: none; border-radius: 4px; font-weight: 500;">
                    Learn More
                </a>
            </div>
            <?php endif; ?>

            <!-- Social Links -->
            <div style="margin-top: 32px; text-align: center;">
                <p style="color: #4A5568; margin-bottom: 16px;">Follow us on social media</p>
                <div>
                    <?php if (!empty($social_links['linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($social_links['linkedin']); ?>" style="text-decoration: none; margin: 0 8px;">
                        <img src="<?php echo SITE_URL; ?>/assets/images/linkedin-icon.png" alt="LinkedIn" style="width: 24px; height: 24px;">
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($social_links['twitter'])): ?>
                    <a href="<?php echo htmlspecialchars($social_links['twitter']); ?>" style="text-decoration: none; margin: 0 8px;">
                        <img src="<?php echo SITE_URL; ?>/assets/images/twitter-icon.png" alt="Twitter" style="width: 24px; height: 24px;">
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 24px; background-color: #F7FAFC; border-top: 1px solid #E2E8F0;">
            <p style="margin: 0 0 16px 0; color: #718096; font-size: 14px; text-align: center;">
                You're receiving this email because you subscribed to updates from <?php echo SITE_NAME; ?>.
            </p>
            <p style="margin: 0 0 16px 0; color: #718096; font-size: 14px; text-align: center;">
                To unsubscribe from our newsletter, 
                <a href="<?php echo htmlspecialchars($unsubscribe_link); ?>" 
                   style="color: #4299E1; text-decoration: none;">click here</a>.
            </p>
            <div style="text-align: center;">
                <p style="color: #A0AEC0; font-size: 12px; margin: 0;">
                    © <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html> 