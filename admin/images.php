<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


// Image Upload and Replacement Script
$targetDirectory = "../assets/img/"; // Directory where images are stored
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Allowed image formats

// Function to get image dimensions
function getImageDimensions($path) {
    $dimensions = getimagesize($path);
    return $dimensions ? "{$dimensions[0]} × {$dimensions[1]} px" : "Unknown dimensions";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    // Check if file was uploaded without errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($file['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileTmp = $file['tmp_name'];
        
        // Validate file extension
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['message'] = "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
            $_SESSION['message_type'] = 'danger';
        } elseif (isset($_POST['original_filename']) && !empty($_POST['original_filename'])) {
            $originalName = $_POST['original_filename'];
            $originalImage = glob($targetDirectory . $originalName . '.{jpg,jpeg,png,gif}', GLOB_BRACE);
            
            if (!empty($originalImage)) {
                $originalExtension = strtolower(pathinfo($originalImage[0], PATHINFO_EXTENSION));
                
                // Check if new image has same format as original
                if ($fileExtension !== $originalExtension) {
                    $_SESSION['message'] = "Please keep the same format ($originalExtension) and try to use same size, image is not replaced!";
                    $_SESSION['message_type'] = 'danger';
                } else {
                    // Delete old file
                    unlink($originalImage[0]);
                    
                    // Move uploaded file to target directory with original name
                    $targetFile = $targetDirectory . $originalName . '.' . $fileExtension;
                    if (move_uploaded_file($fileTmp, $targetFile)) {
                        $dimensions = getImageDimensions($targetFile);
                        $_SESSION['message'] = "Image successfully replaced while keeping the original name!<br>New dimensions: $dimensions";
                        $_SESSION['message_type'] = 'success';
                        
                        // Redirect to prevent form resubmission
                        header("Location: ".$_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $_SESSION['message'] = "Error uploading file.";
                        $_SESSION['message_type'] = 'danger';
                    }
                }
            } else {
                $_SESSION['message'] = "Original image not found!";
                $_SESSION['message_type'] = 'danger';
            }
        }
    } else {
        $_SESSION['message'] = "Error: " . $file['error'];
        $_SESSION['message_type'] = 'danger';
    }
    
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
$page_title = 'Images Management';
$current_page = 'Images';
require_once __DIR__ . '/includes/header.php';
?>

    <style>
        .image-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .image-card { border: 1px solid #ddd; padding: 10px; text-align: center; }
        .image-card img { max-width: 100%; height: auto; margin-bottom: 10px; }
        .dimensions { color: #666; font-size: 0.9em; margin: 5px 0; }
        .message { padding: 10px; margin: 20px 0; background: #f0f0f0; }
    </style>


        <div class="container-fluid px-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Images</h1>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $message_type ?> alert-dismissible fade show mb-4">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Existing Images</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (glob($targetDirectory . "*.{jpg,jpeg,png,gif}", GLOB_BRACE) as $image): ?>
                            <?php 
                            $filename = pathinfo($image, PATHINFO_FILENAME);
                            $extension = pathinfo($image, PATHINFO_EXTENSION);
                            $dimensions = getImageDimensions($image);
                            ?>
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-img-top d-flex align-items-center justify-content-center p-3" style="height: 200px; background-color: #f8f9fa;">
                                        <img src="<?= $image ?>" class="img-fluid" alt="<?= $filename ?>" style="max-height: 100%; width: auto;">
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title text-truncate"><?= $filename ?></h5>
                                        <p class="card-text text-muted small mb-1"><?= $dimensions ?></p>
                                        <p class="card-text text-muted small"><?= strtoupper($extension) ?> format</p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <form method="post" enctype="multipart/form-data" class="mb-0">
                                            <input type="hidden" name="original_filename" value="<?= $filename ?>">
                                            <div class="mb-2">
                                                <input type="file" class="form-control form-control-sm" name="image" accept="image/*" required>
                                                <div class="form-text small">Must be <?= strtoupper($extension) ?> format</div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-sync-alt me-1"></i> Replace Image
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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