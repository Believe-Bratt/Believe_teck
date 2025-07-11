<?php
class Mailer {
    private $from;
    private $fromName;
    private $replyTo;
    private $replyToName;
    
    public function __construct(
        $from = ADMIN_EMAIL,
        $fromName = SITE_NAME,
        $replyTo = ADMIN_EMAIL,
        $replyToName = SITE_NAME
    ) {
        $this->from = $from;
        $this->fromName = $fromName;
        $this->replyTo = $replyTo;
        $this->replyToName = $replyToName;
    }
    
    public function send($to, $subject, $body, $isHtml = true) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: ' . ($isHtml ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8'),
            'From: ' . $this->fromName . ' <' . $this->from . '>',
            'Reply-To: ' . $this->replyToName . ' <' . $this->replyTo . '>',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    public function sendContactNotification($data) {
        $subject = 'New Contact Form Submission - ' . SITE_NAME;
        
        $body = $this->getEmailTemplate('contact_notification', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? 'Not provided',
            'subject' => $data['subject'],
            'message' => nl2br($data['message'])
        ]);
        
        return $this->send($this->from, $subject, $body);
    }
    
    public function sendJobApplicationNotification($data) {
        $subject = 'New Job Application - ' . SITE_NAME;
        
        $body = $this->getEmailTemplate('job_application', [
            'name' => $data['applicant_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? 'Not provided',
            'position' => $data['position_title'],
            'resume_url' => $data['resume_url'],
            'cover_letter' => nl2br($data['cover_letter'])
        ]);
        
        return $this->send($this->from, $subject, $body);
    }
    
    public function sendPasswordReset($email, $token) {
        $subject = 'Password Reset Request - ' . SITE_NAME;
        
        $resetLink = SITE_URL . '/admin/reset-password.php?token=' . $token;
        
        $body = $this->getEmailTemplate('password_reset', [
            'email' => $email,
            'reset_link' => $resetLink,
            'expiry_time' => '1 hour'
        ]);
        
        return $this->send($email, $subject, $body);
    }
    
    private function getEmailTemplate($template, $data) {
        $templateFile = __DIR__ . '/../templates/emails/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new Exception('Email template not found: ' . $template);
        }
        
        extract($data);
        
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }
} 