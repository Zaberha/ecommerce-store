<?php
session_start();
require_once 'db.php';

// Redirect to login if not logged in as admin
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Only admin can access this page
if ($_SESSION['employee_role'] != 'admin') {
    header("Location: manager.php");
    exit();
}

$page_title = 'Employee Privileges';
$current_page = 'privileges';

// Get all employees
$employees = $conn->query("SELECT * FROM employees ORDER BY role DESC, full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get all available pages
$all_pages = [
    'admin_dashboard' => 'Dashboard',
    'products' => 'Products',
    'categories' => 'Categories',
    'brands' => 'Brands',
    'suppliers' => 'Suppliers',
    'orders' => 'Orders',
    'invoices' => 'Invoices',
    'shipments' => 'Shipments',
    'transactions' => 'Transactions',
    'customers' => 'Customers',
    'customer_groups' => 'Customer Groups',
    'reviews' => 'Reviews',
    'inventory_dashboard' => 'Inventory Dashboard',
    'warehouses' => 'Warehouses',
    'movements' => 'Movements',
    'adjustments' => 'Stock Adjustments',
    'transfers' => 'Stock transfers',
    'inventory_alerts' => 'Inventory Alerts',
    'purchase_orders' => 'Purchase Orders',
    'promotion' => 'Promotions',
    'newrelease' => 'New Release',
    'coupons' => 'Coupons',
    'email_campaigns' => 'Email Campaigns',
    'abandoned' => 'Abandoned Cart',
    'loyalty' => 'Loyalty Program',
    'social_media' => 'Social Media',
    'settings' => 'Settings',
    'alerts_settings' => 'Alerts settings',
    'stores' => 'Store Information',
    'themes' => 'Themes',
    'links' => 'Links',
    'blogs' => 'Blogs',
    'news' => 'News',
    'images' => 'Images',
    'payment_methods' => 'Payment Methods',
    'shipping_methods' => 'Shipping Methods',
    'delivery-options' => 'Delivery Options',
    'reports' => 'Reports',
    'privileges' => 'Employee Privileges'
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_employee'])) {
        $username = trim($_POST['username']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role = trim($_POST['role']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO employees (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $email, $full_name, $role]);
            $_SESSION['success_message'] = "Employee added successfully!";
            header("Location: privileges.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error adding employee: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_privileges'])) {
        $employee_id = $_POST['employee_id'];
        
        $conn->prepare("DELETE FROM privileges WHERE employee_id = ?")->execute([$employee_id]);
        
        if (isset($_POST['privileges']) && is_array($_POST['privileges'])) {
            $stmt = $conn->prepare("INSERT INTO privileges (employee_id, page_name, can_access) VALUES (?, ?, 1)");
            foreach (array_keys($_POST['privileges']) as $page_name) {
                $stmt->execute([$employee_id, $page_name]);
            }
        }
        
        $_SESSION['success_message'] = "Privileges updated successfully!";
        header("Location: privileges.php");
        exit();
    } elseif (isset($_POST['delete_employee'])) {
        $employee_id = $_POST['employee_id'];
        
        try {
            $conn->beginTransaction();
            $conn->prepare("DELETE FROM privileges WHERE employee_id = ?")->execute([$employee_id]);
            $conn->prepare("DELETE FROM employees WHERE id = ?")->execute([$employee_id]);
            $conn->commit();
            $_SESSION['success_message'] = "Employee deleted successfully!";
            header("Location: privileges.php");
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['error_message'] = "Error deleting employee: " . $e->getMessage();
        }
    } elseif (isset($_POST['toggle_status'])) {
        $employee_id = $_POST['employee_id'];
        $current_status = $_POST['current_status'];
        $new_status = $current_status ? 0 : 1;
        
        $conn->prepare("UPDATE employees SET is_active = ? WHERE id = ?")->execute([$new_status, $employee_id]);
        $_SESSION['success_message'] = "Employee status updated!";
        header("Location: privileges.php");
        exit();
    }
}

// Get privileges for all employees
$privileges = [];
$stmt = $conn->query("SELECT employee_id, page_name FROM privileges WHERE can_access = 1");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $privileges[$row['employee_id']][$row['page_name']] = true;
}

$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
$default_currency = $admin_settings['default_currency'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dynamic-styles.php">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .access-switch {
            width: 50px;
            height: 26px;
        }
        .access-switch:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
        .access-row.active {
            background-color: rgba(40, 167, 69, 0.1);
        }
    </style>
</head>
<body>
    <button class="btn btn-link d-md-none rounded-circle me-3 position-fixed top-0 start-0 mt-2 mb-2 ms-2 z-3" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar-overlay"></div>

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
  
    <div class="main-content" id="mainContent">
        <nav class="navbar navbar-expand top-navbar shadow mb-4">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Privileges</li>
                        </ol>
                    </nav>
                </div>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-fw"></i> <?php echo htmlspecialchars($_SESSION['employee_full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-fw"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="privileges.php"><i class="fas fa-cog fa-fw"></i> Privileges</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid px-4">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-second">Manage Employees</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="employeesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?= $employee['id'] ?></td>
                                        <td><?= htmlspecialchars($employee['username']) ?></td>
                                        <td><?= htmlspecialchars($employee['full_name']) ?></td>
                                        <td><?= htmlspecialchars($employee['email']) ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= $employee['role'] == 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                                <?= ucfirst($employee['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= $employee['is_active'] ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm <?= $employee['is_active'] ? 'btn-success' : 'btn-danger' ?>">
                                                    <?= $employee['is_active'] ? 'Active' : 'Inactive' ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#privilegesModal<?= $employee['id'] ?>">
                                                <i class="fas fa-key"></i> Privileges
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
                                                <button type="submit" name="delete_employee" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this employee?');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
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

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Privileges Modals -->
<!-- Privileges Modals -->
<?php foreach ($employees as $employee): 
    // Get privileges for this specific employee
    $stmt = $conn->prepare("SELECT page_name, can_access FROM privileges WHERE employee_id = ?");
    $stmt->execute([$employee['id']]);
    $employee_privileges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create an array of pages this employee can access (where can_access = 1)
    $can_access_pages = [];
    foreach ($employee_privileges as $priv) {
        if ($priv['can_access'] == 1) {
            $can_access_pages[$priv['page_name']] = true;
        }
    }
?>
    <div class="modal fade" id="privilegesModal<?= $employee['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Access Control: <?= htmlspecialchars($employee['full_name']) ?>
                            <?php if($employee['role'] == 'admin'): ?>
                                <span class="badge bg-primary ms-2">Admin (Full Access)</span>
                            <?php endif; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Page</th>
                                        <th class="text-center">Access</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_pages as $page => $title): ?>
                                    <?php $hasAccess = isset($can_access_pages[$page]); ?>
                                    <tr class="access-row <?= $hasAccess ? 'active' : '' ?>">
                                        <td><?= htmlspecialchars($title) ?></td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input access-switch" 
                                                       type="checkbox" 
                                                       name="privileges[<?= $page ?>]"
                                                       <?= $hasAccess ? 'checked' : '' ?>
                                                       <?= $employee['role'] == 'admin' ? 'disabled' : '' ?>>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <?php if($employee['role'] != 'admin'): ?>
                            <button type="submit" name="update_privileges" class="btn btn-primary">Save Changes</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#employeesTable').DataTable({
            responsive: true,
            order: [[0, 'asc']]
        });

        // Update row highlighting when modal opens
        $('.modal').on('show.bs.modal', function() {
            $(this).find('.access-switch:checked').each(function() {
                $(this).closest('.access-row').addClass('active');
            });
        });

        // Toggle highlighting when switches change
        $(document).on('change', '.access-switch', function() {
            $(this).closest('.access-row').toggleClass('active', this.checked);
        });
    });

    // Initialize sidebar functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const overlay = document.querySelector('.sidebar-overlay');

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('active');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            sidebarToggle.setAttribute('aria-expanded', isOpen);
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        if (overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });

        const submenuToggles = document.querySelectorAll('.submenu-toggle');
        submenuToggles.forEach((toggle) => {
            toggle.addEventListener('click', function() {
                const submenu = this.nextElementSibling;
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                this.classList.toggle('active');
            });
        });
    });
    </script>
</body>
</html>