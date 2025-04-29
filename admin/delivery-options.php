<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

//countries

// Initialize variables
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;
$error = '';
$success = '';
$country = ['country_code' => '', 'country_name' => '', 'delivery_charges' => ''];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country_code = $_POST['country_code'] ?? '';
    $country_name = $_POST['country_name'] ?? '';
    $delivery_charges = $_POST['delivery_charges'] ?? '';
    
    try {
        if ($_POST['action'] === 'add') {
            // Add new country
            $stmt = $conn->prepare("INSERT INTO countries (country_code, country_name, delivery_charges) VALUES (?, ?, ?)");
            $stmt->execute([$country_code, $country_name, $delivery_charges]);
            $success = "Country added successfully!";
            $action = ''; // Return to list view
        } elseif ($_POST['action'] === 'edit' && $id) {
            // Update existing country
            $stmt = $conn->prepare("UPDATE countries SET country_code = ?, country_name = ?, delivery_charges = ? WHERE id = ?");
            $stmt->execute([$country_code, $country_name, $delivery_charges, $id]);
            $success = "Country updated successfully!";
            $action = ''; // Return to list view
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM countries WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Country deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
    $action = ''; // Return to list view
}

// Fetch country data for editing
if ($action === 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM countries WHERE id = ?");
    $stmt->execute([$id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$country) {
        $error = "Country not found";
        $action = ''; // Return to list view
    }
}

// Fetch all countries for listing
$stmt = $conn->query("SELECT * FROM countries ORDER BY country_name");
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

//end countries

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_delivery_settings'])) {
        $flat_rate = floatval($_POST['flat_rate']);
        $delivery_method = $_POST['delivery_method']; // 'flat_rate', 'by_product', or 'by_area'
        
        // Set all flags to 0 initially
        $is_rate_by_product = 0;
        $is_delivery_by_area = 0;
        
        // Set the appropriate flag based on selected method
        switch ($delivery_method) {
            case 'by_product':
                $is_rate_by_product = 1;
                break;
            case 'by_area':
                $is_delivery_by_area = 1;
                break;
            // default is flat rate (both flags remain 0)
        }
        
        try {
            $stmt = $conn->prepare("UPDATE delivery_options SET 
                                  flat_rate = ?, 
                                  is_rate_by_product = ?, 
                                  is_delivery_by_area = ? 
                                  WHERE id = 1");
            $stmt->execute([$flat_rate, $is_rate_by_product, $is_delivery_by_area]);
            
            $_SESSION['message'] = 'Delivery settings updated successfully';
            header("Location: delivery-options.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error updating delivery settings: ' . $e->getMessage();
            header("Location: delivery-options.php");
            exit();
        }
    }
    
    // Handle area management actions
    if (isset($_POST['add_area'])) {
        $area_name = trim($_POST['area_name']);
        $area_freight_rate = floatval($_POST['area_freight_rate']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO delivery_area (area_name, area_freight_rate) VALUES (?, ?)");
            $stmt->execute([$area_name, $area_freight_rate]);
            
            $_SESSION['message'] = 'Delivery area added successfully';
            header("Location: delivery-options.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error adding delivery area: ' . $e->getMessage();
            header("Location: delivery-options.php");
            exit();
        }
    }
    
    if (isset($_POST['update_area'])) {
        $area_id = intval($_POST['area_id']);
        $area_name = trim($_POST['area_name']);
        $area_freight_rate = floatval($_POST['area_freight_rate']);
        
        try {
            $stmt = $conn->prepare("UPDATE delivery_area SET area_name = ?, area_freight_rate = ? WHERE id = ?");
            $stmt->execute([$area_name, $area_freight_rate, $area_id]);
            
            $_SESSION['message'] = 'Delivery area updated successfully';
            header("Location: delivery-options.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error updating delivery area: ' . $e->getMessage();
            header("Location: delivery-options.php");
            exit();
        }
    }
    
    if (isset($_POST['delete_area'])) {
        $area_id = intval($_POST['area_id']);
        
        try {
            $stmt = $conn->prepare("DELETE FROM delivery_area WHERE id = ?");
            $stmt->execute([$area_id]);
            
            $_SESSION['message'] = 'Delivery area deleted successfully';
            header("Location: delivery-options.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error deleting delivery area: ' . $e->getMessage();
            header("Location: delivery-options.php");
            exit();
        }
    }
}

// Fetch current delivery settings
$delivery_settings = $conn->query("SELECT * FROM delivery_options LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// If no settings exist, create default row
if (!$delivery_settings) {
    $conn->query("INSERT INTO delivery_options (flat_rate, is_rate_by_product, is_delivery_by_area) VALUES (0.00, 0, 0)");
    $delivery_settings = $conn->query("SELECT * FROM delivery_options LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}

// Fetch delivery areas if area-based delivery is enabled
$delivery_areas = [];
if ($delivery_settings['is_delivery_by_area']) {
    $delivery_areas = $conn->query("SELECT * FROM delivery_area ORDER BY area_name")->fetchAll(PDO::FETCH_ASSOC);
}
$page_title = 'Delivery Options';
$current_page = 'Delivery Options';
require_once __DIR__ . '/includes/header.php';
?>


    

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Delivery Options</h1>
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
           
           
           
           
            <!--Countries display  -->

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-second">Delivery Abroad</h6>
                    <a href="delivery-options.php?action=add" class="btn btn-primary"> <i class="fas fa-plus"></i>New Country</a>
                </div>

        <!-- Display messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Show appropriate view based on action -->
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="card-body">
            <div class="form-container">
                <h1><?= $action === 'add' ? 'Add New Country' : 'Edit Country' ?></h1>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="country_code" class="form-label">Country Code (2 letters)</label>
                        <input type="text" class="form-control" id="country_code" name="country_code" 
                               value="<?= htmlspecialchars($country['country_code']) ?>" required maxlength="2">
                    </div>
                    
                    <div class="mb-3">
                        <label for="country_name" class="form-label">Country Name</label>
                        <input type="text" class="form-control" id="country_name" name="country_name" 
                               value="<?= htmlspecialchars($country['country_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="delivery_charges" class="form-label">Delivery Charges</label>
                        <input type="number" step="0.01" class="form-control" id="delivery_charges" name="delivery_charges" 
                               value="<?= htmlspecialchars($country['delivery_charges']) ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?= $action === 'add' ? 'Add Country' : 'Update Country' ?></button>
                    <a href="delivery-options.php" class="btn btn-primary">Cancel</a>
                </form>
            </div>
                    </div>
        <?php else: ?>
            <!-- Countries List -->
            <div class="card-body">
           
            <div class="table-responsive">
            <table class="table table-bordered">
                    <thead>
                        <tr>
                           
                            <th>Country Code</th>
                            <th>Country Name</th>
                            <th>Delivery Charges</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($countries as $country): ?>
                        <tr>
                           
                            <td><?= htmlspecialchars($country['country_code']) ?></td>
                            <td><?= htmlspecialchars($country['country_name']) ?></td>
                            <td><?= number_format($country['delivery_charges'], 2) ?></td>
                            <td>
                                <a href="delivery-options.php?action=edit&id=<?= $country['id'] ?>" class="btn btn-sm btn-primary"> <i class="fas fa-edit"></i></a>
                                <a href="delivery-options.php?action=delete&id=<?= $country['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to delete this country?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
                        </div>
                        </div>
            <!--Countries end -->







            <!-- Delivery Settings Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-second">Local Delivery Configuration</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="flat_rate">Flat Rate Shipping Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="flat_rate" name="flat_rate" 
                                               min="0" step="0.01" value="<?php echo htmlspecialchars($delivery_settings['flat_rate']); ?>">
                                    </div>
                                    <small class="form-text text-muted">This rate will apply if no other methods are selected</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Delivery Method</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="delivery_method" 
                                               id="flat_rate_method" value="flat_rate"
                                               <?php echo (!$delivery_settings['is_rate_by_product'] && !$delivery_settings['is_delivery_by_area']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="flat_rate_method">
                                            Flat Rate Shipping
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="delivery_method" 
                                               id="by_product_method" value="by_product"
                                               <?php echo $delivery_settings['is_rate_by_product'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="by_product_method">
                                            Product-Based Shipping Rates
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="delivery_method" 
                                               id="by_area_method" value="by_area"
                                               <?php echo $delivery_settings['is_delivery_by_area'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="by_area_method">
                                            Area-Based Delivery
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" name="update_delivery_settings" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Additional Sections -->
            <?php if ($delivery_settings['is_rate_by_product']): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-second">Product Shipping Rates</h6>
                    <a href="products.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Manage Products
                    </a>
                </div>
                <div class="card-body">
                    <p>Product-based shipping rates can be configured in the product management section.</p>
                    <p>When enabled, each product can have its own shipping rate that will override the flat rate.</p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($delivery_settings['is_delivery_by_area']): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-second">Local Delivery Areas</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAreaModal">
                        <i class="fas fa-plus"></i> Add Area
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($delivery_areas)): ?>
                        <p>No delivery areas configured yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Area Name</th>
                                        <th>Freight Rate</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($delivery_areas as $area): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($area['area_name']); ?></td>
                                            <td><?php echo htmlspecialchars( $default_currency); ?><?php echo number_format($area['area_freight_rate'], 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-area-btn" 
                                                        data-bs-toggle="modal" data-bs-target="#editAreaModal"
                                                        data-id="<?php echo $area['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($area['area_name']); ?>"
                                                        data-rate="<?php echo $area['area_freight_rate']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="" style="display: inline-block;">
                                                    <input type="hidden" name="area_id" value="<?php echo $area['id']; ?>">
                                                    <button type="submit" name="delete_area" class="btn btn-sm btn-primary" 
                                                            onclick="return confirm('Are you sure you want to delete this delivery area?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Area Modal -->
    <div class="modal fade" id="addAreaModal" tabindex="-1" aria-labelledby="addAreaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAreaModalLabel">Add Delivery Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="area_name" class="form-label">Area Name</label>
                            <input type="text" class="form-control" id="area_name" name="area_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="area_freight_rate" class="form-label">Freight Rate</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo htmlspecialchars( $default_currency); ?></span>
                                <input type="number" class="form-control" id="area_freight_rate" name="area_freight_rate" 
                                       min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_area" class="btn btn-primary">Save Area</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Area Modal -->
    <div class="modal fade" id="editAreaModal" tabindex="-1" aria-labelledby="editAreaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAreaModalLabel">Edit Delivery Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="area_id" id="edit_area_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_area_name" class="form-label">Area Name</label>
                            <input type="text" class="form-control" id="edit_area_name" name="area_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_area_freight_rate" class="form-label">Freight Rate</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo htmlspecialchars( $default_currency); ?></span>
                                <input type="number" class="form-control" id="edit_area_freight_rate" name="area_freight_rate" 
                                       min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_area" class="btn btn-primary">Update Area</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize the edit modal with data
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sidebar functionality
            initSidebar();
            
            // Handle edit area modal
            var editAreaModal = document.getElementById('editAreaModal');
            if (editAreaModal) {
                editAreaModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    document.getElementById('edit_area_id').value = button.getAttribute('data-id');
                    document.getElementById('edit_area_name').value = button.getAttribute('data-name');
                    document.getElementById('edit_area_freight_rate').value = button.getAttribute('data-rate');
                });
            }
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