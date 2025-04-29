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
    if (isset($_POST['add_shipping_method'])) {
        // Add a new shipping method
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $area_of_delivery = $_POST['area_of_delivery'];

        $stmt = $conn->prepare("INSERT INTO shipping_methods (name, phone, email, address, area_of_delivery) VALUES (:name, :phone, :email, :address, :area_of_delivery)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':area_of_delivery', $area_of_delivery);
        $stmt->execute();
        
        $_SESSION['message'] = 'Shipping method added successfully';
        $_SESSION['message_type'] = 'success';
    } elseif (isset($_POST['edit_shipping_method'])) {
        // Edit an existing shipping method
        $id = $_POST['id'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $area_of_delivery = $_POST['area_of_delivery'];

        $stmt = $conn->prepare("UPDATE shipping_methods SET name = :name, phone = :phone, email = :email, address = :address, area_of_delivery = :area_of_delivery WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':area_of_delivery', $area_of_delivery);
        $stmt->execute();
        
        $_SESSION['message'] = 'Shipping method updated successfully';
        $_SESSION['message_type'] = 'success';
    }

    // Refresh the page after any action
    header("Location: shipping_methods.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    // Delete a shipping method
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM shipping_methods WHERE id = :id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Shipping method deleted successfully';
    $_SESSION['message_type'] = 'success';
    header("Location: shipping_methods.php");
    exit();
}

// Fetch all shipping methods
$shipping_methods = $conn->query("SELECT * FROM shipping_methods ORDER BY name ASC");
$page_title = 'Manage Shipping Methods';
$current_page = 'Shipping Methods';
require_once __DIR__ . '/includes/header.php';
?>



        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Shipping Methods</h1>
                <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addShippingMethodModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Shipping Method
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

            <!-- Shipping Methods Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-second">Shipping Methods List</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addShippingMethodModal">Add New</a></li>
                            <li><a class="dropdown-item" href="#">Export</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="shippingMethodsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Delivery Area</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($method = $shipping_methods->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $method['id']; ?></td>
                                        <td><?php echo htmlspecialchars($method['name']); ?></td>
                                        <td><?php echo htmlspecialchars($method['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($method['email']); ?></td>
                                        <td><?php echo htmlspecialchars($method['address']); ?></td>
                                        <td><?php echo htmlspecialchars($method['area_of_delivery']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editShippingMethodModal"
                                                    data-id="<?php echo $method['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($method['name']); ?>"
                                                    data-phone="<?php echo htmlspecialchars($method['phone']); ?>"
                                                    data-email="<?php echo htmlspecialchars($method['email']); ?>"
                                                    data-address="<?php echo htmlspecialchars($method['address']); ?>"
                                                    data-area_of_delivery="<?php echo htmlspecialchars($method['area_of_delivery']); ?>">
                                                <i class="fas fa-edit"></i> 
                                            </button>
                                            <a href="shipping_methods.php?delete_id=<?php echo $method['id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to delete this shipping method?');">
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

    <!-- Add Shipping Method Modal -->
    <div class="modal fade" id="addShippingMethodModal" tabindex="-1" aria-labelledby="addShippingMethodModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addShippingMethodModalLabel">Add New Shipping Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="shippingMethodName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="shippingMethodName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="shippingMethodPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="shippingMethodPhone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="shippingMethodEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="shippingMethodEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="shippingMethodAddress" class="form-label">Address</label>
                            <input type="text" class="form-control" id="shippingMethodAddress" name="address" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shippingMethodArea" class="form-label">Area of Delivery</label>
                            <input type="text" class="form-control" id="shippingMethodArea" name="area_of_delivery" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_shipping_method" class="btn btn-primary">Save Shipping Method</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Shipping Method Modal -->
    <div class="modal fade" id="editShippingMethodModal" tabindex="-1" aria-labelledby="editShippingMethodModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editShippingMethodModalLabel">Edit Shipping Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="editShippingMethodId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editShippingMethodName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editShippingMethodName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editShippingMethodPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editShippingMethodPhone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="editShippingMethodEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editShippingMethodEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editShippingMethodAddress" class="form-label">Address</label>
                            <input type="text" class="form-control" id="editShippingMethodAddress" name="address" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editShippingMethodArea" class="form-label">Area of Delivery</label>
                            <input type="text" class="form-control" id="editShippingMethodArea" name="area_of_delivery" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_shipping_method" class="btn btn-primary">Update Shipping Method</button>
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
        var editShippingMethodModal = document.getElementById('editShippingMethodModal');
        if (editShippingMethodModal) {
            editShippingMethodModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var phone = button.getAttribute('data-phone');
                var email = button.getAttribute('data-email');
                var address = button.getAttribute('data-address');
             
                var area_of_delivery = button.getAttribute('data-area_of_delivery');
                
                document.getElementById('editShippingMethodId').value = id;
                document.getElementById('editShippingMethodName').value = name;
                document.getElementById('editShippingMethodPhone').value = phone;
                document.getElementById('editShippingMethodEmail').value = email;
                document.getElementById('editShippingMethodAddress').value = address;
               
                document.getElementById('editShippingMethodArea').value = area_of_delivery;
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