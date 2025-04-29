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
    if (isset($_POST['add_category'])) {
        // Add a new category
        $name = $_POST['name'];
        $active = isset($_POST['active']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO categories (name, active) VALUES (:name, :active)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':active', $active);
        $stmt->execute();
        
        $_SESSION['message'] = 'Category added successfully';
        $_SESSION['message_type'] = 'success';
    } elseif (isset($_POST['edit_category'])) {
        // Edit an existing category
        $id = $_POST['id'];
        $name = $_POST['name'];
        $active = isset($_POST['active']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE categories SET name = :name, active = :active WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':active', $active);
        $stmt->execute();
        
        $_SESSION['message'] = 'Category updated successfully';
        $_SESSION['message_type'] = 'success';
    }

    // Refresh the page after any action
    header("Location: categories.php");
    exit();
}

// Handle delete and toggle active actions
if (isset($_GET['delete_id'])) {
    // Delete a category
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Category deleted successfully';
    $_SESSION['message_type'] = 'success';
    header("Location: categories.php");
    exit();
} elseif (isset($_GET['toggle_active'])) {
    // Toggle the active flag
    $id = $_GET['toggle_active'];
    $stmt = $conn->prepare("UPDATE categories SET active = NOT active WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Category status updated';
    $_SESSION['message_type'] = 'success';
    header("Location: categories.php");
    exit();
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$page_title = 'Manage Categories';
$current_page = 'categories';
require_once __DIR__ . '/includes/header.php';
?>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Categories</h1>
                <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Category
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

            <!-- Categories Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-second">Category List</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add New</a></li>
                            <li><a class="dropdown-item" href="#">Export</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="categoriesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($category = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $category['active'] ? 'bg-success' : 'bg-primary'; ?>">
                                                <?php echo $category['active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCategoryModal"
                                                    data-id="<?php echo $category['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                    data-active="<?php echo $category['active']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="categories.php?toggle_active=<?php echo $category['id']; ?>" class="btn btn-sm <?php echo $category['active'] ? 'btn-success' : 'btn-warning'; ?>">
                                                <i class="fas fa-power-off"></i> <?php echo $category['active'] ? 'Deactivate' : 'Activate'; ?>
                                            </a>
                                            <a href="categories.php?delete_id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to delete this category?');">
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

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="name" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="categoryActive" name="active" value="1" checked>
                            <label class="form-check-label" for="categoryActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="editCategoryId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="editCategoryName" name="name" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editCategoryActive" name="active" value="1">
                            <label class="form-check-label" for="editCategoryActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_category" class="btn btn-primary">Update Category</button>
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