<?php

class NewsletterEmail {
    private $db;
    private $from_email;
    private $from_name;

    public function __construct($db) {
        $this->db = $db;
        // Get site settings for email configuration
        $settings = $this->getSiteSettings();
        $this->from_email = $settings['contact_email'] ?? 'noreply@believeteckk.com';
        $this->from_name = $settings['site_name'] ?? 'Believe Teckk';
    }

    /**
     * Get site settings from the database
     */
    private function getSiteSettings() {
        $settings = [];
        $result = $this->db->query("SELECT * FROM settings")->fetchAll();
        foreach ($result as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Send newsletter to all active subscribers
     */
    public function sendNewsletter($subject, $content) {
        // Get all active subscribers
        $subscribers = $this->db->query("
            SELECT email, name 
            FROM newsletter_subscriptions 
            WHERE status = 'active'
        ")->fetchAll();

        $success_count = 0;
        $failed_count = 0;

        foreach ($subscribers as $subscriber) {
            $to = $subscriber['email'];
            $name = $subscriber['name'] ?? 'Subscriber';
            
            // Prepare email headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: ' . $this->from_name . ' <' . $this->from_email . '>',
                'Reply-To: ' . $this->from_email,
                'X-Mailer: PHP/' . phpversion()
            ];

            // Personalize content
            $personalized_content = str_replace(
                ['{name}', '{email}'],
                [$name, $subscriber['email']],
                $content
            );

            // Send email
            if (mail($to, $subject, $personalized_content, implode("\r\n", $headers))) {
                $success_count++;
            } else {
                $failed_count++;
            }
        }

        return [
            'success' => $success_count,
            'failed' => $failed_count,
            'total' => count($subscribers)
        ];
    }

    /**
     * Send welcome email to new subscriber
     */
    public function sendWelcomeEmail($email, $name = '') {
        $subject = 'Welcome to Our Newsletter!';
        
        $content = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Welcome to Our Newsletter!</h1>
                    </div>
                    <div class='content'>
                        <p>Dear " . ($name ? htmlspecialchars($name) : 'Subscriber') . ",</p>
                        <p>Thank you for subscribing to our newsletter. We're excited to have you join our community!</p>
                        <p>You'll now receive updates about:</p>
                        <ul>
                            <li>Latest news and announcements</li>
                            <li>New services and products</li>
                            <li>Special offers and promotions</li>
                            <li>Industry insights and tips</li>
                        </ul>
                        <p>If you have any questions or need to update your preferences, please don't hesitate to contact us.</p>
                    </div>
                    <div class='footer'>
                        <p>© " . date('Y') . " Believe Teckk. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($email, $subject, $content, implode("\r\n", $headers));
    }

    /**
     * Send confirmation email for unsubscribe request
     */
    public function sendUnsubscribeConfirmation($email, $name = '') {
        $subject = 'Unsubscribe Confirmation';
        
        $content = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Unsubscribe Confirmation</h1>
                    </div>
                    <div class='content'>
                        <p>Dear " . ($name ? htmlspecialchars($name) : 'Subscriber') . ",</p>
                        <p>You have been successfully unsubscribed from our newsletter.</p>
                        <p>We're sorry to see you go. If you change your mind, you can always resubscribe by visiting our website.</p>
                        <p>Thank you for being a subscriber, and we wish you all the best!</p>
                    </div>
                    <div class='footer'>
                        <p>© " . date('Y') . " Believe Teckk. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($email, $subject, $content, implode("\r\n", $headers));
    }
} 