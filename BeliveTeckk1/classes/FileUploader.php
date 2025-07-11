<?php
class FileUploader {
    private $uploadDir;
    private $maxFileSize;
    private $allowedTypes;
    private $errors = [];
    
    public function __construct(
        $uploadDir = UPLOAD_DIR,
        $maxFileSize = MAX_FILE_SIZE,
        $allowedTypes = ALLOWED_FILE_TYPES
    ) {
        $this->uploadDir = $uploadDir;
        $this->maxFileSize = $maxFileSize;
        $this->allowedTypes = $allowedTypes;
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    }
    
    public function upload($file, $subDir = '') {
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->errors[] = 'Invalid file parameter';
            return false;
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'File exceeds maximum size';
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->errors[] = 'File was only partially uploaded';
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file was uploaded';
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->errors[] = 'Missing temporary folder';
                return false;
            case UPLOAD_ERR_CANT_WRITE:
                $this->errors[] = 'Failed to write file to disk';
                return false;
            case UPLOAD_ERR_EXTENSION:
                $this->errors[] = 'A PHP extension stopped the file upload';
                return false;
            default:
                $this->errors[] = 'Unknown upload error';
                return false;
        }
        
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = 'File exceeds maximum size';
            return false;
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            $this->errors[] = 'Invalid file type';
            return false;
        }
        
        $fileName = sprintf(
            '%s.%s',
            sha1_file($file['tmp_name']),
            $extension
        );
        
        $uploadPath = $this->uploadDir . $subDir . '/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $this->errors[] = 'Failed to move uploaded file';
            return false;
        }
        
        return $fileName;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function delete($fileName) {
        $filePath = $this->uploadDir . $fileName;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    public function getUploadUrl($fileName) {
        return SITE_URL . '/uploads/' . $fileName;
    }
    
    public function validateImage($file) {
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedImageTypes)) {
            $this->errors[] = 'Invalid image type';
            return false;
        }
        
        return true;
    }
    public function uploadImage($file, $subDir = '') {
        return $this->upload($file, $subDir);
    }
    
    
    public function resizeImage($sourcePath, $targetPath, $maxWidth, $maxHeight) {
        list($width, $height) = getimagesize($sourcePath);
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        
        if ($ratio < 1) {
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            switch (mime_content_type($sourcePath)) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($sourcePath);
                    break;
                default:
                    return false;
            }
            
            imagecopyresampled(
                $newImage,
                $source,
                0, 0, 0, 0,
                $newWidth,
                $newHeight,
                $width,
                $height
            );
            
            switch (mime_content_type($sourcePath)) {
                case 'image/jpeg':
                    imagejpeg($newImage, $targetPath, 90);
                    break;
                case 'image/png':
                    imagepng($newImage, $targetPath, 9);
                    break;
                case 'image/gif':
                    imagegif($newImage, $targetPath);
                    break;
            }
            
            imagedestroy($newImage);
            imagedestroy($source);
        } else {
            copy($sourcePath, $targetPath);
        }
        
        return true;
    }
} 