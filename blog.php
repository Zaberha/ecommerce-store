<?php
// Include the database connection file
include 'db.php';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Prepare SQL query
$where = '';
$params = [];

if (!empty($filter)) {
    $where = "WHERE type = ?";
    $params = [$filter];
}

// Get all blogs
$stmt = $conn->prepare("SELECT * FROM blogs $where ORDER BY date DESC LIMIT 12");
$stmt->execute($params);
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique types for filter
$types = $conn->query("SELECT DISTINCT type FROM blogs ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Blog';
$current_page = 'blog';
require_once 'includes/header.php';

?>

<div class="container py-5">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
    





<div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="fw-bold">Our Blog</h2>
            <p class="lead">Discover the latest insights and stories</p>
        </div>
    </div>
    
    <!-- Filter Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="blog.php" class="btn btn-primary <?= empty($filter) ? 'active' : '' ?>">
                    All Articles
                </a>
                <?php foreach ($types as $type): ?>
                    <a href="blog.php?filter=<?= urlencode($type) ?>" class="btn btn-primary <?= $filter === $type ? 'active' : '' ?>">
                        <?= ucfirst($type) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Blog Grid -->
    <div class="row g-4">
        <?php if (empty($blogs)): ?>
            <div class="col-12 text-center py-5">
                <h3 class="text-muted">No blog articles found</h3>
            </div>
        <?php else: ?>
            <?php foreach ($blogs as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 overflow-hidden">
                        <div class="position-relative">
                            <img src="admin/Blogs/<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['title']) ?>" style="height: 200px; object-fit: cover;">
                            <span class="badge bg-primary position-absolute top-0 end-0 m-2">
                                <?= ucfirst($item['type']) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <small class="text-muted"><?= date('F j, Y', strtotime($item['date'])) ?></small>
                            <h5 class="card-title mt-2"><?= htmlspecialchars($item['title']) ?></h5>
                            <p class="card-text"><?= substr(htmlspecialchars($item['description']), 0, 100) ?>...</p>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="blog_details.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary">
                                Read More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>




</div>


<?php require_once 'includes/footer.php'; ?>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
</body>
            </html>