<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'toggle_status':
                if (!isset($_POST['review_id'])) {
                    throw new Exception('Review ID missing');
                }
                
                $reviewId = (int)$_POST['review_id'];
                $stmt = $conn->prepare("UPDATE reviews SET active = NOT active WHERE id = ?");
                $stmt->execute([$reviewId]);
                
                // Get updated status
                $stmt = $conn->prepare("SELECT active FROM reviews WHERE id = ?");
                $stmt->execute([$reviewId]);
                $newStatus = $stmt->fetchColumn();
                
                echo json_encode([
                    'success' => true,
                    'newStatus' => $newStatus,
                    'statusText' => $newStatus ? 'Active' : 'Inactive',
                    'statusClass' => $newStatus ? 'success' : 'primary',
                    'buttonText' => $newStatus ? '' : '',
                    'buttonIcon' => $newStatus ? 'eye-slash' : 'eye'
                ]);
                exit;
                
            case 'delete_review':
                if (!isset($_POST['review_id'])) {
                    throw new Exception('Review ID missing');
                }
                
                $reviewId = (int)$_POST['review_id'];
                $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$reviewId]);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'toggle_sidebar':
                $_SESSION['sidebar_open'] = !($_SESSION['sidebar_open'] ?? false);
                echo json_encode(['success' => true, 'state' => $_SESSION['sidebar_open']]);
                exit;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Pagination and filtering
$records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Base query
$query = "SELECT r.*, 
            u.email AS customer_email, 
            CONCAT(p.first_name, ' ', p.last_name) AS customer_name,
            pr.name AS product_name,
            pr.main_image AS product_image
          FROM reviews r
          JOIN users u ON r.user_id = u.id
          JOIN profiles p ON u.id = p.user_id
          JOIN products pr ON r.product_id = pr.id
          WHERE 1=1";

$params = [];
$types = [];

// Add filters
if (!empty($search)) {
    $search_param = "%$search%";
    $query .= " AND (r.review_text LIKE ? OR u.email LIKE ? OR pr.name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?)";
    array_push($params, $search_param, $search_param, $search_param, $search_param);
    array_push($types, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR);
}

if ($status_filter !== 'all') {
    $status_value = ($status_filter === 'active') ? 1 : 0;
    $query .= " AND r.active = ?";
    $params[] = $status_value;
    $types[] = PDO::PARAM_INT;
}

if ($rating_filter > 0) {
    $query .= " AND r.stars = ?";
    $params[] = $rating_filter;
    $types[] = PDO::PARAM_INT;
}

// Count total records first (without pagination)
$count_query = "SELECT COUNT(*) AS total FROM reviews r
                JOIN users u ON r.user_id = u.id
                JOIN profiles p ON u.id = p.user_id
                JOIN products pr ON r.product_id = pr.id
                WHERE 1=1";

$count_params = [];
$count_types = [];

if (!empty($search)) {
    $count_query .= " AND (r.review_text LIKE ? OR u.email LIKE ? OR pr.name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?)";
    array_push($count_params, $search_param, $search_param, $search_param, $search_param);
    array_push($count_types, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR);
}

if ($status_filter !== 'all') {
    $count_query .= " AND r.active = ?";
    $count_params[] = $status_value;
    $count_types[] = PDO::PARAM_INT;
}

if ($rating_filter > 0) {
    $count_query .= " AND r.stars = ?";
    $count_params[] = $rating_filter;
    $count_types[] = PDO::PARAM_INT;
}

