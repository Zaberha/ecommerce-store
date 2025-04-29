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
    if (isset($_POST['add_news'])) {
        // Add new news
        $date = $_POST['date'];
        $header = $_POST['header'];
        $description = $_POST['description'];
        $link = $_POST['link'];
        $link_caption = $_POST['link_caption'];
        $type = $_POST['type'];
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'News/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = $fileName;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO news (date, header, description, link, link_caption, type, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$date, $header, $description, $link, $link_caption, $type, $image]);
        
        $_SESSION['message'] = 'News added successfully!';
        header('Location: admin_news.php');
        exit;
    } elseif (isset($_POST['update_news'])) {
        // Update existing news
        $id = $_POST['id'];
        $date = $_POST['date'];
        $header = $_POST['header'];
        $description = $_POST['description'];
        $link = $_POST['link'];
        $link_caption = $_POST['link_caption'];
        $type = $_POST['type'];
        
        // Handle image update
        $image = $_POST['existing_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'News/';
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Delete old image if it exists
                if ($image && file_exists($uploadDir . $image)) {
                    unlink($uploadDir . $image);
                }
                $image = $fileName;
            }
        }
        
        $stmt = $conn->prepare("UPDATE news SET date=?, header=?, description=?, link=?, link_caption=?, type=?, image=? WHERE id=?");
        $stmt->execute([$date, $header, $description, $link, $link_caption, $type, $image, $id]);
        
        $_SESSION['message'] = 'News updated successfully!';
        header('Location: admin_news.php');
        exit;
    } elseif (isset($_POST['delete_news'])) {
        // Delete news
        $id = $_POST['id'];
        
        // Get image path to delete it
        $stmt = $conn->prepare("SELECT image FROM news WHERE id = ?");
        $stmt->execute([$id]);
        $news = $stmt->fetch();
        
        if ($news && $news['image']) {
            $imagePath = 'News/' . $news['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = 'News deleted successfully!';
        header('Location: admin_news.php');
        exit;
    }
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE header LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Get all news
$stmt = $conn->prepare("SELECT * FROM news $where ORDER BY date DESC");
$stmt->execute($params);
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'News Management';
$current_page = 'News';
require_once __DIR__ . '/includes/header.php';

?>

        <div class="container-fluid px-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage News</h1>
            </div>

        

            <div class="container-fluid py-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Manage News</h6>
                    <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                        <i class="fas fa-plus me-1"></i> Add News
                    </button>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="px-4 py-3">
                        <form method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search news..." name="search" value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Image</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Header</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <img src="News/<?= htmlspecialchars($item['image']) ?>" class="img-thumbnail" style="max-height: 150px;" alt="<?= htmlspecialchars($item['header']) ?>">
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($item['header']) ?></h6>
                                            <p class="text-xs text-secondary mb-0"><?= substr(htmlspecialchars($item['description']), 0, 50) ?>...</p>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary text-xs font-weight-bold"><?= date('M d, Y', strtotime($item['date'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm 
                                            <?= $item['type'] === 'new release' ? 'bg-primary' : 
                                               ($item['type'] === 'promotion' ? 'bg-info' : 
                                               ($item['type'] === 'event' ? 'bg-warning' : 'bg-success')) ?>">
                                            <?= ucfirst($item['type']) ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editNewsModal<?= $item['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#deleteNewsModal<?= $item['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Edit News Modal -->
                                <div class="modal fade" id="editNewsModal<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit News</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <input type="hidden" name="existing_image" value="<?= $item['image'] ?>">
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Date</label>
                                                            <input type="date" class="form-control" name="date" value="<?= $item['date'] ?>" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Type</label>
                                                            <select class="form-select" name="type" required>
                                                                <option value="new release" <?= $item['type'] === 'new release' ? 'selected' : '' ?>>New Release</option>
                                                                <option value="promotion" <?= $item['type'] === 'promotion' ? 'selected' : '' ?>>Promotion</option>
                                                                <option value="event" <?= $item['type'] === 'event' ? 'selected' : '' ?>>Event</option>
                                                                <option value="new" <?= $item['type'] === 'news' ? 'selected' : '' ?>>News</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Header</label>
                                                        <input type="text" class="form-control" name="header" value="<?= htmlspecialchars($item['header']) ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($item['description']) ?></textarea>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Link (optional)</label>
                                                            <input type="text" class="form-control" name="link" value="<?= htmlspecialchars($item['link']) ?>">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Link Caption (optional)</label>
                                                            <input type="text" class="form-control" name="link_caption" value="<?= htmlspecialchars($item['link_caption']) ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Image</label>
                                                        <input type="file" class="form-control" name="image" accept="image/*">
                                                        <small class="text-muted">Current image: <?= $item['image'] ?></small>
                                                        <?php if ($item['image']): ?>
                                                            <div class="mt-2">
                                                                <img src="News/<?= $item['image'] ?>" class="img-thumbnail" style="max-height: 150px;">
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="update_news" class="btn btn-primary">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Delete News Modal -->
                                <div class="modal fade" id="deleteNewsModal<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <p>Are you sure you want to delete this news item: <strong><?= htmlspecialchars($item['header']) ?></strong>?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="delete_news" class="btn btn-primary">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New News</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="new release">New Release</option>
                                <option value="promotion">Promotion</option>
                                <option value="event">Event</option>
                                <option value="news">News</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Header</label>
                        <input type="text" class="form-control" name="header" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link (optional)</label>
                            <input type="url" class="form-control" name="link">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link Caption (optional)</label>
                            <input type="text" class="form-control" name="link_caption">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_news" class="btn btn-primary">Add News</button>
                </div>
            </form>
        </div>
    </div>
</div>



        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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