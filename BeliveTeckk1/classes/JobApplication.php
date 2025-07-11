<?php
class JobApplication extends Model {
    protected $table = 'job_applications';
    
    public function createApplication($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'pending';
        return parent::create($data);
    }
    
    public function getApplicationsByJob($job_id) {
        $sql = "SELECT ja.*, j.title as job_title 
                FROM {$this->table} ja 
                LEFT JOIN jobs j ON ja.job_id = j.id 
                WHERE ja.job_id = ? 
                ORDER BY ja.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$job_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }
    
    public function getApplicationById($id) {
        $sql = "SELECT ja.*, j.title as job_title, j.location, j.type 
                FROM {$this->table} ja 
                LEFT JOIN jobs j ON ja.job_id = j.id 
                WHERE ja.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllApplications() {
        $sql = "SELECT ja.*, j.title as job_title, j.type as job_type 
                FROM {$this->table} ja 
                LEFT JOIN jobs j ON ja.job_id = j.id 
                ORDER BY ja.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteApplication($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
} 