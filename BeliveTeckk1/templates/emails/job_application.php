<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Job Application | <?php echo SITE_NAME; ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #2D3748; background-color: #F7FAFC;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; margin-top: 40px; margin-bottom: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <!-- Header with Logo -->
        <div style="background-color: #4A5568; padding: 24px; text-align: center;">
            <?php if (defined('SITE_LOGO') && !empty(SITE_LOGO)): ?>
            <img src="<?php echo SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?> Logo" style="max-height: 60px; margin-bottom: 16px;">
            <?php endif; ?>
            <h1 style="color: #FFFFFF; margin: 0; font-size: 24px; font-weight: 600;">New Job Application</h1>
            <p style="color: #E2E8F0; margin: 8px 0 0 0; font-size: 16px;"><?php echo htmlspecialchars($position); ?></p>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <p style="margin-top: 0; color: #4A5568; font-size: 16px;">A new candidate has applied for a position at <?php echo SITE_NAME; ?>. Please review their application details below:</p>
            
            <!-- Applicant Information -->
            <div style="background-color: #EDF2F7; padding: 24px; border-radius: 6px; margin: 24px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #718096; width: 140px;">Applicant Name:</td>
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
                        <td style="padding: 8px 0; color: #718096;">Position:</td>
                        <td style="padding: 8px 0; color: #2D3748; font-weight: 500;"><?php echo htmlspecialchars($position); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Application Documents -->
            <div style="background-color: #F7FAFC; padding: 24px; border-radius: 6px; margin: 24px 0; border: 1px solid #E2E8F0;">
                <h3 style="color: #4A5568; font-size: 18px; margin: 0 0 16px 0;">Application Documents</h3>
                
                <!-- Resume -->
                <div style="margin-bottom: 16px;">
                    <a href="<?php echo htmlspecialchars($resume_url); ?>" 
                       style="display: inline-block; padding: 12px 20px; background-color: #4299E1; color: #FFFFFF; text-decoration: none; border-radius: 4px; font-weight: 500;">
                        <span style="display: inline-block; vertical-align: middle;">📄</span>
                        Download Resume
                    </a>
                </div>

                <!-- Cover Letter -->
                <?php if (!empty($cover_letter)): ?>
                <div style="margin-top: 20px;">
                    <h4 style="color: #4A5568; font-size: 16px; margin: 0 0 12px 0;">Cover Letter</h4>
                    <div style="background-color: #FFFFFF; padding: 16px; border-left: 4px solid #4299E1; border-radius: 4px; color: #2D3748;">
                        <?php echo nl2br(htmlspecialchars($cover_letter)); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Required -->
            <div style="margin-top: 32px; padding: 16px; background-color: #EBF8FF; border-radius: 6px;">
                <p style="color: #2B6CB0; margin: 0; font-size: 14px;">
                    <strong>Action Required:</strong> Please review this application and update the candidate status in the admin panel.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 24px; background-color: #F7FAFC; border-top: 1px solid #E2E8F0;">
            <p style="margin: 0; color: #718096; font-size: 14px; text-align: center;">
                This is an automated notification from <?php echo SITE_NAME; ?>'s recruitment system. Please do not reply to this email.
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