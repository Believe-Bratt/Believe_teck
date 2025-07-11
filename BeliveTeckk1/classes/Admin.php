<?php
require_once __DIR__ . '/../vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Admin extends Model {
    protected $table = 'admins';
    protected $blog_table = 'blog_posts';
    protected $blog_category_table = 'blog_categories';
    protected $page_table = 'page_contents';
    protected $training_table = 'training_programs';
    protected $registration_table = 'training_registrations';
    protected $services_table = 'services';
    protected $portfolio_categories_table = 'portfolio_categories';
    protected $team_table = 'team_members';
    
    protected $db;
    private $table_prefix = 'admin_';
    
    public function __construct($db) {
        parent::__construct($db);
        $this->db = $db;
    }
    
    /**
     * Authenticate admin user
     * @param string $email
     * @param string $password
     * @return array|false User data if successful, false otherwise
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Update last login timestamp
                $this->updateLastLogin($user['id']);
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     * @param int $userId
     * @return bool
     */
    private function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get login attempts count for an IP
     * @param string $ip
     * @param int $timeWindow Time window in seconds
     * @return int Number of attempts
     */
    public function getLoginAttempts($ip, $timeWindow) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as attempts 
                FROM {$this->table_prefix}login_attempts 
                WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([$ip, $timeWindow]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['attempts'];
        } catch (PDOException $e) {
            error_log("Get login attempts error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Increment login attempts for an IP
     * @param string $ip
     * @return bool
     */
    public function incrementLoginAttempts($ip) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table_prefix}login_attempts (ip_address, attempt_time)
                VALUES (?, NOW())
            ");
            return $stmt->execute([$ip]);
        } catch (PDOException $e) {
            error_log("Increment login attempts error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset login attempts for an IP
     * @param string $ip
     * @return bool
     */
    public function resetLoginAttempts($ip) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM {$this->table_prefix}login_attempts 
                WHERE ip_address = ?
            ");
            return $stmt->execute([$ip]);
        } catch (PDOException $e) {
            error_log("Reset login attempts error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save remember me token
     * @param int $userId
     * @param string $token
     * @param int $expires
     * @return bool
     */
    public function saveRememberToken($userId, $token, $expires) {
        try {
            // First, invalidate any existing tokens for this user
            $this->invalidateRememberTokens($userId);

            // Insert new token
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table_prefix}remember_tokens 
                (user_id, token, expires_at)
                VALUES (?, ?, FROM_UNIXTIME(?))
            ");
            return $stmt->execute([$userId, $token, $expires]);
        } catch (PDOException $e) {
            error_log("Save remember token error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate all remember tokens for a user
     * @param int $userId
     * @return bool
     */
    private function invalidateRememberTokens($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table_prefix}remember_tokens 
                SET is_valid = 0 
                WHERE user_id = ?
            ");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Invalidate remember tokens error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate remember token
     * @param string $token
     * @return array|false User data if valid, false otherwise
     */
    public function validateRememberToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.* 
                FROM {$this->table_prefix}users u
                JOIN {$this->table_prefix}remember_tokens rt ON u.id = rt.user_id
                WHERE rt.token = ? 
                AND rt.is_valid = 1 
                AND rt.expires_at > NOW()
                AND u.is_active = 1
            ");
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Validate remember token error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by ID
     * @param int $userId
     * @return array|false
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if password needs to be changed
     * @param int $userId
     * @param int $maxDays Maximum days before password change required
     * @return bool
     */
    public function isPasswordChangeRequired($userId, $maxDays = 90) {
        try {
            $stmt = $this->db->prepare("
                SELECT updated_at 
                FROM {$this->table} 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !$user['updated_at']) {
                return true;
            }

            $lastChange = strtotime($user['updated_at']);
            $now = time();
            $daysSinceChange = ($now - $lastChange) / (24 * 60 * 60);

            return $daysSinceChange >= $maxDays;
        } catch (PDOException $e) {
            error_log("Check password change required error: " . $e->getMessage());
            return true; // Require change on error for security
        }
    }

    /**
     * Log user activity
     * @param int $userId
     * @param string $action
     * @param string $details
     * @return bool
     */
    public function logActivity($userId, $action, $details = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO admin_activity_log 
                (user_id, action, details, ip_address)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $userId,
                $action,
                $details,
                $_SERVER['REMOTE_ADDR']
            ]);
        } catch (PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user session is still valid
     * @param int $userId
     * @param int $maxInactivity Maximum seconds of inactivity
     * @return bool
     */
    public function isSessionValid($userId, $maxInactivity = 1800) {
        try {
            $stmt = $this->db->prepare("
                SELECT last_activity 
                FROM {$this->table_prefix}users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !$user['last_activity']) {
                return false;
            }

            $lastActivity = strtotime($user['last_activity']);
            $now = time();
            return ($now - $lastActivity) <= $maxInactivity;
        } catch (PDOException $e) {
            error_log("Check session validity error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user's last activity timestamp
     * @param int $userId
     * @return bool
     */
    public function updateLastActivity($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table_prefix}users 
                SET last_activity = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Update last activity error: " . $e->getMessage());
            return false;
        }
    }
    
    public function createUser($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }
    
    public function updateUser($id, $data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }
    
    public function deleteUser($id) {
        return parent::delete($id);
    }
    
    public function toggleUserActive($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getAllUsers() {
        $sql = "SELECT id, username, email, role, is_active, created_at FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateSettings($data) {
        $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        
        return true;
    }
    
    public function getSettings() {
        $sql = "SELECT * FROM settings";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    public function updatePageContent($page_slug, $data) {
        $sql = "UPDATE page_contents SET 
                title = ?, 
                content = ?, 
                meta_description = ?, 
                meta_keywords = ?, 
                is_active = ?,
                updated_at = NOW() 
                WHERE page_slug = ?";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['content'],
            $data['meta_description'],
            $data['meta_keywords'],
            $data['is_active'],
            $page_slug
        ]);
    }
    
    public function getPageContent($page_slug) {
        $sql = "SELECT * FROM page_contents WHERE page_slug = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$page_slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllPages() {
        $sql = "SELECT * FROM page_contents ORDER BY page_slug";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getBlogPosts() {
        try {
            $sql = "SELECT id, title, slug, content, featured_image, author_name, status, 
                           published_at, created_at, updated_at 
                    FROM {$this->blog_table} 
                    ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting blog posts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Strip HTML tags and clean content
     * @param string $content
     * @return string
     */
    private function cleanContent($content) {
        // First decode HTML entities
        $content = html_entity_decode($content);
        // Then strip HTML tags
        $content = strip_tags($content);
        // Clean up whitespace
        $content = trim($content);
        return $content;
    }

    /**
     * Updates the about page content
     * @param array $data Array containing page content fields
     * @return bool True on success, false on failure
     */
    public function updateAboutPage($data) {
        try {
            // Clean content fields
            $data['content'] = $this->cleanContent($data['content']);
            $data['mission'] = $this->cleanContent($data['mission']);
            $data['vision'] = $this->cleanContent($data['vision']);
            $data['values'] = $this->cleanContent($data['values']);
            $data['founder_content'] = $this->cleanContent($data['founder_content']);

            $sql = "UPDATE page_contents SET 
                    title = :title,
                    content = :content,
                    mission = :mission,
                    vision = :vision,
                    `values` = :values,
                    founder_name = :founder_name,
                    founder_position = :founder_position,
                    founder_content = :founder_content";

            // Only include founder_image in the update if it's provided
            if (isset($data['founder_image'])) {
                $sql .= ", founder_image = :founder_image";
            }

            $sql .= " WHERE page_slug = 'about'";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':title' => $data['title'],
                ':content' => $data['content'],
                ':mission' => $data['mission'],
                ':vision' => $data['vision'],
                ':values' => $data['values'],
                ':founder_name' => $data['founder_name'],
                ':founder_position' => $data['founder_position'],
                ':founder_content' => $data['founder_content']
            ];

            if (isset($data['founder_image'])) {
                $params[':founder_image'] = $data['founder_image'];
            }

            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log("Error updating about page: " . $e->getMessage());
            throw new Exception("Failed to update about page content");
        }
    }

    /**
     * Gets the about page content
     * @return array The about page content
     */
    public function getAboutPage() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM page_contents WHERE page_slug = 'about'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // If no record exists, create one with default values
                $default_values = [
                    'page_slug' => 'about',
                    'title' => 'About Us',
                    'content' => '',
                    'mission' => '',
                    'vision' => '',
                    'values' => '',
                    'founder_name' => '',
                    'founder_position' => '',
                    'founder_content' => '',
                    'founder_image' => null
                ];
                
                $sql = "INSERT INTO page_contents 
                        (page_slug, title, content, mission, vision, `values`, 
                         founder_name, founder_position, founder_content, founder_image) 
                        VALUES 
                        (:page_slug, :title, :content, :mission, :vision, :values,
                         :founder_name, :founder_position, :founder_content, :founder_image)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($default_values);
                
                return $default_values;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting about page content: " . $e->getMessage());
            throw new Exception("Failed to retrieve about page content");
        }
    }
    
    /**
     * Adds a new training program
     * @param string $title Program title
     * @param string $description Program description
     * @param string $duration Program duration
     * @param string $price Program price
     * @param string $whatsapp_group WhatsApp group link
     * @return int|false The ID of the newly created program or false on failure
     * @throws Exception if required fields are missing
     */
    public function addTrainingProgram($title, $description, $duration, $price, $whatsapp_group) {
        try {
            // Validate input data
            if (empty($title) || empty($description) || empty($duration) || empty($price) || empty($whatsapp_group)) {
                throw new Exception("All fields are required");
            }

            $sql = "INSERT INTO {$this->training_table} (
                title, 
                description, 
                duration, 
                price, 
                whatsapp_group,
                created_at,
                updated_at
            ) VALUES (
                :title,
                :description,
                :duration,
                :price,
                :whatsapp_group,
                NOW(),
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':title' => $title,
                ':description' => $description,
                ':duration' => $duration,
                ':price' => $price,
                ':whatsapp_group' => $whatsapp_group
            ];

            $stmt->execute($params);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding training program: " . $e->getMessage());
            throw new Exception("Failed to add training program: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Error validating training program data: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets all training programs
     * @return array Array of training programs
     */
    public function getTrainingPrograms() {
        try {
            $sql = "SELECT * FROM training_programs ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting training programs: " . $e->getMessage());
            throw new Exception("Failed to retrieve training programs");
        }
    }

    /**
     * Gets a specific training program by ID
     * @param int $id Training program ID
     * @return array|false Training program data or false if not found
     */
    public function getTrainingProgram($id) {
        try {
            $sql = "SELECT * FROM training_programs WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting training program: " . $e->getMessage());
            throw new Exception("Failed to retrieve training program");
        }
    }

    /**
     * Updates a training program
     * @param int $id Training program ID
     * @param string $title Program title
     * @param string $description Program description
     * @param string $duration Program duration
     * @param string $price Program price
     * @param string $whatsapp_group WhatsApp group link
     * @return bool True on success, false on failure
     */
    public function updateTrainingProgram($id, $title, $description, $duration, $price, $whatsapp_group) {
        try {
            // Validate input data
            if (empty($id) || empty($title) || empty($description) || empty($duration) || empty($price) || empty($whatsapp_group)) {
                throw new Exception("All fields are required");
            }

            $sql = "UPDATE {$this->training_table} SET 
                    title = :title,
                    description = :description,
                    duration = :duration,
                    price = :price,
                    whatsapp_group = :whatsapp_group,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':id' => $id,
                ':title' => $title,
                ':description' => $description,
                ':duration' => $duration,
                ':price' => $price,
                ':whatsapp_group' => $whatsapp_group
            ];

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating training program: " . $e->getMessage());
            throw new Exception("Failed to update training program: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Error validating training program data: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletes a training program
     * @param int $id Training program ID
     * @return bool True on success, false on failure
     */
    public function deleteTrainingProgram($id) {
        try {
            $sql = "DELETE FROM training_programs WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting training program: " . $e->getMessage());
            throw new Exception("Failed to delete training program");
        }
    }
    
    public function getTrainingRegistrations() {
        $sql = "SELECT r.*, p.title as program_title 
                FROM {$this->registration_table} r 
                LEFT JOIN {$this->training_table} p ON r.program_id = p.id 
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a single blog post by ID
     * @param int $id Blog post ID
     * @return array|false Blog post data or false if not found
     */
    public function getBlogPost($id) {
        try {
            $sql = "SELECT * FROM {$this->blog_table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting blog post: " . $e->getMessage());
            throw new Exception("Failed to get blog post");
        }
    }

    /**
     * Adds a new blog post
     * @param string $title Post title
     * @param string $content Post content
     * @param string $image_url Image URL
     * @param string $author Author name
     * @return bool True on success, false on failure
     */
    public function addBlogPost($title, $content, $image_url, $author) {
        try {
            // Validate required fields
            if (empty($title) || empty($content) || empty($author)) {
                error_log("Missing required fields in addBlogPost");
                return false;
            }

            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

            $sql = "INSERT INTO {$this->blog_table} (
                title, 
                slug,
                content, 
                featured_image, 
                author_name,
                status,
                published_at,
                created_at,
                updated_at
            ) VALUES (
                :title,
                :slug,
                :content,
                :featured_image,
                :author_name,
                'published',
                NOW(),
                NOW(),
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':title' => $title,
                ':slug' => $slug,
                ':content' => $content,
                ':featured_image' => $image_url,
                ':author_name' => $author
            ];

            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("Failed to execute addBlogPost query");
                error_log("SQL Error: " . implode(", ", $stmt->errorInfo()));
                return false;
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error in addBlogPost: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
        }
    }

    /**
     * Updates a blog post
     * @param int $id Post ID
     * @param string $title Post title
     * @param string $content Post content
     * @param string $author Author name
     * @param string $image_url Image URL
     * @return bool True on success, false on failure
     */
    public function updateBlogPost($id, $title, $content, $author, $image_url) {
        try {
            // Clean content before saving
            $content = $this->cleanContent($content);

            $sql = "UPDATE {$this->blog_table} SET 
                    title = :title,
                    content = :content,
                    author = :author,
                    image_url = :image_url,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':id' => $id,
                ':title' => $title,
                ':content' => $content,
                ':author' => $author,
                ':image_url' => $image_url
            ];

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating blog post: " . $e->getMessage());
            throw new Exception("Failed to update blog post");
        }
    }

    /**
     * Deletes a blog post
     * @param int $id Post ID
     * @return bool True on success, false on failure
     */
    public function deleteBlogPost($id) {
        try {
            $sql = "DELETE FROM {$this->blog_table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting blog post: " . $e->getMessage());
            throw new Exception("Failed to delete blog post");
        }
    }

    public function addService($title, $description, $icon) {
        try {
            $this->db->beginTransaction();
            
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            $sql = "INSERT INTO {$this->services_table} (title, slug, description, icon, is_active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$title, $slug, $description, $icon]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                throw new Exception("Failed to add service");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error adding service: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateService($id, $title, $description, $icon) {
        try {
            $this->db->beginTransaction();
            
            // First check if service exists
            $check_sql = "SELECT id FROM {$this->services_table} WHERE id = ?";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->execute([$id]);
            
            if (!$check_stmt->fetch()) {
                throw new Exception("Service not found");
            }
            
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            $sql = "UPDATE {$this->services_table} 
                    SET title = ?, slug = ?, description = ?, icon = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$title, $slug, $description, $icon, $id]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                throw new Exception("Failed to update service");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating service: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteService($id) {
        try {
            $this->db->beginTransaction();
            
            // First check if service exists
            $check_sql = "SELECT id FROM {$this->services_table} WHERE id = ?";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->execute([$id]);
            
            if (!$check_stmt->fetch()) {
                throw new Exception("Service not found");
            }
            
            $sql = "DELETE FROM {$this->services_table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                throw new Exception("Failed to delete service");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting service: " . $e->getMessage());
            throw $e;
        }
    }

    public function getServiceById($id) {
        try {
            $sql = "SELECT * FROM {$this->services_table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$service) {
                throw new Exception("Service not found");
            }
            
            return $service;
        } catch (Exception $e) {
            error_log("Error getting service: " . $e->getMessage());
            throw $e;
        }
    }

    public function getServices() {
        try {
            $sql = "SELECT * FROM {$this->services_table} ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting services: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPortfolioCategories() {
        try {
            $sql = "SELECT * FROM {$this->portfolio_categories_table} ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting portfolio categories: " . $e->getMessage());
            throw $e;
        }
    }

    public function addPortfolioCategory($name, $description) {
        try {
            $this->db->beginTransaction();
            
            // Generate slug from name
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            
            $sql = "INSERT INTO {$this->portfolio_categories_table} (name, slug, description, created_at, updated_at) 
                    VALUES (?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$name, $slug, $description]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                throw new Exception("Failed to add portfolio category");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error adding portfolio category: " . $e->getMessage());
            throw $e;
        }
    }

    public function updatePortfolioCategory($id, $name, $description) {
        try {
            $this->db->beginTransaction();
            
            // First check if category exists
            $check_sql = "SELECT id FROM {$this->portfolio_categories_table} WHERE id = ?";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->execute([$id]);
            
            if (!$check_stmt->fetch()) {
                throw new Exception("Portfolio category not found");
            }
            
            // Generate slug from name
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            
            $sql = "UPDATE {$this->portfolio_categories_table} 
                    SET name = ?, slug = ?, description = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$name, $slug, $description, $id]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                throw new Exception("Failed to update portfolio category");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating portfolio category: " . $e->getMessage());
            throw $e;
        }
    }

    public function deletePortfolioCategory($id) {
        try {
            $this->db->beginTransaction();
            
            // First check if category exists
            $check_sql = "SELECT id FROM {$this->portfolio_categories_table} WHERE id = ?";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->execute([$id]);
            
            if (!$check_stmt->fetch()) {
                throw new Exception("Portfolio category not found");
            }
            
            // Check if category has any items
            $items_sql = "SELECT COUNT(*) as count FROM portfolio_items WHERE category_id = ?";
            $items_stmt = $this->db->prepare($items_sql);
            $items_stmt->execute([$id]);
            $items_count = $items_stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($items_count > 0) {
                throw new Exception("Cannot delete category with existing portfolio items");
            }
            
            $sql = "DELETE FROM {$this->portfolio_categories_table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                throw new Exception("Failed to delete portfolio category");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting portfolio category: " . $e->getMessage());
            throw $e;
        }
    }
    
public function sendCampaign($template_id) {
    $stmt = $this->db->prepare("SELECT * FROM newsletter_templates WHERE id = :template_id");
    $stmt->bindParam(':template_id', $template_id, PDO::PARAM_INT);
    $stmt->execute();
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        return false; 
    }

    $subject = $template['subject'];
    $content = $template['content'];

    $stmt = $this->db->prepare("SELECT email FROM newsletter_subscriptions WHERE status = 'active'");
    $stmt->execute();
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($subscribers)) {
        return false; 
    }

    $mail = new PHPMailer(true);
    try {
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'believebrat@gmail.com'; 
        $mail->Password = 'ewzg uxer ufio lrnh'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender info
        $mail->setFrom('believebrat@gmail.com', 'Believe Teckk');
        $mail->addReplyTo('believebrat@gmail.com', 'Believe Teckk');

        // Add recipients (you can loop through all the active subscribers)
        foreach ($subscribers as $subscriber) {
            $mail->addAddress($subscriber['email']);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;

        // Send the email
        if ($mail->send()) {
            return true; // Campaign sent successfully
        } else {
            return false; // Failed to send campaign
        }

    } catch (Exception $e) {
        // Catch any errors
        return false; // Failed to send email
    }
   }
   public function sendTestEmail($test_email, $template_id){
    $stmt = $this->db->prepare("SELECT * FROM newsletter_templates WHERE id = :template_id");
    $stmt->bindParam(':template_id', $template_id, PDO::PARAM_INT);
    $stmt->execute();
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        return false; 
    }

    $subject = $template['subject'];
    $content = $template['content'];


    $mail = new PHPMailer(true);
    try {
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'believebrat@gmail.com'; 
        $mail->Password = 'ewzg uxer ufio lrnh'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;


        $mail->setFrom('believebrat@gmail.com', 'Believe Teckk');
        $mail->addReplyTo('believebrat@gmail.com', 'Believe Teckk');

        
        $mail->addAddress('test_email');
        

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;

        if ($mail->send()) {
            return true; 
        } else {
            return false; 
        }

    } catch (Exception $e) {
        
        return false; 
    }

   }

    /**
     * Add a new training registration
     * @param int $program_id
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function addTrainingRegistration($program_id, $name, $email, $phone, $message = '') {
        try {
            // Validate program exists
            $program = $this->getTrainingProgram($program_id);
            if (!$program) {
                throw new Exception("Training program not found");
            }

            // Check for existing registration
            $existing = $this->getRegistrationByEmail($email, $program_id);
            if ($existing) {
                throw new Exception("You have already registered for this program");
            }

            $sql = "INSERT INTO {$this->registration_table} (
                program_id, 
                name, 
                email, 
                phone, 
                message,
                status,
                created_at
            ) VALUES (
                :program_id,
                :name,
                :email,
                :phone,
                :message,
                'pending',
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            $params = [
                ':program_id' => $program_id,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':message' => $message
            ];

            $result = $stmt->execute($params);

            if ($result) {
                // Send confirmation email
                $this->sendRegistrationConfirmation($email, $name, $program['title']);
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Error adding training registration: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get registration by email and program
     * @param string $email
     * @param int $program_id
     * @return array|false
     */
    private function getRegistrationByEmail($email, $program_id) {
        try {
            $sql = "SELECT * FROM {$this->registration_table} 
                    WHERE email = :email AND program_id = :program_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':program_id' => $program_id
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting registration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send registration confirmation email
     * @param string $email
     * @param string $name
     * @param string $program_title
     * @return bool
     */
    private function sendRegistrationConfirmation($email, $name, $program_title) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'believebrat@gmail.com';
            $mail->Password = 'ewzg uxer ufio lrnh';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('believebrat@gmail.com', 'Believe Teckk');

            $mail->addAddress($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Training Registration Confirmation';
            
            $body = "
                <h2>Thank you for registering!</h2>
                <p>Dear {$name},</p>
                <p>Your registration for the training program <strong>{$program_title}</strong> has been received.</p>
                <p>We will contact you shortly with further details.</p>
                <p>Best regards,<br>Believe Teckk Team</p>
            ";
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending registration confirmation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all training registrations with program details
     * @return array
     */
    public function getAllTrainingRegistrations() {
        try {
            $sql = "SELECT r.*, p.title as program_title, p.duration, p.price 
                    FROM {$this->registration_table} r 
                    LEFT JOIN {$this->training_table} p ON r.program_id = p.id 
                    ORDER BY r.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting training registrations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update registration status
     * @param int $registration_id
     * @param string $status
     * @return bool
     */
    public function updateRegistrationStatus($registration_id, $status) {
        try {
            $this->db->beginTransaction();

            // Get registration details first
            $registration = $this->getRegistrationById($registration_id);
            if (!$registration) {
                throw new Exception("Registration not found");
            }

            // Get program details
            $program = $this->getTrainingProgram($registration['program_id']);
            if (!$program) {
                throw new Exception("Training program not found");
            }

            // Update status
            $sql = "UPDATE {$this->registration_table} 
                    SET status = :status, updated_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':id' => $registration_id,
                ':status' => $status
            ]);

            if ($result && $status === 'confirm') {
                // Send approval email with WhatsApp link
                $this->sendApprovalEmail($registration, $program);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating registration status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get registration by ID
     * @param int $id
     * @return array|false
     */
    private function getRegistrationById($id) {
        try {
            $sql = "SELECT * FROM {$this->registration_table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting registration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send approval email with WhatsApp link
     * @param array $registration
     * @param array $program
     * @return bool
     */
    private function sendApprovalEmail($registration, $program) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true; 
            $mail->Username = 'believebrat@gmail.com';
            $mail->Password = 'ewzg uxer ufio lrnh';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('believebrat@gmail.com', 'Believe Teckk');
            $mail->addAddress($registration['email'], $registration['name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Training Registration Approved - ' . $program['title'];
            
            $body = "
                <h2>Registration Approved!</h2>
                <p>Dear {$registration['name']},</p>
                <p>Your registration for the training program <strong>{$program['title']}</strong> has been approved!</p>
                <p><strong>Program Details:</strong></p>
                <ul>
                    <li>Title: {$program['title']}</li>
                    <li>Duration: {$program['duration']}</li>
                    <li>Price: {$program['price']}</li>
                </ul>
                <p>To join the training program, please click the WhatsApp link below:</p>
                <p><a href='{$program['whatsapp_group']}' style='background-color: #25D366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Join WhatsApp Group</a></p>
                <p>If you have any questions, please don't hesitate to contact us.</p>
                <p>Best regards,<br>Believe Teckk Team</p>
            ";
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Error sending approval email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get registration statistics
     * @return array
     */
    public function getRegistrationStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_registrations,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed,
                        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
                    FROM {$this->registration_table}";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting registration stats: " . $e->getMessage());
            return [
                'total_registrations' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'cancelled' => 0
            ];
        }
    }

    /**
     * Get registrations by program
     * @param int $program_id
     * @return array
     */
    public function getRegistrationsByProgram($program_id) {
        try {
            $sql = "SELECT r.*, p.title as program_title 
                    FROM {$this->registration_table} r 
                    LEFT JOIN {$this->training_table} p ON r.program_id = p.id 
                    WHERE r.program_id = :program_id 
                    ORDER BY r.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':program_id' => $program_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting program registrations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export registrations to CSV
     * @param array $registrations
     * @return string
     */
    public function exportRegistrationsToCSV($registrations) {
        $output = fopen('php://temp', 'r+');
        
        // Add CSV headers
        fputcsv($output, [
            'ID',
            'Program',
            'Name',
            'Email',
            'Phone',
            'Message',
            'Status',
            'Registration Date'
        ]);

        // Add data rows
        foreach ($registrations as $registration) {
            fputcsv($output, [
                $registration['id'],
                $registration['program_title'],
                $registration['name'],
                $registration['email'],
                $registration['phone'],
                $registration['message'],
                $registration['status'],
                $registration['created_at']
            ]);
        }

        // Get the CSV content
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
    public function deleteRegistration($id) {
        try {
            $sql = "DELETE FROM {$this->registration_table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting registration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all inquiries
     * @return array
     */
    public function getInquiries() {
        $sql = "SELECT * FROM inquiries ORDER BY created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get a single inquiry by ID
     * @param int $id
     * @return array|false
     */
    public function getInquiry($id) {
        $sql = "SELECT * FROM inquiries WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Update inquiry status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateInquiryStatus($id, $status) {
        $sql = "UPDATE inquiries SET status = ? WHERE id = ?";
        return $this->db->prepare($sql)->execute([$status, $id]);
    }

    /**
     * Delete an inquiry
     * @param int $id
     * @return bool
     */
    public function deleteInquiry($id) {
        $sql = "DELETE FROM inquiries WHERE id = ?";
        return $this->db->prepare($sql)->execute([$id]);
    }

    /**
     * Get all team members
     * @return array
     */
    public function getTeamMembers() {
        try {
            $sql = "SELECT * FROM team_members WHERE is_active = 1 ORDER BY order_index ASC, created_at DESC";
            error_log("Executing query: " . $sql);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($result) . " team members");
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting team members: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single team member by ID
     * @param int $id
     * @return array|false
     */
    public function getTeamMember($id) {
        try {
            $sql = "SELECT * FROM team_members WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting team member: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a new team member
     * @param string $name
     * @param string $position
     * @param string $bio
     * @param string $image_url
     * @param string $linkedin_url
     * @param string $twitter_url
     * @param int $order_index
     * @return bool
     */
    public function addTeamMember($name, $position, $bio, $image_url, $linkedin_url = null, $twitter_url = null, $order_index = 0) {
        try {
            error_log("Adding team member: " . json_encode([
                'name' => $name,
                'position' => $position,
                'bio' => substr($bio, 0, 100) . '...',
                'image' => $image_url,
                'linkedin_url' => $linkedin_url,
                'twitter_url' => $twitter_url,
                'order_index' => $order_index
            ]));

            $sql = "INSERT INTO team_members (name, position, bio, image, linkedin_url, twitter_url, order_index) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$name, $position, $bio, $image_url, $linkedin_url, $twitter_url, $order_index]);
            
            if ($result) {
                error_log("Successfully added team member with ID: " . $this->db->lastInsertId());
                return true;
            } else {
                error_log("Failed to add team member. Error info: " . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error adding team member: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a team member
     * @param int $id
     * @param string $name
     * @param string $position
     * @param string $bio
     * @param string $image
     * @param string $linkedin_url
     * @param string $twitter_url
     * @param int $order_index
     * @return bool
     */
    public function updateTeamMember($id, $name, $position, $bio, $image = null, $linkedin_url = null, $twitter_url = null, $order_index = null) {
        try {
            error_log("Updating team member ID " . $id . " with data: " . json_encode([
                'name' => $name,
                'position' => $position,
                'bio' => substr($bio, 0, 100) . '...',
                'image' => $image,
                'linkedin_url' => $linkedin_url,
                'twitter_url' => $twitter_url,
                'order_index' => $order_index
            ]));

            $params = [$name, $position, $bio];
            $sql = "UPDATE team_members SET name = ?, position = ?, bio = ?";
            
            if ($image !== null) {
                $sql .= ", image = ?";
                $params[] = $image;
            }
            if ($linkedin_url !== null) {
                $sql .= ", linkedin_url = ?";
                $params[] = $linkedin_url;
            }
            if ($twitter_url !== null) {
                $sql .= ", twitter_url = ?";
                $params[] = $twitter_url;
            }
            if ($order_index !== null) {
                $sql .= ", order_index = ?";
                $params[] = $order_index;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                error_log("Successfully updated team member ID: " . $id);
                return true;
            } else {
                error_log("Failed to update team member. Error info: " . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error updating team member: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a team member
     * @param int $id
     * @return bool
     */
    public function deleteTeamMember($id) {
        try {
            error_log("Deleting team member ID: " . $id);
            
            $sql = "DELETE FROM team_members WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                error_log("Successfully deleted team member ID: " . $id);
                return true;
            } else {
                error_log("Failed to delete team member. Error info: " . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error deleting team member: " . $e->getMessage());
            return false;
        }
    }

    public function getTeamMemberById($id) {
        try {
            error_log("Getting team member ID: " . $id);
            
            $sql = "SELECT * FROM team_members WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("Found team member: " . json_encode($result));
            } else {
                error_log("No team member found with ID: " . $id);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting team member by ID: " . $e->getMessage());
            return false;
        }
    }

    public function toggleTeamMemberStatus($id) {
        try {
            $sql = "UPDATE team_members SET is_active = NOT is_active WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error toggling team member status: " . $e->getMessage());
            return false;
        }
    }

    public function updateTeamMemberOrder($id, $order_index) {
        try {
            $sql = "UPDATE team_members SET order_index = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$order_index, $id]);
        } catch (PDOException $e) {
            error_log("Error updating team member order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all admin users
     * @return array List of admin users
     */
    public function getAllAdmins() {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, created_at 
                FROM admins 
                ORDER BY username ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching admins: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all pending enrollments with student and course details
     * @return array Array of pending enrollments
     */
    public function getPendingEnrollments() {
        try {
            $sql = "SELECT 
                    enrollments.*,
                    students.name as student_name,
                    students.email as student_email,
                    courses.title as course_title,
                    courses.price as course_price
                FROM enrollments
                JOIN students ON enrollments.student_id = students.id
                JOIN courses ON enrollments.course_id = courses.id
                WHERE enrollments.payment_status = 'pending'
                ORDER BY enrollments.enrollment_date DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting pending enrollments: " . $e->getMessage());
            return [];
        }
    }

    public function updateEnrollmentStatus($enrollment_id, $status) {
        $sql = "UPDATE enrollments SET payment_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'id' => $enrollment_id
        ]);
    }
}