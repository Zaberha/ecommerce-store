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
        if (isset($_POST['create_coupon'])) {
            // Create new coupon - now with mandatory user_id
            $code_name = $_POST['code_name'];
            $code = $_POST['code'];
            $discount_percentage = $_POST['discount_percentage'];
            $expiry_date = $_POST['expiry_date'];
            $usage_limit = $_POST['usage_limit'];
            $type = $_POST['type'];
            $user_id = $_POST['user_id'];
            
            try {
                $stmt = $conn->prepare("INSERT INTO discount_codes 
                                      (code_name, code, discount_percentage, expiry_date, usage_limit, type, user_id) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code_name, $code, $discount_percentage, $expiry_date, $usage_limit, $type, $user_id]);
                
                $_SESSION['success_message'] = "Coupon created successfully!";
                
                if (isset($_POST['send_email'])) {
                    // Send email to the assigned user
                    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $email = $stmt->fetchColumn();
                    
                    if ($email) {
                        $subject = "Your Exclusive Discount Coupon";
                        $message = "Here's your exclusive discount coupon:\n\n";
                        $message .= "Code: $code\n";
                        $message .= "Discount: $discount%\n";
                        $message .= "Expires: " . date('F j, Y', strtotime($expiry_date)) . "\n\n";
                        $message .= "Thank you for being a valued customer!";
                        // mail($email, $subject, $message);
                    }
                }
                
                // Redirect to prevent form resubmission
                header('Location: coupons.php');
                exit();
                
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error creating coupon: " . $e->getMessage();
            }
        } 
        elseif (isset($_POST['update_coupon'])) {
            // Handle coupon update
            $coupon_id = $_POST['coupon_id'];
            $code_name = $_POST['code_name'];
            $code = $_POST['code'];
            $discount_percentage = $_POST['discount_percentage'];
            $expiry_date = $_POST['expiry_date'];
            $usage_limit = $_POST['usage_limit'];
            $type = $_POST['type'];
            $user_id = $_POST['user_id'];
            
            try {
                $stmt = $conn->prepare("UPDATE discount_codes SET 
                                      code_name = ?, 
                                      code = ?, 
                                      discount_percentage = ?, 
                                      expiry_date = ?, 
                                      usage_limit = ?, 
                                      type = ?, 
                                      user_id = ?
                                      WHERE id = ?");
                $stmt->execute([$code_name, $code, $discount_percentage, $expiry_date, $usage_limit, $type, $user_id, $coupon_id]);
                
                $_SESSION['success_message'] = "Coupon updated successfully!";
                
                if (isset($_POST['send_email'])) {
                    // Send email to the assigned user
                    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $email = $stmt->fetchColumn();
                    
                    if ($email) {
                        $subject = "Your Updated Discount Coupon";
                        $message = "Here's your updated discount coupon:\n\n";
                        $message .= "Code: $code\n";
                        $message .= "Discount: $discount_percentage%\n";
                        $message .= "Expires: " . date('F j, Y', strtotime($expiry_date)) . "\n\n";
                        $message .= "Thank you for being a valued customer!";
                        // mail($email, $subject, $message);
                    }
                }
                
                // Redirect to prevent form resubmission
                header('Location: coupons.php');
                exit();
                
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error updating coupon: " . $e->getMessage();
            }
        }
        elseif (isset($_POST['delete_coupon'])) {
            // Delete coupon
            $coupon_id = $_POST['coupon_id'];
            try {
                $stmt = $conn->prepare("DELETE FROM discount_codes WHERE id = ?");
                $stmt->execute([$coupon_id]);
                $_SESSION['success_message'] = "Coupon deleted successfully!";
                
                // Redirect to prevent form resubmission
                header('Location: coupons.php');
                exit();
                
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error deleting coupon: " . $e->getMessage();
            }
        } 
        elseif (isset($_POST['send_coupon_email'])) {
            // Send coupon email to assigned user
            $coupon_id = $_POST['coupon_id'];
            try {
                $stmt = $conn->prepare("SELECT dc.*, u.email FROM discount_codes dc
                                      JOIN users u ON dc.user_id = u.id
                                      WHERE dc.id = ?");
                $stmt->execute([$coupon_id]);
                $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($coupon) {
                    $subject = "Your Exclusive Discount Coupon";
                    $message = "Here's your exclusive discount coupon:\n\n";
                    $message .= "Code: {$coupon['code']}\n";
                    $message .= "Discount: {$coupon['discount_percentage']}%\n";
                    $message .= "Expires: " . date('F j, Y', strtotime($coupon['expiry_date'])) . "\n\n";
                    $message .= "Thank you for being a valued customer!";
                    // mail($coupon['email'], $subject, $message);
                    
                    $_SESSION['success_message'] = "Coupon email sent successfully!";
                    
                    // Redirect to prevent form resubmission
                    header('Location: coupons.php');
                    exit();
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error sending coupon email: " . $e->getMessage();
            }
        }
    }
    
// Get all coupons with user information
$coupons = [];
try {
    $stmt = $conn->query("SELECT dc.*, u.username as user_name 
                        FROM discount_codes dc
                        LEFT JOIN users u ON dc.user_id = u.id
                        ORDER BY dc.created_at DESC");
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching coupons: " . $e->getMessage();
}

// Get users for dropdown
$users = [];
try {
    $stmt = $conn->query("SELECT id, username FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching users: " . $e->getMessage();
}
$page_title = 'Coupons Management';
$current_page = 'Coupons';
require_once __DIR__ . '/includes/header.php';
?>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Coupon Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCouponModal">
                    <i class="fas fa-plus me-2"></i>Create New Coupon
                </button>
            </div>

            <!-- Coupons Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">All Coupons</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="couponsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code Name</th>
                                    <th>Code</th>
                                    <th>Discount</th>
                                    <th>Assigned User</th>
                                    <th>Expiry Date</th>
                                    <th>Usage Limit</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coupons as $coupon): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($coupon['id']); ?></td>
                                        <td><?php echo htmlspecialchars($coupon['code_name']); ?></td>
                                        <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                                        <td><?php echo htmlspecialchars($coupon['discount_percentage']); ?>%</td>
                                        <td><?php echo htmlspecialchars($coupon['user_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($coupon['expiry_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($coupon['usage_limit']); ?></td>
                                        <td><?php echo htmlspecialchars($coupon['type']); ?></td>
                                        <td>
                                            <?php if ($coupon['active_flag'] == 1): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Used</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                                    <button type="submit" name="send_coupon_email" class="btn btn-sm btn-primary" title="Send Email">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </form>
                                               
                                                <button class="btn btn-sm btn-primary edit-coupon <?php echo ($coupon['type'] === 'loyalty' || $coupon['type'] === 'registration') ? 'disabled' : ''; ?>" 
                data-id="<?php echo $coupon['id']; ?>"
                data-code_name="<?php echo htmlspecialchars($coupon['code_name']); ?>"
                data-code="<?php echo htmlspecialchars($coupon['code']); ?>"
                data-discount="<?php echo htmlspecialchars($coupon['discount_percentage']); ?>"
                data-expiry="<?php echo date('Y-m-d H:i', strtotime($coupon['expiry_date'])); ?>"
                data-usage_limit="<?php echo htmlspecialchars($coupon['usage_limit']); ?>"
                data-type="<?php echo htmlspecialchars($coupon['type']); ?>"
                data-assigned_to="<?php echo htmlspecialchars($coupon['assigned_to']); ?>"
                data-user_id="<?php echo htmlspecialchars($coupon['user_id']); ?>"
                data-group_id="<?php echo htmlspecialchars($coupon['group_id']); ?>"
                title="Edit"
                <?php echo ($coupon['type'] === 'loyalty' || $coupon['type'] === 'registration') ? 'disabled' : ''; ?>>
            <i class="fas fa-edit"></i>
        </button>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                                    <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                                    <button type="submit" name="delete_coupon" class="btn btn-sm btn-primary" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
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

    <!-- Create Coupon Modal -->
    <div class="modal fade" id="createCouponModal" tabindex="-1" aria-labelledby="createCouponModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCouponModalLabel">Create New Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="couponForm">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="code_name" class="form-label">Coupon Name</label>
                                <input type="text" class="form-control" id="code_name" name="code_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="code" class="form-label">Coupon Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="code" name="code" required>
                                    <button class="btn btn-primary" type="button" id="generateCode">
                                        <i class="fas fa-sync-alt"></i> Generate
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="discount_percentage" class="form-label">Discount Percentage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" min="1" max="100" step="0.01" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="datetime-local" class="form-control" id="expiry_date" name="expiry_date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="usage_limit" class="form-label">Usage Limit</label>
                                <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" value="1">
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label">Coupon Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="gift">Gift</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">Assign To User</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="send_email" name="send_email">
                                    <label class="form-check-label" for="send_email">
                                        Send coupon to assigned user via email
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_coupon" class="btn btn-primary">Create Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Coupon Modal -->
    <div class="modal fade" id="editCouponModal" tabindex="-1" aria-labelledby="editCouponModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCouponModalLabel">Edit Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="editCouponForm">
                    <input type="hidden" id="edit_coupon_id" name="coupon_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_code_name" class="form-label">Coupon Name</label>
                                <input type="text" class="form-control" id="edit_code_name" name="code_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_code" class="form-label">Coupon Code</label>
                                <input type="text" class="form-control" id="edit_code" name="code" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_discount_percentage" class="form-label">Discount Percentage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_discount_percentage" name="discount_percentage" min="1" max="100" step="0.01" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_expiry_date" class="form-label">Expiry Date</label>
                                <input type="datetime-local" class="form-control" id="edit_expiry_date" name="expiry_date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_usage_limit" class="form-label">Usage Limit</label>
                                <input type="number" class="form-control" id="edit_usage_limit" name="usage_limit" min="1">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_type" class="form-label">Coupon Type</label>
                                <select class="form-select" id="edit_type" name="type" required>
                                    <option value="gift">Gift</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_user_id" class="form-label">Assign To User</label>
                                <select class="form-select" id="edit_user_id" name="user_id" required>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_send_email" name="send_email">
                                    <label class="form-check-label" for="edit_send_email">
                                        Send updated coupon to user via email
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_coupon" class="btn btn-primary">Update Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr for datepicker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- Custom Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize sidebar functionality
        initSidebar();
        
        // Initialize datepicker
        flatpickr("#expiry_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today"
        });
        
        flatpickr("#edit_expiry_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i"
        });
        
        // Generate random coupon code
        document.getElementById('generateCode').addEventListener('click', function() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 8; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('code').value = result;
        });
        
        // Edit coupon modal
        document.querySelectorAll('.edit-coupon').forEach(button => {
            button.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editCouponModal'));
                const data = this.dataset;
                
                document.getElementById('edit_coupon_id').value = data.id;
                document.getElementById('edit_code_name').value = data.code_name;
                document.getElementById('edit_code').value = data.code;
                document.getElementById('edit_discount_percentage').value = data.discount;
                document.getElementById('edit_expiry_date').value = data.expiry;
                document.getElementById('edit_usage_limit').value = data.usage_limit;
                document.getElementById('edit_type').value = data.type;
                document.getElementById('edit_user_id').value = data.user_id;
                
                modal.show();
            });
        });
    });

    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        if (!sidebarToggle || !sidebar || !mainContent) return;

        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('active');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            sidebarToggle.setAttribute('aria-expanded', isOpen);
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    }
    </script>
</body>
</html>