<?php
class Job extends Model {
    protected $table = 'jobs';
    protected $career_table = 'careers';
    
    public function getAllCareers() {
        $sql = "SELECT * FROM {$this->career_table} ORDER BY title ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllJobs() {
        $sql = "SELECT j.*, c.title as career_title 
                FROM {$this->table} j 
                LEFT JOIN careers c ON j.career_id = c.id 
                ORDER BY j.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllActiveJobs() {
        $sql = "SELECT j.*, c.title as career_title 
                FROM {$this->table} j 
                LEFT JOIN careers c ON j.career_id = c.id 
                WHERE j.is_active = 1 
                ORDER BY j.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getJobById($id) {
        $sql = "SELECT j.*, c.title as career_title 
                FROM {$this->table} j 
                LEFT JOIN careers c ON j.career_id = c.id 
                WHERE j.id = ? AND j.is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createJob($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = 1;
        return parent::create($data);
    }
    
    public function updateJob($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }
    
    public function deleteJob($id) {
        return parent::delete($id);
    }
    
    public function toggleActive($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
} 