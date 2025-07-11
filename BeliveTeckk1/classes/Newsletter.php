<?php

class Newsletter {
    private $db;
    private $table = 'newsletter_subscriptions';
    private $email;

    public function __construct($db) {
        $this->db = $db;
        $this->email = new NewsletterEmail($db);
        $this->ensureTableStructure();
    }

    /**
     * Ensure the table has the required structure
     */
    private function ensureTableStructure() {
        try {
            // Check if created_at column exists
            $this->db->query("SELECT created_at FROM {$this->table} LIMIT 1");
        } catch (PDOException $e) {
            // Add created_at and updated_at columns if they don't exist
            $this->db->query("
                ALTER TABLE {$this->table}
                ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ");
        }
    }

    /**
     * Get all newsletter subscribers
     */
    public function all() {
        try {
            return $this->db->query("
                SELECT * FROM {$this->table} 
                ORDER BY created_at DESC
            ")->fetchAll();
        } catch (PDOException $e) {
            // Fallback to ordering by id if created_at is not available
            return $this->db->query("
                SELECT * FROM {$this->table} 
                ORDER BY id DESC
            ")->fetchAll();
        }
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribe($email, $name = null) {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return false;
            }

            // Insert new subscription
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (email, name, status) VALUES (?, ?, 'active')");
            $stmt->execute([$email, $name]);

            // Send welcome email
            $this->email->sendWelcomeEmail($email, $name);

            return true;
        } catch (PDOException $e) {
            error_log("Error in subscribe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe($email) {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'unsubscribed' WHERE email = ?");
            return $stmt->execute([$email]);
        } catch (PDOException $e) {
            error_log("Error in unsubscribe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete subscriber
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subscriber by ID
     */
    public function getById($id) {
        return $this->db->query("SELECT * FROM {$this->table} WHERE id = ?", [$id])->fetch();
    }

    /**
     * Get subscriber by email
     */
    public function getByEmail($email) {
        return $this->db->query("SELECT * FROM {$this->table} WHERE email = ?", [$email])->fetch();
    }

    /**
     * Get count of active subscribers
     */
    public function getActiveCount() {
        return $this->db->query("
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE status = 'active'
        ")->fetch()['count'];
    }

    /**
     * Create a new email template
     */
    public function createTemplate($name, $subject, $content, $image_url = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO newsletter_templates (name, subject, content, image_url) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$name, $subject, $content, $image_url]);
        } catch (PDOException $e) {
            error_log("Error in createTemplate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing email template
     */
    public function updateTemplate($id, $name, $subject, $content, $image_url = null) {
        try {
            $stmt = $this->db->prepare("UPDATE newsletter_templates SET name = ?, subject = ?, content = ?, image_url = ? WHERE id = ?");
            return $stmt->execute([$name, $subject, $content, $image_url, $id]);
        } catch (PDOException $e) {
            error_log("Error in updateTemplate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all email templates
     */
    public function getAllTemplates() {
        return $this->db->query("
            SELECT * FROM newsletter_templates 
            ORDER BY created_at DESC
        ")->fetchAll();
    }

    /**
     * Get template by ID
     */
    public function getTemplateById($id) {
        return $this->db->query("
            SELECT * FROM newsletter_templates 
            WHERE id = ?
        ", [$id])->fetch();
    }

    /**
     * Create a new campaign
     */
    public function createCampaign($subject, $content, $scheduled_at = null) {
        $status = $scheduled_at ? 'scheduled' : 'draft';
        
        $this->db->query("
            INSERT INTO newsletter_campaigns (subject, content, status, scheduled_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ", [$subject, $content, $status, $scheduled_at]);

        $campaign_id = $this->db->lastInsertId();

        if ($campaign_id && !$scheduled_at) {
            // If not scheduled, send immediately
            $this->sendCampaign($campaign_id);
        }

        return $campaign_id;
    }

    /**
     * Send a campaign
     */
    public function sendCampaign($campaign_id) {
        $campaign = $this->db->query("
            SELECT * FROM newsletter_campaigns 
            WHERE id = ?
        ", [$campaign_id])->fetch();

        if (!$campaign) {
            return false;
        }

        // Get all active subscribers
        $subscribers = $this->db->query("
            SELECT * FROM {$this->table} 
            WHERE status = 'active'
        ")->fetchAll();

        $success_count = 0;
        $failed_count = 0;

        foreach ($subscribers as $subscriber) {
            // Personalize content
            $personalized_content = str_replace(
                ['{name}', '{email}'],
                [$subscriber['name'] ?? 'Subscriber', $subscriber['email']],
                $campaign['content']
            );

            // Send email
            if ($this->email->sendNewsletter($campaign['subject'], $personalized_content)) {
                $success_count++;
            } else {
                $failed_count++;
            }
        }

        // Update campaign status
        $this->db->query("
            UPDATE newsletter_campaigns 
            SET status = 'sent', 
                sent_at = NOW(), 
                updated_at = NOW() 
            WHERE id = ?
        ", [$campaign_id]);

        return [
            'success' => $success_count,
            'failed' => $failed_count,
            'total' => count($subscribers)
        ];
    }

    /**
     * Get all campaigns
     */
    public function getAllCampaigns() {
        return $this->db->query("
            SELECT * FROM newsletter_campaigns 
            ORDER BY created_at DESC
        ")->fetchAll();
    }

    /**
     * Get campaign by ID
     */
    public function getCampaignById($id) {
        return $this->db->query("
            SELECT * FROM newsletter_campaigns 
            WHERE id = ?
        ", [$id])->fetch();
    }

    // Template Management Methods
    public function deleteTemplate($id) {
        try {
            // Get the template to check for image
            $stmt = $this->db->prepare("SELECT image_url FROM newsletter_templates WHERE id = ?");
            $stmt->execute([$id]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete the image file if it exists
            if ($template && !empty($template['image_url'])) {
                $image_path = __DIR__ . '/../' . $template['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            // Delete the template from database
            $stmt = $this->db->prepare("DELETE FROM newsletter_templates WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error in deleteTemplate: " . $e->getMessage());
            return false;
        }
    }

    public function getTemplates() {
        try {
            $stmt = $this->db->query("SELECT * FROM newsletter_templates ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getTemplates: " . $e->getMessage());
            return [];
        }
    }

    public function getTemplate($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM newsletter_templates WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getTemplate: " . $e->getMessage());
            return false;
        }
    }
} 