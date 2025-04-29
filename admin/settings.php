<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch admin settings from the database
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_language_currency'])) {
        // Update language and currency
        $default_language = $_POST['default_language'];
        $default_currency = $_POST['default_currency'];
        
        // Fixed SQL query - removed trailing comma
        $stmt = $conn->prepare("
            UPDATE admin SET
            default_language = :default_language,
            default_currency = :default_currency
            WHERE id = :id
        ");
        $stmt->bindParam(':default_language', $default_language);
        $stmt->bindParam(':default_currency', $default_currency);
        $stmt->bindParam(':id', $admin['id']);
        $stmt->execute();
        $_SESSION['message'] = "Language and currency updated successfully.";
        header("Location: settings.php");
        exit();
    } elseif (isset($_POST['update_tax'])) {
        // Update tax rate
        $tax_rate = $_POST['tax_rate'];

        $stmt = $conn->prepare("
            UPDATE admin SET
            tax_rate = :tax_rate
            WHERE id = :id
        ");
        $stmt->bindParam(':tax_rate', $tax_rate);
        $stmt->bindParam(':id', $admin['id']);
        $stmt->execute();
        $_SESSION['message'] = "Tax rate updated successfully.";
        header("Location: settings.php");
        exit();
    }
}

try {
    $stmt = $conn->query("SELECT * FROM admin LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error loading settings: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_coupon_settings'])) {
    try {
        // Prepare the update statement
        $stmt = $conn->prepare("UPDATE admin SET
            auto_registration_coupon = ?,
            registration_coupon_percentage = ?,
            registration_coupon_expiry_days = ?,
            loyalty_program_enabled = ?,
            COLLOYALTY_coupon_threshold = ?,
            loyalty_points_rate = ?,
            loyalty_coupon_percentage = ?,
            loyalty_coupon_expiry_days = ?
            WHERE id = ?");
        
        // Execute with form values
        $stmt->execute([
            isset($_POST['auto_registration_coupon']) ? 1 : 0,
            $_POST['registration_coupon_percentage'],
            $_POST['registration_coupon_expiry_days'],
            isset($_POST['loyalty_program_enabled']) ? 1 : 0,
            $_POST['loyalty_coupon_threshold'],
            $_POST['loyalty_points_rate'],
            $_POST['loyalty_coupon_percentage'],
            $_POST['loyalty_coupon_expiry_days'],
            $admin['id'] // Assuming there's an id column in your admin table
        ]);
        
        $_SESSION['success_message'] = "Settings updated successfully!";
        
        // Refresh admin data
        $stmt = $conn->query("SELECT * FROM admin LIMIT 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating settings: " . $e->getMessage();
    }
    
    // Redirect to prevent form resubmission
    header("Location: settings.php");
    exit();
}
$page_title = 'Manage Settings';
$current_page = 'Settings';
require_once __DIR__ . '/includes/header.php';
?>

        
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Settings</h1>
            </div>

            <!-- Message Alert -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Language and Currency Settings -->
            <div class="card shadow mb-4">
            <div class="card-header">
        <h4>General Settings</h4>
    </div>
                <div class="card-body">
                    <h5><i class="fas fa-language me-2"></i>Language and Currency</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="default_language" class="form-label">Default Language</label>
                            <select class="form-select" name="default_language" id="default_language" required>
                                <option value="English" <?php echo $admin['default_language'] == 'English' ? 'selected' : ''; ?>>English</option>
                                <option value="Arabic" <?php echo $admin['default_language'] == 'Arabic' ? 'selected' : ''; ?>>Arabic</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="default_currency" class="form-label">Default Currency</label>
                            <select class="form-select" name="default_currency" id="default_currency" required>
                                <option value="AED" <?php echo $admin['default_currency'] == 'AED' ? 'selected' : ''; ?>>AED</option>
                                <option value="SAR" <?php echo $admin['default_currency'] == 'SAR' ? 'selected' : ''; ?>>SAR</option>
                                <option value="QAR" <?php echo $admin['default_currency'] == 'QAR' ? 'selected' : ''; ?>>QAR</option>
                                <option value="BHD" <?php echo $admin['default_currency'] == 'BHD' ? 'selected' : ''; ?>>BHD</option>
                                <option value="KWD" <?php echo $admin['default_currency'] == 'KWD' ? 'selected' : ''; ?>>KWD</option>
                                <option value="LP" <?php echo $admin['default_currency'] == 'LP' ? 'selected' : ''; ?>>LP</option>
                                <option value="SP" <?php echo $admin['default_currency'] == 'SP' ? 'selected' : ''; ?>>SP</option>
                                <option value="$" <?php echo $admin['default_currency'] == '$' ? 'selected' : ''; ?>>USD ($)</option>
                                <option value="EURO" <?php echo $admin['default_currency'] == 'EURO' ? 'selected' : ''; ?>>EURO</option>
                                <option value="GBP" <?php echo $admin['default_currency'] == 'GBP' ? 'selected' : ''; ?>>GBP</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_language_currency" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Language and Currency
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tax Settings -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5><i class="fas fa-percent me-2"></i>Tax Settings</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate</label>
                            <select class="form-select" name="tax_rate" id="tax_rate" required>
                                <option value="0%" <?php echo $admin['tax_rate'] == '0%' ? 'selected' : ''; ?>>0%</option>
                                <option value="5%" <?php echo $admin['tax_rate'] == '5%' ? 'selected' : ''; ?>>5%</option>
                                <option value="10%" <?php echo $admin['tax_rate'] == '10%' ? 'selected' : ''; ?>>10%</option>
                                <option value="11%" <?php echo $admin['tax_rate'] == '11%' ? 'selected' : ''; ?>>11%</option>
                                <option value="12%" <?php echo $admin['tax_rate'] == '12%' ? 'selected' : ''; ?>>12%</option>
                                <option value="15%" <?php echo $admin['tax_rate'] == '15%' ? 'selected' : ''; ?>>15%</option>
                                <option value="18%" <?php echo $admin['tax_rate'] == '18%' ? 'selected' : ''; ?>>18%</option>
                                <option value="20%" <?php echo $admin['tax_rate'] == '20%' ? 'selected' : ''; ?>>20%</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_tax" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Tax Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>



      
<div class="card mb-4">
    <div class="card-header">
        <h4>Rewards Settings</h4>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="row g-3">
                <h5><i class="fa-solid fa-money-bill me-2"></i>Registration Coupon</h5>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto_registration_coupon" 
                               name="auto_registration_coupon" <?php echo $admin['auto_registration_coupon'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="auto_registration_coupon">Enable Auto Registration Coupon</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="registration_coupon_percentage" class="form-label">Discount Percentage</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="registration_coupon_percentage" 
                               name="registration_coupon_percentage" 
                               value="<?php echo $admin['registration_coupon_percentage']; ?>" min="1" max="100" step="0.01" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="registration_coupon_expiry_days" class="form-label">Expiry Days</label>
                    <input type="number" class="form-control" id="registration_coupon_expiry_days" 
                           name="registration_coupon_expiry_days" 
                           value="<?php echo $admin['registration_coupon_expiry_days']; ?>" min="1" required>
                </div>
                
                <hr class="my-3">
                <h5><i class="fa-solid fa-arrow-up-right-dots me-2"></i>Loyalty Program</h5>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="loyalty_program_enabled" 
                               name="loyalty_program_enabled" <?php echo $admin['loyalty_program_enabled'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="loyalty_program_enabled">Enable Loyalty Program</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="loyalty_coupon_threshold" class="form-label">Points Threshold</label>
                    <input type="number" class="form-control" id="loyalty_coupon_threshold" 
                           name="loyalty_coupon_threshold" 
                           value="<?php echo $admin['COLLOYALTY_coupon_threshold']; ?>" min="1" required>
                </div>
                <div class="col-md-4">
                    <label for="loyalty_points_rate" class="form-label">Points per unit Spent</label>
                    <input type="number" class="form-control" id="loyalty_points_rate" 
                           name="loyalty_points_rate" 
                           value="<?php echo $admin['loyalty_points_rate']; ?>" min="0.01" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label for="loyalty_coupon_percentage" class="form-label">Loyalty Discount %</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="loyalty_coupon_percentage" 
                               name="loyalty_coupon_percentage" 
                               value="<?php echo $admin['loyalty_coupon_percentage']; ?>" min="1" max="100" step="1" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="loyalty_coupon_expiry_days" class="form-label">Loyalty Coupon Expiry Days</label>
                    <input type="number" class="form-control" id="loyalty_coupon_expiry_days" 
                           name="loyalty_coupon_expiry_days" 
                           value="<?php echo $admin['loyalty_coupon_expiry_days']; ?>" min="1" required>
                </div>
                
                <div class="col-12 mt-3">
                    <button type="submit" name="save_coupon_settings" class="btn btn-primary">Save Settings</button>
                </div>
            </div>
        </form>
    </div>
</div>





        </div>
        
        <!-- Footer -->
        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const body = document.body;
        
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', body.classList.contains('sb-sidenav-toggled'));
        });
        
        // Check localStorage for sidebar state
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.add('sb-sidenav-toggled');
        }
    });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the checkboxes and related fields
    const registrationCouponCheckbox = document.getElementById('auto_registration_coupon');
    const loyaltyProgramCheckbox = document.getElementById('loyalty_program_enabled');
    
    // Registration coupon fields
    const registrationFields = [
        'registration_coupon_percentage',
        'registration_coupon_expiry_days'
    ];
    
    // Loyalty program fields
    const loyaltyFields = [
        'loyalty_coupon_threshold',
        'loyalty_points_rate',
        'loyalty_coupon_percentage',
        'loyalty_coupon_expiry_days'
    ];
    
    // Function to toggle fields
    function toggleFields(checkbox, fields) {
        const isChecked = checkbox.checked;
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.disabled = !isChecked;
                field.required = isChecked; // Toggle required attribute as well
            }
        });
    }
    
    // Initial setup
    toggleFields(registrationCouponCheckbox, registrationFields);
    toggleFields(loyaltyProgramCheckbox, loyaltyFields);
    
    // Add event listeners
    registrationCouponCheckbox.addEventListener('change', function() {
        toggleFields(this, registrationFields);
    });
    
    loyaltyProgramCheckbox.addEventListener('change', function() {
        toggleFields(this, loyaltyFields);
    });
});
</script>
</body>
</html>