$count_stmt = $conn->prepare($count_query);
foreach ($count_params as $i => $param) {
    $count_stmt->bindValue($i + 1, $param, $count_types[$i]);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Add pagination to main query
$query .= " ORDER BY r.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$types[] = PDO::PARAM_INT;
$params[] = $records_per_page;
$types[] = PDO::PARAM_INT;

// Execute main query
$stmt = $conn->prepare($query);
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param, $types[$i]);
}
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
$page_title = 'Manage Reviews';
$current_page = 'Reviews';
require_once __DIR__ . '/includes/header.php';
?>


        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Reviews</h1>
                <div>
                    <a href="reviews.php" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Reset All
                    </a>
                </div>
            </div>

            <!-- Filter and Search Card -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <input type="hidden" name="page" value="1">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search reviews..." value="<?php echo htmlspecialchars($search); ?>">
                                <?php if (!empty($search)): ?>
                                    <a href="reviews.php?status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>&per_page=<?php echo $records_per_page; ?>" class="btn btn-primary" title="Clear search">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select name="rating" class="form-select">
                                <option value="0" <?php echo $rating_filter === 0 ? 'selected' : ''; ?>>All Ratings</option>
                                <option value="1" <?php echo $rating_filter === 1 ? 'selected' : ''; ?>>1 Star</option>
                                <option value="2" <?php echo $rating_filter === 2 ? 'selected' : ''; ?>>2 Stars</option>
                                <option value="3" <?php echo $rating_filter === 3 ? 'selected' : ''; ?>>3 Stars</option>
                                <option value="4" <?php echo $rating_filter === 4 ? 'selected' : ''; ?>>4 Stars</option>
                                <option value="5" <?php echo $rating_filter === 5 ? 'selected' : ''; ?>>5 Stars</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="per_page" class="form-label">Items per page</label>
                            <select name="per_page" class="form-select">
                                <option value="10" <?php echo $records_per_page === 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $records_per_page === 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $records_per_page === 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $records_per_page === 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reviews Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <div class="alert alert-info">No reviews found matching your criteria. <a href="reviews.php" class="alert-link">Show all reviews</a></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="reviewsTable">
                                <thead class="table-light">
                                    <tr>
                                       
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr data-review-id="<?php echo $review['id']; ?>">
                                           
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="images/<?php echo htmlspecialchars($review['product_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($review['product_name']); ?>" 
                                                         class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php echo htmlspecialchars($review['product_name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlspecialchars($review['customer_name']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($review['customer_email']); ?></div>
                                            </td>
                                            <td>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $review['stars'] ? ' text-warning' : ' text-secondary'; ?>"></i>
                                                <?php endfor; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($review['review_text'], 0, 50)); ?><?php echo strlen($review['review_text']) > 50 ? '...' : ''; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $review['active'] ? 'bg-success' : 'bg-primary'; ?>">
                                                    <?php echo $review['active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-primary toggle-review" 
                                                            data-review-id="<?php echo $review['id']; ?>"
                                                            data-current-status="<?php echo $review['active'] ? 'active' : 'inactive'; ?>">
                                                        <i class="fas fa-<?php echo $review['active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                        <?php echo $review['active'] ? '' : ''; ?>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary delete-review" 
                                                            data-review-id="<?php echo $review['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link bg-success" 
                                           href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>" 
                                           aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link bg-success" 
                                           href="?page=<?php echo $i; ?>&per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link bg-success" 
                                           href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>" 
                                           aria-label="Next">
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // 1. Sidebar toggle functionality
        $('#sidebarToggle').click(function(e) {
            e.stopPropagation();
            $('.sidebar, .sidebar-overlay').toggleClass('active');
            $('body').toggleClass('sidebar-open');
            
            // Save state to session
            $.post('reviews.php', {
                action: 'toggle_sidebar'
            });
        });

        // Close sidebar when clicking outside
        $(document).click(function(e) {
            if ($(window).width() < 768 && $('.sidebar').hasClass('active') && 
                !$(e.target).closest('.sidebar').length && 
                !$(e.target).is('#sidebarToggle')) {
                $('.sidebar, .sidebar-overlay').removeClass('active');
                $('body').removeClass('sidebar-open');
                
                // Save state to session
                $.post('reviews.php', {
                    action: 'toggle_sidebar'
                });
            }
        });

        // 2. Toggle review status
        $(document).on('click', '.toggle-review', function() {
            const $btn = $(this);
            const reviewId = $btn.data('review-id');
            
            // Show loading state
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: 'reviews.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'toggle_status',
                    review_id: reviewId
                },
                success: function(response) {
                    if (response.success) {
                        // Update button
                        $btn.html(`<i class="fas fa-${response.buttonIcon}"></i> ${response.buttonText}`);
                        
                        // Update status badge
                        const $badge = $btn.closest('tr').find('.badge');
                        $badge.removeClass('bg-success bg-primary')
                              .addClass('bg-' + response.statusClass)
                              .text(response.statusText);
                    } else {
                        alert(response.error || 'Failed to update status');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.statusText);
                    console.error('AJAX Error:', xhr.responseText);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // 3. Delete review
        $(document).on('click', '.delete-review', function() {
            if (!confirm('Are you sure you want to delete this review?')) {
                return;
            }
            
            const $row = $(this).closest('tr');
            const reviewId = $(this).data('review-id');
            
            $row.css('opacity', '0.5');
            
            $.ajax({
                url: 'reviews.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'delete_review',
                    review_id: reviewId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.error || 'Failed to delete review');
                        $row.css('opacity', '1');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.statusText);
                    $row.css('opacity', '1');
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
        });
    });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>