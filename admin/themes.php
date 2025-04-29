<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Verify database connection
if (!$conn) {
    die("Database connection failed: " . print_r($conn->errorInfo(), true));
}

// Fetch admin settings
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
if (!$stmt->execute()) {
    die("Failed to fetch admin settings: " . print_r($stmt->errorInfo(), true));
}
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_theme_settings'])) {
    try {
        // Verify required fields
        $required = ['main_color', 'second_color', 'third_color', 'forth_color', 'font_family'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Get form data with proper field names (note 'forth_color' not 'fourth_color')
        $main_color = $_POST['main_color'];
        $second_color = $_POST['second_color'];
        $third_color = $_POST['third_color'];
        $forth_color = $_POST['forth_color']; // Corrected to match your DB column
        $font_family = $_POST['font_family'];
        $business_logo = $admin['business_logo'] ?? null;

        // Handle logo upload
        if (!empty($_FILES['business_logo']['name'])) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("Failed to create upload directory");
                }
            }

            $file_ext = pathinfo($_FILES['business_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['business_logo']['tmp_name'], $target_path)) {
                throw new Exception("Failed to move uploaded file");
            }

            // Delete old logo if exists
            if (!empty($admin['business_logo']) && file_exists(__DIR__ . '/' . $admin['business_logo'])) {
                unlink(__DIR__ . '/' . $admin['business_logo']);
            }

            $business_logo = 'uploads/' . $filename;
        }

        // Prepare SQL with correct column names
        $sql = "UPDATE admin SET 
                main_color = :main_color,
                second_color = :second_color,
                third_color = :third_color,
                forth_color = :forth_color,  /* Note: 'forth_color' not 'fourth_color' */
                font_family = :font_family,
                business_logo = :business_logo
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . print_r($conn->errorInfo(), true));
        }

        $success = $stmt->execute([
            ':main_color' => $main_color,
            ':second_color' => $second_color,
            ':third_color' => $third_color,
            ':forth_color' => $forth_color,  // Corrected to match your DB column
            ':font_family' => $font_family,
            ':business_logo' => $business_logo,
            ':id' => $admin['id']
        ]);

        if (!$success) {
            throw new Exception("Execute failed: " . print_r($stmt->errorInfo(), true));
        }

        if ($stmt->rowCount() === 0) {
            throw new Exception("No rows were updated - check if record with id={$admin['id']} exists");
        }

        $_SESSION['message'] = 'Theme settings updated successfully!';
        $_SESSION['message_type'] = 'success';
        header("Location: themes.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("THEME UPDATE ERROR: " . $e->getMessage());
        header("Location: themes.php");
        exit();
    }
}
$page_title = 'Theme Settings';
$current_page = 'Themes';
require_once __DIR__ . '/includes/header.php';
?>


        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Theme Settings</h1>
            </div>

            <!-- Message Alerts -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'success'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Theme Settings Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customize Your Theme</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Logo Upload Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">Business Logo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="business_logo" class="form-label">Upload New Logo</label>
                                            <input class="form-control" type="file" id="business_logo" name="business_logo" accept="image/*">
                                            <small class="text-muted">Recommended size: 300x100px (PNG with transparent background)</small>
                                        </div>
                                        
                                        <?php if (!empty($admin['business_logo'])): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Current Logo Preview</label>
                                            <div class="border p-2 text-center">
                                                <img src="<?php echo htmlspecialchars($admin['business_logo']); ?>" alt="Current Logo" class="img-fluid" style="max-height: 100px;">
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Color Settings Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">Color Scheme</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label for="main_color" class="form-label">Main Color</label>
                                                <input type="color" class="form-control form-control-color" id="main_color" name="main_color" value="<?php echo htmlspecialchars($admin['main_color'] ?? '#28a745'); ?>" title="Primary brand color">
                                            </div>
                                            <div class="col-6">
                                                <label for="second_color" class="form-label">Secondary Color</label>
                                                <input type="color" class="form-control form-control-color" id="second_color" name="second_color" value="<?php echo htmlspecialchars($admin['second_color'] ?? '#007bff'); ?>" title="Secondary accent color">
                                            </div>
                                            <div class="col-6">
                                                <label for="third_color" class="form-label">Tertiary Color</label>
                                                <input type="color" class="form-control form-control-color" id="third_color" name="third_color" value="<?php echo htmlspecialchars($admin['third_color'] ?? '#dc3545'); ?>" title="Highlight/alert color">
                                            </div>
                                            <div class="col-6">
                                                <label for="forth_color" class="form-label">Fourth Color</label>
                                                <input type="color" class="form-control form-control-color" id="forth_color" name="forth_color" value="<?php echo htmlspecialchars($admin['forth_color'] ?? '#6c757d'); ?>" title="Additional accent color">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Font Settings Section -->
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Typography</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="font_family" class="form-label">Primary Font Family</label>
                                                    <select class="form-select" id="font_family" name="font_family">
                                                        <option value="'Arial', sans-serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Arial', sans-serif" ? 'selected' : '' ?>>Arial</option>
                                                        <option value="'Helvetica', sans-serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Helvetica', sans-serif" ? 'selected' : '' ?>>Helvetica</option>
                                                        <option value="'Verdana', sans-serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Verdana', sans-serif" ? 'selected' : '' ?>>Verdana</option>
                                                        <option value="'Tahoma', sans-serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Tahoma', sans-serif" ? 'selected' : '' ?>>Tahoma</option>
                                                        <option value="'Trebuchet MS', sans-serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Trebuchet MS', sans-serif" ? 'selected' : '' ?>>Trebuchet MS</option>
                                                        <option value="'Garamond', serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Garamond', serif" ? 'selected' : '' ?>>Garamond</option>
                                                        <option value="'Georgia', serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Georgia', serif" ? 'selected' : '' ?>>Georgia</option>
                                                        <option value="'Times New Roman', serif" <?= ($admin['font_family'] ?? "'Arial', sans-serif") == "'Times New Roman', serif" ? 'selected' : '' ?>>Times New Roman</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Font Preview</label>
                                                    <div class="p-3 border rounded" id="fontPreview" style="font-family: <?php echo htmlspecialchars($admin['font_family'] ?? "'Arial', sans-serif"); ?>">
                                                        The quick brown fox jumps over the lazy dog
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" name="update_theme_settings" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save All Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        }
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSidebar();
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }

        // Font preview update
        const fontSelect = document.getElementById('font_family');
        const fontPreview = document.getElementById('fontPreview');
        
        if (fontSelect && fontPreview) {
            fontSelect.addEventListener('change', function() {
                fontPreview.style.fontFamily = this.value;
            });
        }

        // Form submission debug
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                console.log('Form values being submitted:', {
                    main_color: this.main_color.value,
                    second_color: this.second_color.value,
                    third_color: this.third_color.value,
                    forth_color: this.forth_color.value,
                    font_family: this.font_family.value,
                    has_logo: this.business_logo.files.length > 0
                });
            });
        }
    });
    </script>
</body>
</html>