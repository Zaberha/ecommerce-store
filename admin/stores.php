<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_store_settings'])) {
    try {
        $store_name = trim($_POST['store_name']);
        $store_phone = trim($_POST['store_phone']);
        $store_email = trim($_POST['store_email']);
        $store_address = trim($_POST['store_address']);
        $store_city = trim($_POST['store_city']);
        $store_country = trim($_POST['store_country']);

        $stmt = $conn->prepare("UPDATE admin SET 
                              store_name = :store_name,
                              store_phone = :store_phone,
                              store_email = :store_email,
                              store_address = :store_address,
                              store_city = :store_city,
                              store_country = :store_country 
                              WHERE id = 1");

        $stmt->execute([
            ':store_name' => $store_name,
            ':store_phone' => $store_phone,
            ':store_email' => $store_email,
            ':store_address' => $store_address,
            ':store_city' => $store_city,
            ':store_country' => $store_country
        ]);

        $_SESSION['message'] = 'Store settings updated successfully';
        $_SESSION['message_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    header("Location: stores.php");
    exit();
}

// Fetch admin/store settings
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = 'Store Management';
$current_page = 'Store';
require_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Content -->
    <div class="container-fluid px-4">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Store Information</h1>
        </div>

        <!-- Message Alerts -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Store Settings Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Store Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="storeSettingsForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_name" class="form-label">Store Name*</label>
                                <input type="text" class="form-control" id="store_name" name="store_name" 
                                       value="<?php echo htmlspecialchars($admin['store_name'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please provide a store name</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="store_phone" class="form-label">Store Phone*</label>
                                <input type="tel" class="form-control" id="store_phone" name="store_phone" 
                                       value="<?php echo htmlspecialchars($admin['store_phone'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please provide a valid phone number</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="store_email" class="form-label">Store Email*</label>
                                <input type="email" class="form-control" id="store_email" name="store_email" 
                                       value="<?php echo htmlspecialchars($admin['store_email'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please provide a valid email address</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_address" class="form-label">Store Address*</label>
                                <input type="text" class="form-control" id="store_address" name="store_address" 
                                       value="<?php echo htmlspecialchars($admin['store_address'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please provide a store address</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="store_city" class="form-label">Store City*</label>
                                <input type="text" class="form-control" id="store_city" name="store_city" 
                                       value="<?php echo htmlspecialchars($admin['store_city'] ?? ''); ?>" required>
                                <div class="invalid-feedback">Please provide a city</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="store_country" class="form-label">Store Country*</label>
                                <select id="store_country" name="store_country" class="form-control" required>
                                <option value=" <?= htmlspecialchars($admin['store_country'] ?? '') ?>"> <?= htmlspecialchars($admin['store_country'] ?? '') ?></option>
                                <?php
        // Fetch countries from database
        $stmt = $conn->query("SELECT country_code, country_name FROM countries ORDER BY country_name");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $selected = (isset($country) && $country === $row['country_name']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($row['country_name']) . '" ' . $selected . '>' . 
                 htmlspecialchars($row['country_name']) . '</option>';
        }
        ?>
    </select>
                                <div class="invalid-feedback">Please provide a country</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" name="update_store_settings" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Information
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. SIDEBAR TOGGLE - GUARANTEED WORKING
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const body = document.body;
    
    // Create overlay if missing (matches your CSS)
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('active');
        overlay.classList.toggle('active');
        body.classList.toggle('sidebar-open');
    }

    // Click handlers (mobile and desktop)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });
    }

    overlay.addEventListener('click', toggleSidebar);

    // 2. ADMIN DROPDOWNS - FOOLPROOF VERSION
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const menu = this.nextElementSibling;
            menu.classList.toggle('show');
            
            // Close other menus
            document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
                if (otherMenu !== menu) otherMenu.classList.remove('show');
            });
        });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // 3. AUTO-CLOSE ALERTS (matches your existing alerts)
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    });
});
</script>