<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Get search term if exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$records_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Build the base query
$query = "
    SELECT 
        u.id, 
        u.email, 
        u.phone, 
        u.created_at AS registration_date, 
        da.country, 
        da.city, 
        da.street, 
        pr.first_name, 
        pr.last_name,
        cg.name AS customer_group,
        cg.id AS group_id,
        COUNT(o.user_id) AS order_count, 
        SUM(o.grand_total) AS total_spent
    FROM 
        users u
    LEFT JOIN 
        delivery_addresses da ON u.id = da.user_id
    LEFT JOIN 
        profiles pr ON u.id = pr.user_id
    LEFT JOIN 
        orders o ON u.id = o.user_id
    LEFT JOIN
        customer_groups cg ON u.group_id = cg.id
";

// Add search condition if search term exists
$params = [];
if (!empty($search)) {
    $query .= " WHERE CONCAT(pr.first_name, ' ', pr.last_name) LIKE ? 
                OR u.email LIKE ? 
                OR u.phone LIKE ? 
                OR CONCAT(da.city, ' ', da.country) LIKE ? 
                OR cg.name LIKE ?";
    $search_term = '%' . $search . '%';
    $params = array_fill(0, 5, $search_term);
}

// Complete the query
$query .= "
    GROUP BY 
        u.id
    ORDER BY
        u.created_at DESC
    LIMIT 
        ?, ?
";

// Prepare and execute the query
$stmt = $conn->prepare($query);

// Bind parameters
$param_index = 1;
if (!empty($search)) {
    foreach ($params as $param) {
        $stmt->bindValue($param_index++, $param, PDO::PARAM_STR);
    }
}
$stmt->bindValue($param_index++, $offset, PDO::PARAM_INT);
$stmt->bindValue($param_index++, $records_per_page, PDO::PARAM_INT);

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total records for pagination
$count_query = "
    SELECT COUNT(DISTINCT u.id) AS total_records
    FROM users u
    LEFT JOIN delivery_addresses da ON u.id = da.user_id
    LEFT JOIN profiles pr ON u.id = pr.user_id
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN customer_groups cg ON u.group_id = cg.id
";

$count_params = [];
if (!empty($search)) {
    $count_query .= " WHERE CONCAT(pr.first_name, ' ', pr.last_name) LIKE ? 
                      OR u.email LIKE ? 
                      OR u.phone LIKE ? 
                      OR CONCAT(da.city, ' ', da.country) LIKE ? 
                      OR cg.name LIKE ?";
    $count_params = array_fill(0, 5, $search_term);
}

$count_stmt = $conn->prepare($count_query);
if (!empty($search)) {
    foreach ($count_params as $index => $param) {
        $count_stmt->bindValue($index + 1, $param, PDO::PARAM_STR);
    }
}

$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total_records'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch all customer groups for the dropdown
$groups_query = "SELECT id, name FROM customer_groups";
$groups_stmt = $conn->query($groups_query);
$customer_groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to modify URL parameters
function modify_url($key, $value) {
    $query = $_GET;
    $query[$key] = $value;
    return '?' . http_build_query($query);
}
$page_title = 'Manage Customers';
$current_page = 'Customers';
require_once __DIR__ . '/includes/header.php';
?>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Customers</h1>
                <div>
                    <a href="customer_groups.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> Manage Groups
                    </a>
                </div>
            </div>
            
            <!-- Search Box -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="searchForm" method="get" action="customers.php">
                        <input type="hidden" name="page" value="1">
                        <div class="input-group">
                            <input type="text" id="customerSearch" name="search" class="form-control" 
                                   placeholder="Search customers by name, email, phone, or location..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Customers Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <?php if (!empty($search)): ?>
                        <div class="alert alert-info mb-3">
                            Showing results for: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                            <a href="customers.php" class="float-end">Clear search</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="customersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Phone</th>
                                    <th>Group</th>
                                    <th>Registration</th>
                                    <th>Location</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="customersTableBody">
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <?php echo empty($search) ? 'No customers found' : 'No customers match your search'; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><a href="customer_details.php?id=<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                            </a>
                                            <div class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></div>
                                        
                                        
                                        
                                        </td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($user['customer_group'] ?? 'No Group'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['registration_date'])); ?></td>
                                            <td>
                                                <?php if ($user['city']): ?>
                                                    <?php echo htmlspecialchars($user['city']); ?>, <?php echo htmlspecialchars($user['country']); ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $user['order_count']; ?></td>
                                            <td><?php echo htmlspecialchars($default_currency); echo number_format($user['total_spent'], 2); ?></td>
                                            <td>
                                                <a href="customer_details.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo modify_url('page', $page - 1); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo modify_url('page', $i); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo modify_url('page', $page + 1); ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      
    <!-- Custom Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize sidebar functionality
        initSidebar();   
        
        // Initialize customer search functionality
        initCustomerSearch();
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

    function initCustomerSearch() {
        const searchInput = document.getElementById('customerSearch');
        const clearButton = document.getElementById('clearSearch');
        const searchForm = document.getElementById('searchForm');
        
        if (!searchInput || !clearButton || !searchForm) return;
        
        // Focus on search input when page loads
        searchInput.focus();
        
        // Clear button functionality
        clearButton.addEventListener('click', function() {
            window.location.href = 'customers.php';
        });
        
        // Allow pressing ESC to clear search
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'customers.php';
            }
        });
    }
    </script>
</body>
</html>