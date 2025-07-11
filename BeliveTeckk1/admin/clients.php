<?php
require_once __DIR__ .'/../config/config.php';
require_once __DIR__ .'/../classes/Admin.php';
require_once __DIR__ . '/../classes/Database.php';

$db = getDBConnection();

// Handle form submission for adding/editing clients
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $name = trim($_POST['name']);
            $website = trim($_POST['website']);
            $status = $_POST['status'];
            
            // Handle logo upload
            $logo_url = '';
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                $upload_dir = '../uploads/clients/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = uniqid() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    $logo_url = 'uploads/clients/' . $file_name;
                }
            } elseif (isset($_POST['existing_logo'])) {
                $logo_url = $_POST['existing_logo'];
            }
            
            try {
                if ($_POST['action'] === 'add') {
                    $stmt = $db->prepare("INSERT INTO clients (name, logo, website, status) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $logo_url, $website, $status]);
                    $_SESSION['success_message'] = "Client added successfully!";
                } else {
                    $id = (int)$_POST['id'];
                    $stmt = $db->prepare("UPDATE clients SET name = ?, logo = ?, website = ?, status = ? WHERE id = ?");
                    $stmt->execute([$name, $logo_url, $website, $status, $id]);
                    $_SESSION['success_message'] = "Client updated successfully!";
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            try {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_message'] = "Client deleted successfully!";
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_status' && isset($_POST['id']) && isset($_POST['status'])) {
            try {
                $id = (int)$_POST['id'];
                $status = $_POST['status'];
                $stmt = $db->prepare("UPDATE clients SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                $_SESSION['success_message'] = "Client status updated successfully!";
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: clients.php");
    exit;
}

// Get all clients
try {
    $stmt = $db->query("SELECT * FROM clients ORDER BY created_at DESC");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    $clients = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients - Believe Teckk</title>
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
            <h1 class="mt-4 mb-4 text-2xl font-bold">Manage Clients</h1>
            
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
                    <i class="fas fa-building mr-2"></i>
                    Add New Client
                </div>
                <div class="p-4">
                    <form action="clients.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="name" name="name" required>
                            </div>
                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                                <input type="url" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="website" name="website" placeholder="https://example.com">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Client Logo</label>
                                <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded-md" id="logo" name="logo">
                                <small class="text-gray-500">Recommended size: 200x100px</small>
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
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Client</button>
                    </form>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="bg-gray-200 px-4 py-3 rounded-t-lg">
                    <i class="fas fa-table mr-2"></i>
                    Clients List
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table id="clientsTable" class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 text-left">ID</th>
                                    <th class="py-2 px-4 text-left">Name</th>
                                    <th class="py-2 px-4 text-left">Logo</th>
                                    <th class="py-2 px-4 text-left">Website</th>
                                    <th class="py-2 px-4 text-left">Status</th>
                                    <th class="py-2 px-4 text-left">Date Added</th>
                                    <th class="py-2 px-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                <tr class="border-b">
                                    <td class="py-2 px-4"><?php echo $client['id']; ?></td>
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($client['name']); ?></td>
                                    <td class="py-2 px-4">
                                        <?php if (!empty($client['logo'])): ?>
                                            <img src="../<?php echo htmlspecialchars($client['logo']); ?>" alt="Logo" style="max-height: 50px;">
                                        <?php else: ?>
                                            No Logo
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4">
                                        <?php if (!empty($client['website'])): ?>
                                            <a href="<?php echo htmlspecialchars($client['website']); ?>" target="_blank" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($client['website']); ?></a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4">
                                        <form action="clients.php" method="POST" class="status-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                            <select name="status" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $client['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="approved" <?php echo $client['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $client['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="py-2 px-4"><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                                    <td class="py-2 px-4">
                                        <button type="button" class="px-2 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 edit-client-btn" 
                                                data-id="<?php echo $client['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($client['name']); ?>"
                                                data-website="<?php echo htmlspecialchars($client['website'] ?? ''); ?>"
                                                data-logo="<?php echo htmlspecialchars($client['logo'] ?? ''); ?>"
                                                data-status="<?php echo htmlspecialchars($client['status']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#editClientModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 delete-client-btn" 
                                                data-id="<?php echo $client['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($client['name']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#deleteClientModal">
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

    <!-- Edit Client Modal -->
    <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="clients.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="existing_logo" id="existing_logo">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Client Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_website" class="form-label">Website URL</label>
                            <input type="url" class="form-control" id="edit_website" name="website" placeholder="https://example.com">
                        </div>
                        <div class="mb-3">
                            <label for="edit_logo" class="form-label">Client Logo</label>
                            <input type="file" class="form-control" id="edit_logo" name="logo">
                            <small class="text-muted">Leave empty to keep current logo</small>
                            <div id="current_logo_preview" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-control" id="edit_status" name="status">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
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

    <!-- Delete Client Modal -->
    <div class="modal fade" id="deleteClientModal" tabindex="-1" aria-labelledby="deleteClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteClientModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the client: <span id="delete_client_name"></span>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="clients.php" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#clientsTable').DataTable();
        
        // Edit Client Modal
        const editButtons = document.querySelectorAll('.edit-client-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const website = this.getAttribute('data-website');
                const logo = this.getAttribute('data-logo');
                const status = this.getAttribute('data-status');
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_website').value = website;
                document.getElementById('edit_status').value = status;
                document.getElementById('existing_logo').value = logo;
                
                const logoPreview = document.getElementById('current_logo_preview');
                if (logo) {
                    logoPreview.innerHTML = `<img src="../${logo}" alt="Current Logo" style="max-height: 100px;">`;
                } else {
                    logoPreview.innerHTML = 'No logo currently set';
                }
            });
        });
        
        // Delete Client Modal
        const deleteButtons = document.querySelectorAll('.delete-client-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_id').value = id;
                document.getElementById('delete_client_name').textContent = name;
            });
        });
    });
    </script>
</body>
</html>

