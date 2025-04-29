<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_supplier'])) {
        // Add a new supplier
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("INSERT INTO suppliers (name, phone, email, address) VALUES (:name, :phone, :email, :address)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->execute();
        
        $_SESSION['message'] = 'Supplier added successfully';
        $_SESSION['message_type'] = 'success';
    } elseif (isset($_POST['edit_supplier'])) {
        // Edit an existing supplier
        $id = $_POST['id'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("UPDATE suppliers SET name = :name, phone = :phone, email = :email, address = :address WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->execute();
        
        $_SESSION['message'] = 'Supplier updated successfully';
        $_SESSION['message_type'] = 'success';
    }

    // Refresh the page after any action
    header("Location: suppliers.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    // Delete a supplier
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = :id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Supplier deleted successfully';
    $_SESSION['message_type'] = 'success';
    header("Location: suppliers.php");
    exit();
}

// Fetch all suppliers
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY name ASC");
$page_title = 'Manage Suppliers';
$current_page = 'Suppliers';
require_once __DIR__ . '/includes/header.php';
?>



        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Suppliers</h1>
                <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Supplier
                </button>
            </div>

            <!-- Message Alert -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Suppliers Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-second">Supplier List</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addSupplierModal">Add New</a></li>
                            <li><a class="dropdown-item" href="#">Export</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="suppliersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($supplier = $suppliers->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $supplier['id']; ?></td>
                                        <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                                        <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                        <td><?php echo htmlspecialchars($supplier['address']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSupplierModal"
                                                    data-id="<?php echo $supplier['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($supplier['name']); ?>"
                                                    data-phone="<?php echo htmlspecialchars($supplier['phone']); ?>"
                                                    data-email="<?php echo htmlspecialchars($supplier['email']); ?>"
                                                    data-address="<?php echo htmlspecialchars($supplier['address']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="suppliers.php?delete_id=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to delete this supplier?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="supplierName" class="form-label">Supplier Name</label>
                            <input type="text" class="form-control" id="supplierName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="supplierPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="supplierPhone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="supplierEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="supplierEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="supplierAddress" class="form-label">Address</label>
                            <input type="text" class="form-control" id="supplierAddress" name="address" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_supplier" class="btn btn-primary">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="editSupplierId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editSupplierName" class="form-label">Supplier Name</label>
                            <input type="text" class="form-control" id="editSupplierName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSupplierPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editSupplierPhone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSupplierEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editSupplierEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSupplierAddress" class="form-label">Address</label>
                            <input type="text" class="form-control" id="editSupplierAddress" name="address" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_supplier" class="btn btn-primary">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
    // Initialize the edit modal with data
    document.addEventListener('DOMContentLoaded', function() {
        var editSupplierModal = document.getElementById('editSupplierModal');
        if (editSupplierModal) {
            editSupplierModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var phone = button.getAttribute('data-phone');
                var email = button.getAttribute('data-email');
                var address = button.getAttribute('data-address');
                
                document.getElementById('editSupplierId').value = id;
                document.getElementById('editSupplierName').value = name;
                document.getElementById('editSupplierPhone').value = phone;
                document.getElementById('editSupplierEmail').value = email;
                document.getElementById('editSupplierAddress').value = address;
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