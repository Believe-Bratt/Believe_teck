<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Inquiry | <?php echo SITE_NAME; ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #2D3748; background-color: #F7FAFC;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; margin-top: 40px; margin-bottom: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <!-- Header with Logo -->
        <div style="background-color: #4A5568; padding: 24px; text-align: center;">
            <?php if (defined('SITE_LOGO') && !empty(SITE_LOGO)): ?>
            <img src="<?php echo SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?> Logo" style="max-height: 60px; margin-bottom: 16px;">
            <?php endif; ?>
            <h1 style="color: #FFFFFF; margin: 0; font-size: 24px; font-weight: 600;">New Contact Inquiry</h1>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <p style="margin-top: 0; color: #4A5568; font-size: 16px;">A new inquiry has been received through the <?php echo SITE_NAME; ?> contact form. Details are provided below:</p>
            
            <!-- Contact Information -->
            <div style="background-color: #EDF2F7; padding: 24px; border-radius: 6px; margin: 24px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #718096; width: 120px;">Name:</td>
                        <td style="padding: 8px 0; color: #2D3748; font-weight: 500;"><?php echo htmlspecialchars($name); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #718096;">Email:</td>
                        <td style="padding: 8px 0; color: #2D3748; font-weight: 500;"><?php echo htmlspecialchars($email); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #718096;">Phone:</td>
                        <td style="padding: 8px 0; color: #2D3748; font-weight: 500;"><?php echo htmlspecialchars($phone); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #718096;">Subject:</td>
                        <td style="padding: 8px 0; color: #2D3748; font-weight: 500;"><?php echo htmlspecialchars($subject); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Message Content -->
            <div style="margin-top: 24px;">
                <h3 style="color: #4A5568; font-size: 18px; margin-bottom: 16px;">Message Content:</h3>
                <div style="background-color: #F7FAFC; padding: 16px; border-left: 4px solid #4299E1; border-radius: 4px; color: #2D3748;">
                    <?php echo nl2br(htmlspecialchars($message)); ?>
                </div>
            </div>

            <!-- Action Required -->
            <div style="margin-top: 32px; padding: 16px; background-color: #EBF8FF; border-radius: 6px;">
                <p style="color: #2B6CB0; margin: 0; font-size: 14px;">
                    <strong>Action Required:</strong> Please review and respond to this inquiry within 24 hours.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 24px; background-color: #F7FAFC; border-top: 1px solid #E2E8F0;">
            <p style="margin: 0; color: #718096; font-size: 14px; text-align: center;">
                This is an automated notification from <?php echo SITE_NAME; ?>'s system. Please do not reply to this email.
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