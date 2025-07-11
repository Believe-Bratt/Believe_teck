<?php
class Blog extends Model {
    protected $table = 'blog_posts';
    
    public function getAllPosts($limit = null, $offset = null) {
        $sql = "SELECT bp.*, bc.name as category_name, a.username as author_name 
                FROM {$this->table} bp 
                LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
                LEFT JOIN admins a ON bp.author_id = a.id 
                WHERE bp.status = 'published' 
                ORDER BY bp.published_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
        
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPostById($id) {
        $stmt = $this->db->prepare(
            "SELECT bp.*, bc.name as category_name, a.username as author_name 
             FROM {$this->table} bp 
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
             LEFT JOIN admins a ON bp.author_id = a.id 
             WHERE bp.id = ? AND bp.status = 'published'"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getPostsByCategory($category_id, $limit = null) {
        $sql = "SELECT bp.*, bc.name as category_name, a.username as author_name 
                FROM {$this->table} bp 
                LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
                LEFT JOIN admins a ON bp.author_id = a.id 
                WHERE bp.category_id = ? AND bp.status = 'published' 
                ORDER BY bp.published_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategories() {
        $stmt = $this->db->prepare("SELECT * FROM blog_categories ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createPost($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($data['status'] === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        return parent::create($data);
    }
    
    public function updatePost($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($data['status'] === 'published' && !isset($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        return parent::update($id, $data);
    }
    
    public function deletePost($id) {
        return parent::delete($id);
    }
} 