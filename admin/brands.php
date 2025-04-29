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
    if (isset($_POST['add_brand'])) {
        // Add a new brand
        $name = $_POST['name'];
        $active = isset($_POST['active']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO brands (name, active) VALUES (:name, :active)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':active', $active);
        $stmt->execute();
        
        $_SESSION['message'] = 'Brand added successfully';
        $_SESSION['message_type'] = 'success';
    } elseif (isset($_POST['edit_brand'])) {
        // Edit an existing brand
        $id = $_POST['id'];
        $name = $_POST['name'];
        $active = isset($_POST['active']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE brands SET name = :name, active = :active WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':active', $active);
        $stmt->execute();
        
        $_SESSION['message'] = 'Brand updated successfully';
        $_SESSION['message_type'] = 'success';
    }

    // Refresh the page after any action
    header("Location: brands.php");
    exit();
}

// Handle delete and toggle active actions
if (isset($_GET['delete_id'])) {
    // Delete a brand
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM brands WHERE id = :id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Brand deleted successfully';
    $_SESSION['message_type'] = 'success';
    header("Location: brands.php");
    exit();
} elseif (isset($_GET['toggle_active'])) {
    // Toggle the active flag
    $id = $_GET['toggle_active'];
    $stmt = $conn->prepare("UPDATE brands SET active = NOT active WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Brand status updated';
    $_SESSION['message_type'] = 'success';
    header("Location: brands.php");
    exit();
}

// Fetch all brands
$brands = $conn->query("SELECT * FROM brands ORDER BY name ASC");
$page_title = 'Manage Brands';
$current_page = 'Brands';
require_once __DIR__ . '/includes/header.php';
?>


        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Brands</h1>
                <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Brand
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

            <!-- Brands Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-second">Brand List</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addBrandModal">Add New</a></li>
                            <li><a class="dropdown-item" href="#">Export</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="brandsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($brand = $brands->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $brand['id']; ?></td>
                                        <td><?php echo htmlspecialchars($brand['name']); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $brand['active'] ? 'bg-success' : 'bg-primary'; ?>">
                                                <?php echo $brand['active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editBrandModal"
                                                    data-id="<?php echo $brand['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($brand['name']); ?>"
                                                    data-active="<?php echo $brand['active']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="brands.php?toggle_active=<?php echo $brand['id']; ?>" class="btn btn-sm <?php echo $brand['active'] ? 'btn-success' : 'btn-warning'; ?>">
                                                <i class="fas fa-power-off"></i> <?php echo $brand['active'] ? 'Deactivate' : 'Activate'; ?>
                                            </a>
                                            <a href="brands.php?delete_id=<?php echo $brand['id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to delete this brand?');">
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

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBrandModalLabel">Add New Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="brandName" class="form-label">Brand Name</label>
                            <input type="text" class="form-control" id="brandName" name="name" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="brandActive" name="active" value="1" checked>
                            <label class="form-check-label" for="brandActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_brand" class="btn btn-primary">Save Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBrandModalLabel">Edit Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="editBrandId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editBrandName" class="form-label">Brand Name</label>
                            <input type="text" class="form-control" id="editBrandName" name="name" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editBrandActive" name="active" value="1">
                            <label class="form-check-label" for="editBrandActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_brand" class="btn btn-primary">Update Brand</button>
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
        var editBrandModal = document.getElementById('editBrandModal');
        if (editBrandModal) {
            editBrandModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var active = button.getAttribute('data-active');
                
                document.getElementById('editBrandId').value = id;
                document.getElementById('editBrandName').value = name;
                document.getElementById('editBrandActive').checked = active === '1';
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