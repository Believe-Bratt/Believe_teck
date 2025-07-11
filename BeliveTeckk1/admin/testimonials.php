<?php
require_once __DIR__ .'/../config/config.php';
require_once __DIR__ .'/../classes/Admin.php';
require_once __DIR__ . '/../classes/Database.php';

$db = getDBConnection();

// Handle form submission for adding/editing testimonials
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $client_name = trim($_POST['client_name']);
            $client_position = trim($_POST['client_position']);
            $company_name = trim($_POST['company_name']);
            $testimonial = trim($_POST['testimonial']);
            $rating = (int)$_POST['rating'];
            $status = $_POST['status'];
            
            // Handle image upload
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = '../uploads/testimonials/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = 'uploads/testimonials/' . $file_name;
                }
            } elseif (isset($_POST['existing_image'])) {
                $image_url = $_POST['existing_image'];
            }
            
            try {
                if ($_POST['action'] === 'add') {
                    $stmt = $db->prepare("INSERT INTO testimonials (client_name, client_position, company_name, testimonial, rating, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$client_name, $client_position, $company_name, $testimonial, $rating, $image_url, $status]);
                    $_SESSION['success_message'] = "Testimonial added successfully!";
                } else {
                    $id = (int)$_POST['id'];
                    $stmt = $db->prepare("UPDATE testimonials SET client_name = ?, client_position = ?, company_name = ?, testimonial = ?, rating = ?, image_url = ?, status = ? WHERE id = ?");
                    $stmt->execute([$client_name, $client_position, $company_name, $testimonial, $rating, $image_url, $status, $id]);
                    $_SESSION['success_message'] = "Testimonial updated successfully!";
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            try {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_message'] = "Testimonial deleted successfully!";
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_status' && isset($_POST['id']) && isset($_POST['status'])) {
            try {
                $id = (int)$_POST['id'];
                $status = $_POST['status'];
                $stmt = $db->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                $_SESSION['success_message'] = "Testimonial status updated successfully!";
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: testimonials.php");
    exit;
}

// Get all testimonials
try {
    $stmt = $db->query("SELECT * FROM testimonials ORDER BY created_at DESC");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    $testimonials = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials - Believe Teckk</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 250px; /* Adjust based on your sidebar width */
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mt-4 mb-4 text-2xl font-bold">Manage Testimonials</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="bg-gray-200 px-4 py-3 rounded-t-lg">
                    <i class="fas fa-quote-left mr-2"></i>
                    Add New Testimonial
                </div>
                <div class="p-4">
                    <form action="testimonials.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="client_name" class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="client_name" name="client_name" required>
                            </div>
                            <div>
                                <label for="client_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="client_position" name="client_position">
                            </div>
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="company_name" name="company_name">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="testimonial" class="block text-sm font-medium text-gray-700 mb-1">Testimonial</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md" id="testimonial" name="testimonial" rows="4" required></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Rating (1-5)</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md" id="rating" name="rating">
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Client Image</label>
                                <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="image" name="image">
                                <small class="text-gray-500">Recommended size: 100x100px</small>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md" id="status" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Testimonial</button>
                    </form>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="bg-gray-200 px-4 py-3 rounded-t-lg">
                    <i class="fas fa-table mr-2"></i>
                    Testimonials List
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table id="testimonialsTable" class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 text-left">ID</th>
                                    <th class="py-2 px-4 text-left">Client</th>
                                    <th class="py-2 px-4 text-left">Company</th>
                                    <th class="py-2 px-4 text-left">Testimonial</th>
                                    <th class="py-2 px-4 text-left">Rating</th>
                                    <th class="py-2 px-4 text-left">Status</th>
                                    <th class="py-2 px-4 text-left">Date</th>
                                    <th class="py-2 px-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($testimonials as $testimonial): ?>
                                <tr class="border-b">
                                    <td class="py-2 px-4"><?php echo $testimonial['id']; ?></td>
                                    <td class="py-2 px-4">
                                        <?php echo htmlspecialchars($testimonial['client_name']); ?>
                                        <?php if (!empty($testimonial['image_url'])): ?>
                                            <br><img src="../<?php echo htmlspecialchars($testimonial['image_url']); ?>" alt="Client" class="mt-1" style="max-height: 50px; border-radius: 50%;">
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4">
                                        <?php if (!empty($testimonial['company_name'])): ?>
                                            <?php echo htmlspecialchars($testimonial['company_name']); ?>
                                            <?php if (!empty($testimonial['client_position'])): ?>
                                                <br><small class="text-gray-500"><?php echo htmlspecialchars($testimonial['client_position']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4"><?php echo nl2br(htmlspecialchars(substr($testimonial['testimonial'], 0, 100) . (strlen($testimonial['testimonial']) > 100 ? '...' : ''))); ?></td>
                                    <td class="py-2 px-4">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $testimonial['rating']): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-warning"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </td>
                                    <td class="py-2 px-4">
                                        <form action="testimonials.php" method="POST" class="status-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                            <select name="status" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $testimonial['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="approved" <?php echo $testimonial['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $testimonial['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="py-2 px-4"><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></td>
                                    <td class="py-2 px-4">
                                        <button type="button" class="px-2 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 edit-testimonial-btn" 
                                                data-id="<?php echo $testimonial['id']; ?>"
                                                data-client-name="<?php echo htmlspecialchars($testimonial['client_name']); ?>"
                                                data-client-position="<?php echo htmlspecialchars($testimonial['client_position'] ?? ''); ?>"
                                                data-company-name="<?php echo htmlspecialchars($testimonial['company_name'] ?? ''); ?>"
                                                data-testimonial="<?php echo htmlspecialchars($testimonial['testimonial']); ?>"
                                                data-rating="<?php echo $testimonial['rating']; ?>"
                                                data-image="<?php echo htmlspecialchars($testimonial['image_url'] ?? ''); ?>"
                                                data-status="<?php echo htmlspecialchars($testimonial['status']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#editTestimonialModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 delete-testimonial-btn" 
                                                data-id="<?php echo $testimonial['id']; ?>"
                                                data-client-name="<?php echo htmlspecialchars($testimonial['client_name']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#deleteTestimonialModal">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Edit Testimonial Modal -->
<div class="modal fade" id="editTestimonialModal" tabindex="-1" aria-labelledby="editTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTestimonialModalLabel">Edit Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="testimonials.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="existing_image" id="existing_image">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_client_name" class="form-label">Client Name</label>
                                <input type="text" class="form-control" id="edit_client_name" name="client_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_client_position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="edit_client_position" name="client_position">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_company_name" class="form-label">Company</label>
                                <input type="text" class="form-control" id="edit_company_name" name="company_name">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_testimonial" class="form-label">Testimonial</label>
                        <textarea class="form-control" id="edit_testimonial" name="testimonial" rows="4" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_rating" class="form-label">Rating (1-5)</label>
                                <select class="form-control" id="edit_rating" name="rating">
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_image" class="form-label">Client Image</label>
                                <input type="file" class="form-control" id="edit_image" name="image">
                                <small class="text-muted">Leave empty to keep current image</small>
                                <div id="current_image_preview" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Testimonial Modal -->
<div class="modal fade" id="deleteTestimonialModal" tabindex="-1" aria-labelledby="deleteTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTestimonialModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the testimonial from <span id="delete_client_name"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="testimonials.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap and jQuery JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#testimonialsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']]
        });
        
        // Handle edit testimonial button click
        $('.edit-testimonial-btn').on('click', function() {
            const id = $(this).data('id');
            const clientName = $(this).data('client-name');
            const clientPosition = $(this).data('client-position');
            const companyName = $(this).data('company-name');
            const testimonialText = $(this).data('testimonial');
            const rating = $(this).data('rating');
            const image = $(this).data('image');
            const status = $(this).data('status');
            
            // Populate the edit form fields
            $('#edit_id').val(id);
            $('#edit_client_name').val(clientName);
            $('#edit_client_position').val(clientPosition);
            $('#edit_company_name').val(companyName);
            $('#edit_testimonial').val(testimonialText);
            $('#edit_rating').val(rating);
            $('#edit_status').val(status);
            $('#existing_image').val(image);
            
            // Show current image if available
            if (image) {
                $('#current_image_preview').html(`<img src="../${image}" alt="Current Image" style="max-height: 100px; border-radius: 50%;" class="mt-2">`);
            } else {
                $('#current_image_preview').html('No image uploaded');
            }
        });
        
        // Handle delete testimonial button click
        $('.delete-testimonial-btn').on('click', function() {
            const id = $(this).data('id');
            const clientName = $(this).data('client-name');
            
            $('#delete_id').val(id);
            $('#delete_client_name').text(clientName);
        });
    });
</script>
</body>
</html>

