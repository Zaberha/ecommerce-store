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
    if (isset($_POST['add_group'])) {
        // Add new group
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        $stmt = $conn->prepare("INSERT INTO customer_groups (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        
        $_SESSION['message'] = 'Customer group added successfully';
        header("Location: customer_groups.php");
        exit();
    } elseif (isset($_POST['update_group'])) {
        // Update existing group
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        $stmt = $conn->prepare("UPDATE customer_groups SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        
        $_SESSION['message'] = 'Customer group updated successfully';
        header("Location: customer_groups.php");
        exit();
    } elseif (isset($_POST['delete_group'])) {
        // Delete group
        $id = intval($_POST['id']);
        
        // First check if any customers are in this group
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE group_id = ?");
        $check_stmt->execute([$id]);
        $customer_count = $check_stmt->fetchColumn();
        
        if ($customer_count > 0) {
            $_SESSION['error'] = 'Cannot delete group with assigned customers';
        } else {
            $stmt = $conn->prepare("DELETE FROM customer_groups WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = 'Customer group deleted successfully';
        }
        
        header("Location: customer_groups.php");
        exit();
    }
}

// Fetch all customer groups
$groups_query = "SELECT * FROM customer_groups ORDER BY name";
$groups_stmt = $conn->query($groups_query);
$customer_groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Customer Groups';
$current_page = 'Customer Groups';
require_once __DIR__ . '/includes/header.php';
?>


                <div class="container-fluid px-4">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Customer Groups</h1>
                        <div>
                            <a href="customers.php" class="btn btn-primary me-2">
                                <i class="fas fa-arrow-left"></i> Back to Customers
                            </a>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                                <i class="fas fa-plus"></i> Add Group
                            </button>
                        </div>
                    </div>

                    <!-- Message Alert -->
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

                    <!-- Customer Groups Table -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Group Name</th>
                                            <th>Description</th>
                                            <th>Customers</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($customer_groups)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No customer groups found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($customer_groups as $group): ?>
                                                <?php
                                                // Count customers in this group
                                                $count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE group_id = ?");
                                                $count_stmt->execute([$group['id']]);
                                                $customer_count = $count_stmt->fetchColumn();
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($group['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($group['description']); ?></td>
                                                    <td><?php echo $customer_count; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary edit-group" 
                                                                data-id="<?php echo $group['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($group['name']); ?>"
                                                                data-description="<?php echo htmlspecialchars($group['description']); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="id" value="<?php echo $group['id']; ?>">
                                                            <button type="submit" name="delete_group" class="btn btn-sm btn-primary" 
                                                                    onclick="return confirm('Are you sure you want to delete this group?')">
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
   
         <!-- Footer -->
         <?php require_once __DIR__ . '/includes/footer.php'; ?>
    <!-- Add Group Modal -->
    <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGroupModalLabel">Add Customer Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="groupName" class="form-label">Group Name *</label>
                            <input type="text" class="form-control" id="groupName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="groupDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="groupDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_group" class="btn btn-primary">Add Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Group Modal -->
    <div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="id" id="editGroupId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editGroupModalLabel">Edit Customer Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editGroupName" class="form-label">Group Name *</label>
                            <input type="text" class="form-control" id="editGroupName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editGroupDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editGroupDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_group" class="btn btn-primary">Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Handle edit group buttons
        document.querySelectorAll('.edit-group').forEach(button => {
            button.addEventListener('click', function() {
                const groupId = this.dataset.id;
                const groupName = this.dataset.name;
                const groupDescription = this.dataset.description;
                
                document.getElementById('editGroupId').value = groupId;
                document.getElementById('editGroupName').value = groupName;
                document.getElementById('editGroupDescription').value = groupDescription;
                
                var editModal = new bootstrap.Modal(document.getElementById('editGroupModal'));
                editModal.show();
            });
        });
    });
    // Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }
});
    </script>
</body>
</html>