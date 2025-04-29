<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$error = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_warehouse'])) {
            $conn->beginTransaction();

            $warehouse_data = [
                'name' => trim($_POST['name']),
                'location' => trim($_POST['location']),
                'contact_person' => trim($_POST['contact_person']),
                'phone' => trim($_POST['phone']),
                'email' => trim($_POST['email']),
                'status' => isset($_POST['status']) ? 1 : 0
            ];

            // Validate input
            if (empty($warehouse_data['name'])) throw new Exception("Warehouse name is required");
            if (empty($warehouse_data['location'])) throw new Exception("Location is required");

            if (isset($_POST['warehouse_id']) && !empty($_POST['warehouse_id'])) {
                // Update existing warehouse
                $stmt = $conn->prepare("UPDATE warehouses SET 
                    name = :name, location = :location, contact_person = :contact_person,
                    phone = :phone, email = :email, status = :status, updated_at = NOW()
                    WHERE id = :id");
                
                $warehouse_data['id'] = (int)$_POST['warehouse_id'];
                $stmt->execute($warehouse_data);
                $success = "Warehouse updated successfully!";
            } else {
                // Insert new warehouse
                $stmt = $conn->prepare("INSERT INTO warehouses 
                    (name, location, contact_person, phone, email, status, created_at) 
                    VALUES (:name, :location, :contact_person, :phone, :email, :status, NOW())");
                
                $stmt->execute($warehouse_data);
                $success = "Warehouse added successfully!";
            }

            $conn->commit();
            header("Location: warehouses.php?success=" . urlencode($success));
            exit();

        } elseif (isset($_POST['delete_warehouse'])) {
            // Existing delete handling remains the same
            $warehouse_id = (int)$_POST['warehouse_id'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) FROM warehouse_stock WHERE warehouse_id = ?");
            $stmt->execute([$warehouse_id]);
            $has_stock = $stmt->fetchColumn();
            
            if ($has_stock) throw new Exception("Cannot delete warehouse with existing stock");
            
            $stmt = $conn->prepare("DELETE FROM warehouses WHERE id = ?");
            $stmt->execute([$warehouse_id]);
            
            $success = "Warehouse deleted successfully";
            header("Location: warehouses.php?success=" . urlencode($success));
            exit();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}

// Get all warehouses
$warehouses = [];
try {
    $stmt = $conn->query("
        SELECT w.*, 
               COUNT(ws.id) as stock_items,
               SUM(ws.quantity) as total_quantity
        FROM warehouses w
        LEFT JOIN warehouse_stock ws ON w.id = ws.warehouse_id
        GROUP BY w.id
        ORDER BY w.name
    ");
    $warehouses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching warehouses: " . $e->getMessage();
}

$page_title = 'Warehouse';
$current_page = 'Warehouse';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Success/Error Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Warehouses</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
            <i class="fas fa-plus"></i> Add New Warehouse
        </button>
    </div>

    <!-- Warehouse Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Stock Items</th>
                            <th>Total Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <tr>
                                <td><?= htmlspecialchars($warehouse['name']) ?></td>
                                <td><?= htmlspecialchars($warehouse['location']) ?></td>
                                <td>
                                    <span class="badge <?= $warehouse['status'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $warehouse['status'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= $warehouse['stock_items'] ?></td>
                                <td><?= $warehouse['total_quantity'] ?: 0 ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-primary btn-sm edit-warehouse" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editWarehouseModal"
                                                data-warehouse='<?= json_encode($warehouse) ?>'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="warehouse_id" value="<?= $warehouse['id'] ?>">
                                            <button type="submit" name="delete_warehouse" class="btn btn-primary" style="border-radius:0;"
                                                    onclick="return confirm('Delete this warehouse? This cannot be undone.')"
                                                    <?= $warehouse['stock_items'] > 0 ? 'disabled title="Warehouse contains stock"' : '' ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <a href="stock.php?id=<?= $warehouse['id'] ?>" 
                                           class="btn btn-primary" title="View Stock">
                                            <i class="fas fa-boxes"></i>
                                        </a>
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

<!-- Add Warehouse Modal -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location *</label>
                            <input type="text" name="location" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="status" class="form-check-input" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_warehouse" class="btn btn-primary">Save Warehouse</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Warehouse Modal -->
<div class="modal fade" id="editWarehouseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="warehouse_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location *</label>
                            <input type="text" name="location" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="status" class="form-check-input">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_warehouse" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit modal data
    document.querySelectorAll('.edit-warehouse').forEach(button => {
        button.addEventListener('click', function() {
            const warehouse = JSON.parse(this.dataset.warehouse);
            const form = document.querySelector('#editWarehouseModal form');
            
            form.querySelector('[name="warehouse_id"]').value = warehouse.id;
            form.querySelector('[name="name"]').value = warehouse.name;
            form.querySelector('[name="location"]').value = warehouse.location;
            form.querySelector('[name="contact_person"]').value = warehouse.contact_person;
            form.querySelector('[name="phone"]').value = warehouse.phone;
            form.querySelector('[name="email"]').value = warehouse.email;
            form.querySelector('[name="status"]').checked = warehouse.status === 1;
        });
    });

    // Clear add modal on close
    document.getElementById('addWarehouseModal').addEventListener('hidden.bs.modal', function() {
        this.querySelector('form').reset();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the edit modal with data
            var editCategoryModal = document.getElementById('editCategoryModal');
            if (editCategoryModal) {
                editCategoryModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-id');
                    var name = button.getAttribute('data-name');
                    var active = button.getAttribute('data-active');
                    
                    document.getElementById('editCategoryId').value = id;
                    document.getElementById('editCategoryName').value = name;
                    document.getElementById('editCategoryActive').checked = active === '1';
                });
            }
            
            // Initialize sidebar functionality
            initSidebar();
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