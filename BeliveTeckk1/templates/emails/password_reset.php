<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request | <?php echo SITE_NAME; ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #2D3748; background-color: #F7FAFC;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; margin-top: 40px; margin-bottom: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <!-- Header with Logo -->
        <div style="background-color: #4A5568; padding: 24px; text-align: center;">
            <?php if (defined('SITE_LOGO') && !empty(SITE_LOGO)): ?>
            <img src="<?php echo SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?> Logo" style="max-height: 60px; margin-bottom: 16px;">
            <?php endif; ?>
            <h1 style="color: #FFFFFF; margin: 0; font-size: 24px; font-weight: 600;">Password Reset Request</h1>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <p style="margin-top: 0; color: #4A5568; font-size: 16px;">Hello,</p>
            
            <p style="color: #4A5568; font-size: 16px;">We received a request to reset the password for your <?php echo SITE_NAME; ?> admin account. To proceed with the password reset, please click the button below:</p>
            
            <!-- Reset Button -->
            <div style="margin: 32px 0; text-align: center;">
                <a href="<?php echo htmlspecialchars($reset_link); ?>" 
                   style="display: inline-block; padding: 14px 32px; background-color: #4299E1; color: #FFFFFF; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 16px;">
                    Reset Password
                </a>
            </div>

            <!-- Security Notice -->
            <div style="background-color: #EDF2F7; padding: 24px; border-radius: 6px; margin: 24px 0;">
                <h3 style="color: #4A5568; font-size: 16px; margin: 0 0 12px 0;">🔒 Security Notice</h3>
                <ul style="color: #4A5568; margin: 0; padding-left: 20px; font-size: 14px;">
                    <li style="margin-bottom: 8px;">This link will expire in <?php echo $expiry_time; ?></li>
                    <li style="margin-bottom: 8px;">If you didn't request this password reset, please ignore this email</li>
                    <li>For security reasons, consider changing your password regularly</li>
                </ul>
            </div>

            <!-- Alternative Link -->
            <div style="margin-top: 24px;">
                <p style="color: #4A5568; font-size: 14px; margin-bottom: 8px;">If the button above doesn't work, copy and paste this URL into your browser:</p>
                <p style="background-color: #F7FAFC; padding: 12px; border-radius: 4px; color: #718096; font-size: 14px; word-break: break-all; margin: 0;">
                    <?php echo htmlspecialchars($reset_link); ?>
                </p>
            </div>

            <!-- Help Section -->
            <div style="margin-top: 32px; padding: 16px; background-color: #EBF8FF; border-radius: 6px;">
                <p style="color: #2B6CB0; margin: 0; font-size: 14px;">
                    <strong>Need Help?</strong> If you're having trouble resetting your password, please contact our support team.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 24px; background-color: #F7FAFC; border-top: 1px solid #E2E8F0;">
            <p style="margin: 0; color: #718096; font-size: 14px; text-align: center;">
                This is an automated security notification from <?php echo SITE_NAME; ?>. Please do not reply to this email.
            </p>
            <div style="margin-top: 16px; text-align: center;">
                <p style="color: #A0AEC0; font-size: 12px; margin: 0;">
                    © <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html> 