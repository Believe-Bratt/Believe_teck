<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Training extends Model {
    protected $table = 'training_programs';
    protected $registration_table = 'training_registrations';
    
    private $db;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->db = $db;
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
    public function addRegistration($program_id, $name, $email, $phone, $message = '') {
        try {
            // Validate program exists
            $program = $this->getProgram($program_id);
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
     * Get all training programs
     * @return array
     */
    public function getAllPrograms() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting training programs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a specific training program
     * @param int $id
     * @return array|false
     */
    public function getProgram($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting training program: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a new training program
     * @param array $data
     * @return int|false
     */
    public function addProgram($data) {
        try {
            $sql = "INSERT INTO {$this->table} (
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
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':duration' => $data['duration'],
                ':price' => $data['price'],
                ':whatsapp_group' => $data['whatsapp_group']
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error adding training program: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a training program
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProgram($id, $data) {
        try {
            $sql = "UPDATE {$this->table} SET 
                    title = :title,
                    description = :description,
                    duration = :duration,
                    price = :price,
                    whatsapp_group = :whatsapp_group,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':duration' => $data['duration'],
                ':price' => $data['price'],
                ':whatsapp_group' => $data['whatsapp_group']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating training program: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a training program
     * @param int $id
     * @return bool
     */
    public function deleteProgram($id) {
        try {
            // Check if program has any registrations
            $registrations = $this->getRegistrationsByProgram($id);
            if (!empty($registrations)) {
                throw new Exception("Cannot delete program with existing registrations");
            }

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting training program: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all registrations for a program
     * @param int $program_id
     * @return array
     */
    public function getRegistrationsByProgram($program_id) {
        try {
            $sql = "SELECT r.*, p.title as program_title 
                    FROM {$this->registration_table} r 
                    LEFT JOIN {$this->table} p ON r.program_id = p.id 
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
     * Update registration status
     * @param int $registration_id
     * @param string $status
     * @return bool
     */
    public function updateRegistrationStatus($registration_id, $status) {
        try {
            $sql = "UPDATE {$this->registration_table} 
                    SET status = :status, updated_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $registration_id,
                ':status' => $status
            ]);
        } catch (PDOException $e) {
            error_log("Error updating registration status: " . $e->getMessage());
            return false;
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
} 