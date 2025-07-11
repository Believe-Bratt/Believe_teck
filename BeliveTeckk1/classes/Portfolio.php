<?php
class Portfolio extends Model {
    protected $table = 'portfolio_items';
    protected $category_table = 'portfolio_categories';
    
    public function getAllItems() {
        $sql = "SELECT pi.*, pc.name as category_name 
                FROM {$this->table} pi 
                LEFT JOIN {$this->category_table} pc ON pi.category_id = pc.id 
                ORDER BY pi.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getItemById($id) {
        $sql = "SELECT pi.*, pc.name as category_name 
                FROM {$this->table} pi 
                LEFT JOIN {$this->category_table} pc ON pi.category_id = pc.id 
                WHERE pi.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getItemsByCategory($category_id) {
        $sql = "SELECT pi.*, pc.name as category_name 
                FROM {$this->table} pi 
                LEFT JOIN {$this->category_table} pc ON pi.category_id = pc.id 
                WHERE pi.category_id = ? AND pi.is_active = 1
                ORDER BY pi.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategories() {
        $sql = "SELECT * FROM {$this->category_table} ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategoryById($id) {
        $sql = "SELECT * FROM {$this->category_table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createItem($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }
    
    public function updateItem($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }
    
    public function deleteItem($id) {
        return parent::delete($id);
    }
    
    public function toggleActive($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function toggleFeatured($id) {
        $sql = "UPDATE {$this->table} SET is_featured = NOT is_featured WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function createCategory($data) {
        $sql = "INSERT INTO {$this->category_table} (name, description, slug, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['slug'],
            $data['is_active']
        ]);
    }
    
    public function updateCategory($id, $data) {
        $sql = "UPDATE {$this->category_table} 
                SET name = ?, description = ?, slug = ?, is_active = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['slug'],
            $data['is_active'],
            $id
        ]);
    }
    
    public function deleteCategory($id) {
        // Check if category has any items
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE category_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return false; // Cannot delete category with items
        }
        
        $sql = "DELETE FROM {$this->category_table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function toggleCategoryActive($id) {
        $sql = "UPDATE {$this->category_table} SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
} 