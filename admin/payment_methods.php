<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}



// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add payment method
    if (isset($_POST['add_payment_method'])) {
        try {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $value = trim($_POST['value']);
            $link = trim($_POST['link']);
            $token_key = trim($_POST['token_key']);
            $active = 1; // Default to active when adding new method
            
            // Handle image upload
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/payment_methods/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image = $destination;
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO payment_methods (name, image, description, value, link, token_key, active) 
                                   VALUES (:name, :image, :description, :value, :link, :token_key, :active)");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':link', $link);
            $stmt->bindParam(':token_key', $token_key);
            $stmt->bindParam(':active', $active);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Payment method added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add payment method';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
        header("Location: payment_methods.php");
        exit();
    }
    
    // Update payment method
    if (isset($_POST['update_payment_method'])) {
        try {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $value = trim($_POST['value']);
            $link = trim($_POST['link']);
            $token_key = trim($_POST['token_key']);
            $current_image = $_POST['current_image'] ?? null;
            
            // Handle image upload
            $image = $current_image;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/payment_methods/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Delete old image if it exists
                if ($current_image && file_exists($current_image)) {
                    unlink($current_image);
                }
                
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image = $destination;
                }
            }
            
            $stmt = $conn->prepare("UPDATE payment_methods SET 
                                  name = :name,
                                  image = :image,
                                  description = :description,
                                  value = :value,
                                  link = :link,
                                  token_key = :token_key
                                  WHERE id = :id");
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':link', $link);
            $stmt->bindParam(':token_key', $token_key);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Payment method updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update payment method';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
        header("Location: payment_methods.php");
        exit();
    }
    
    // Handle toggle status
    if (isset($_POST['toggle_method'])) {
        try {
            $id = $_POST['id'];
            $current_status = $_POST['current_status'];
            $new_status = $current_status ? 0 : 1;
            
            $stmt = $conn->prepare("UPDATE payment_methods SET active = :active WHERE id = :id");
            $stmt->bindParam(':active', $new_status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Payment method status updated';
            } else {
                $_SESSION['error'] = 'Failed to update status';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
        header("Location: payment_methods.php");
        exit();
    }
    
    // Handle delete payment method
    if (isset($_POST['delete_payment_method'])) {
        try {
            $id = $_POST['id'];
            
            // First get the image path to delete the file
            $stmt = $conn->prepare("SELECT image FROM payment_methods WHERE id = ?");
            $stmt->execute([$id]);
            $method = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete the image file if it exists
            if ($method && $method['image'] && file_exists($method['image'])) {
                unlink($method['image']);
            }
            
            // Then delete the record
            $stmt = $conn->prepare("DELETE FROM payment_methods WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['message'] = 'Payment method deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete payment method';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
        header("Location: payment_methods.php");
        exit();
    }
}

// Fetch all payment methods
$payment_methods = $conn->query("SELECT * FROM payment_methods ORDER BY active DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Payment Methods';
$current_page = 'Payment Methods';
require_once __DIR__ . '/includes/header.php';
?>

    <style>
        .img-thumbnail {
            max-width: 100px;
            max-height: 60px;
        }
    </style>


        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Payment Methods</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                    <i class="fas fa-plus me-2"></i>Add Method
                </button>
            </div>

            <!-- Message Alerts -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Payment Methods Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payment_methods)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No payment methods found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($payment_methods as $method): ?>
                                        <tr>
                                            <td>
                                                <?php if ($method['image']): ?>
                                                    <img src="<?php echo htmlspecialchars($method['image']); ?>" class="img-thumbnail" alt="<?php echo htmlspecialchars($method['name']); ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-credit-card fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($method['name']); ?></td>
                                            <td><?php echo htmlspecialchars($method['description']); ?></td>
                                            <td><?php echo htmlspecialchars($method['value']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo $method['active']; ?>">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" 
                                                               id="toggle_<?php echo $method['id']; ?>" 
                                                               <?php echo $method['active'] ? 'checked' : ''; ?>
                                                               onchange="this.form.submit()">
                                                        <input type="hidden" name="toggle_method" value="1">
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-method" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPaymentMethodModal"
                                                        data-id="<?php echo $method['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($method['name']); ?>"
                                                        data-description="<?php echo htmlspecialchars($method['description']); ?>"
                                                        data-value="<?php echo htmlspecialchars($method['value']); ?>"
                                                        data-link="<?php echo htmlspecialchars($method['link']); ?>"
                                                        data-token_key="<?php echo htmlspecialchars($method['token_key']); ?>"
                                                        data-image="<?php echo htmlspecialchars($method['image']); ?>">
                                                    <i class="fas fa-edit"></i> 
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                                                    <button type="submit" name="delete_payment_method" class="btn btn-sm btn-primary" 
                                                            onclick="return confirm('Are you sure you want to delete this payment method?')">
                                                        <i class="fas fa-trash"></i> 
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Method Modal -->
    <div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPaymentMethodModalLabel">Add Payment Method</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Logo/Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="value" class="form-label">Value*</label>
                                    <input type="text" class="form-control" id="value" name="value" required>
                                    <small class="form-text text-muted">Internal identifier (e.g., 'paypal', 'stripe')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="link" class="form-label">Payment Link/URL</label>
                                    <input type="text" class="form-control" id="link" name="link">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="token_key" class="form-label">API Token Key</label>
                                    <input type="text" class="form-control" id="token_key" name="token_key">
                                    <small class="form-text text-muted">For payment gateways requiring API keys</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_payment_method" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Method
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Payment Method Modal -->
    <div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-labelledby="editPaymentMethodModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editMethodId">
                    <input type="hidden" name="current_image" id="editMethodCurrentImage">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPaymentMethodModalLabel">Edit Payment Method</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editMethodName" class="form-label">Name*</label>
                                    <input type="text" class="form-control" id="editMethodName" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editMethodImage" class="form-label">Logo/Image</label>
                                    <input type="file" class="form-control" id="editMethodImage" name="image" accept="image/*">
                                    <div class="mt-2" id="editMethodImagePreview"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editMethodValue" class="form-label">Value*</label>
                                    <input type="text" class="form-control" id="editMethodValue" name="value" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editMethodDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="editMethodDescription" name="description" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editMethodLink" class="form-label">Payment Link/URL</label>
                                    <input type="text" class="form-control" id="editMethodLink" name="link">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editMethodTokenKey" class="form-label">API Token Key</label>
                                    <input type="text" class="form-control" id="editMethodTokenKey" name="token_key">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_payment_method" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Method
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle edit method buttons
        document.getElementById('editPaymentMethodModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var modal = this;
            
            // Extract data from button attributes
            modal.querySelector('#editMethodId').value = button.getAttribute('data-id');
            modal.querySelector('#editMethodName').value = button.getAttribute('data-name');
            modal.querySelector('#editMethodDescription').value = button.getAttribute('data-description');
            modal.querySelector('#editMethodValue').value = button.getAttribute('data-value');
            modal.querySelector('#editMethodLink').value = button.getAttribute('data-link');
            modal.querySelector('#editMethodTokenKey').value = button.getAttribute('data-token_key');
            modal.querySelector('#editMethodCurrentImage').value = button.getAttribute('data-image');
            
            // Update image preview
            var imagePreview = modal.querySelector('#editMethodImagePreview');
            imagePreview.innerHTML = '';
            var imagePath = button.getAttribute('data-image');
            if (imagePath) {
                var img = document.createElement('img');
                img.src = imagePath;
                img.className = 'img-thumbnail';
                imagePreview.appendChild(img);
            }
        });

        // Handle toggle switches in table
        document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    });
    </script>
</body>
</html>