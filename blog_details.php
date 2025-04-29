<?php
// Include the database connection file
include 'db.php';

// Get blog ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get blog details
$stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    header('Location: blog.php');
    exit;
}

// Get related blogs (same type, excluding current blog)
$stmt = $conn->prepare("SELECT id, title, image, date FROM blogs WHERE type = ? AND id != ? ORDER BY date DESC LIMIT 3");
$stmt->execute([$blog['type'], $id]);
$relatedBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get next blog (newer)
$stmt = $conn->prepare("SELECT id, title, image FROM blogs WHERE date > ? AND id != ? ORDER BY date ASC LIMIT 1");
$stmt->execute([$blog['date'], $id]);
$nextBlog = $stmt->fetch(PDO::FETCH_ASSOC);

// Get previous blog (older)
$stmt = $conn->prepare("SELECT id, title, image FROM blogs WHERE date < ? AND id != ? ORDER BY date DESC LIMIT 1");
$stmt->execute([$blog['date'], $id]);
$prevBlog = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = $blog['title'];
$current_page = 'blog';
require_once 'includes/header.php';

?>

<div class="container py-5">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item"><a href="blog.php">Blogs</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
    


 <!-- Blog Header -->
 <div class="row mb-5">
        <div class="col-12 text-center">
            <span class="badge bg-primary mb-3"><?= ucfirst($blog['type']) ?></span>
            <h1 class="display-4 fw-bold"><?= htmlspecialchars($blog['title']) ?></h1>
            <div class="d-flex justify-content-center align-items-center mt-3">
                <span class="text-muted me-3"><i class="far fa-calendar-alt me-1"></i> <?= date('F j, Y', strtotime($blog['date'])) ?></span>
                <span class="text-muted"><i class="far fa-clock me-1"></i> <?= ceil(str_word_count($blog['content']) / 200) ?> min read</span>
            </div>
        </div>
    </div>
    
    <!-- Featured Image -->
    <div class="row mb-5">
        <div class="col-12">
            <img src="admin/Blogs/<?= htmlspecialchars($blog['image']) ?>" class="img-fluid rounded-3 shadow" alt="<?= htmlspecialchars($blog['title']) ?>">
        </div>
    </div>
    
    <!-- Blog Content -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="blog-content">
                <?= nl2br(htmlspecialchars($blog['content'])) ?>
            </div>
            
            <!-- Tags or Categories -->
            <div class="d-flex flex-wrap gap-2 mt-5 mb-4">
                <span class="badge bg-light text-dark">#<?= $blog['type'] ?></span>
                <span class="badge bg-light text-dark">#trending</span>
                <span class="badge bg-light text-dark">#<?= date('Y', strtotime($blog['date'])) ?></span>
            </div>
            
            <hr class="my-5">
            
            <!-- Author Box (optional) -->
            <div class="d-flex align-items-center bg-light p-4 rounded-3 mb-5">
                <img src="https://ui-avatars.com/api/?name=Admin&background=random" class="rounded-circle me-3" width="80" alt="Author">
                <div>
                    <h5 class="mb-1">Admin</h5>
                    <p class="text-muted mb-2">Content Writer & Editor</p>
                    <p class="small mb-0">Bringing you the latest insights and stories in <?= $blog['type'] ?>.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Articles -->
    <?php if (!empty($relatedBlogs)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Related Articles</h3>
            <div class="row g-4">
                <?php foreach ($relatedBlogs as $related): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="admin/Blogs/<?= htmlspecialchars($related['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($related['title']) ?>" style="height: 180px; object-fit: cover;">
                        <div class="card-body">
                            <small class="text-muted"><?= date('M j, Y', strtotime($related['date'])) ?></small>
                            <h5 class="card-title mt-2"><?= htmlspecialchars($related['title']) ?></h5>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="blog_details.php?id=<?= $related['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Read More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Next/Previous Navigation -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <?php if ($prevBlog): ?>
                <a href="blog_details.php?id=<?= $prevBlog['id'] ?>" class="card border-0 shadow-sm text-decoration-none" style="width: 48%;">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <img src="admin/Blogs/<?= htmlspecialchars($prevBlog['image']) ?>" class="rounded" width="80" height="80" style="object-fit: cover;" alt="<?= htmlspecialchars($prevBlog['title']) ?>">
                        </div>
                        <div>
                            <small class="text-muted"><i class="fas fa-arrow-left me-1"></i> Previous</small>
                            <h6 class="mb-0 mt-1"><?= htmlspecialchars($prevBlog['title']) ?></h6>
                        </div>
                    </div>
                </a>
                <?php else: ?>
                <div style="width: 48%;"></div>
                <?php endif; ?>
                
                <?php if ($nextBlog): ?>
                <a href="blog_details.php?id=<?= $nextBlog['id'] ?>" class="card border-0 shadow-sm text-decoration-none text-end" style="width: 48%;">
                    <div class="card-body d-flex align-items-center justify-content-end">
                        <div class="text-end me-3">
                            <small class="text-muted">Next <i class="fas fa-arrow-right ms-1"></i></small>
                            <h6 class="mb-0 mt-1"><?= htmlspecialchars($nextBlog['title']) ?></h6>
                        </div>
                        <div>
                            <img src="admin/Blogs/<?= htmlspecialchars($nextBlog['image']) ?>" class="rounded" width="80" height="80" style="object-fit: cover;" alt="<?= htmlspecialchars($nextBlog['title']) ?>">
                        </div>
                    </div>
                </a>
                <?php else: ?>
                <div style="width: 48%;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>


<style>
    .blog-content {
        font-size: 1.1rem;
        line-height: 1.8;
    }
    .blog-content p {
        margin-bottom: 1.5rem;
    }
</style>









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