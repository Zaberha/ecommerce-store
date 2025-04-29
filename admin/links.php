<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


// Path to footer.php
$footer_file = __DIR__ . '/../includes/footer.php';
$footer_links = [];

// Function to extract links from footer content (only from <ul id="links">)
function extractFooterLinks($content) {
    $links = [];
    // First extract the ul#links section
    if (preg_match('/<ul[^>]*id="links"[^>]*>(.*?)<\/ul>/is', $content, $ul_matches)) {
        // Then extract all links from within this ul
        preg_match_all('/<li[^>]*>\s*<a\s+href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>\s*<\/li>/i', $ul_matches[1], $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $links[] = [
                'text' => htmlspecialchars_decode($match[2]),
                'url' => htmlspecialchars_decode($match[1])
            ];
        }
    }
    return $links;
}

// Read existing footer links
if (file_exists($footer_file)) {
    $footer_content = file_get_contents($footer_file);
    $footer_links = extractFooterLinks($footer_content);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_social_links'])) {
            // Update social media links
            $stmt = $conn->prepare("
                UPDATE admin SET 
                facebook_link = :facebook_link,
                instagram_link = :instagram_link,
                x_link = :x_link,
                tiktok_link = :tiktok_link,
                snapchat_link = :snapchat_link,
                linkedin_link = :linkedin_link,
                google_business_link = :google_business_link,
                youtube_channel_link = :youtube_channel_link
                WHERE id = 1
            ");
            
            $stmt->execute([
                ':facebook_link' => $_POST['facebook_link'] ?? null,
                ':instagram_link' => $_POST['instagram_link'] ?? null,
                ':x_link' => $_POST['x_link'] ?? null,
                ':tiktok_link' => $_POST['tiktok_link'] ?? null,
                ':snapchat_link' => $_POST['snapchat_link'] ?? null,
                ':linkedin_link' => $_POST['linkedin_link'] ?? null,
                ':google_business_link' => $_POST['google_business_link'] ?? null,
                ':youtube_channel_link' => $_POST['youtube_channel_link'] ?? null
            ]);
            
            $_SESSION['message'] = 'Social links updated successfully!';
            $_SESSION['message_type'] = 'success';
            header("Location: links.php");
            exit();
        }
        
        if (isset($_POST['update_footer_links'])) {
            // Process footer links from form
            $links = $_POST['links'] ?? [];
            $valid_links = [];
            
            foreach ($links as $link) {
                if (!empty($link['text']) && !empty($link['url'])) {
                    $valid_links[] = [
                        'text' => htmlspecialchars($link['text'], ENT_QUOTES),
                        'url' => htmlspecialchars($link['url'], ENT_QUOTES)
                    ];
                }
            }
            
            // Update footer.php file
            if (file_exists($footer_file)) {
                // Read the existing footer.php content
                $footer_content = file_get_contents($footer_file);
                
                // Generate new links HTML with proper list item structure
                $new_links_html = '';
                foreach ($valid_links as $link) {
                    $new_links_html .= '<li><a href="' . $link['url'] . '">' . $link['text'] . '</a></li>' . "\n";
                }
                
                // Replace the entire ul#links section
                $updated_content = preg_replace(
                    '/(<ul[^>]*id="links"[^>]*>)(.*?)(<\/ul>)/is',
                    '$1' . "\n" . $new_links_html . '$3',
                    $footer_content
                );
                
                // Save the updated content back to footer.php
                if (file_put_contents($footer_file, $updated_content)) {
                    $_SESSION['message'] = 'Footer links updated successfully!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    throw new Exception("Failed to write to footer file");
                }
                
                header("Location: links.php");
                exit();
            } else {
                throw new Exception("Footer file not found");
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        header("Location: links.php");
        exit();
    }
}
// Fetch admin/store settings
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$page_title = 'Links Management';
$current_page = 'Links';
require_once __DIR__ . '/includes/header.php';
?>


        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Links Management</h1>
            </div>

            <!-- Message Alerts -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'success'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Footer Links Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Footer Links</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div id="footer-links">
                            <?php foreach ($footer_links as $index => $link): ?>
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Link Text</label>
                                        <input type="text" class="form-control" name="links[<?php echo $index; ?>][text]" 
                                               value="<?php echo htmlspecialchars($link['text'], ENT_QUOTES); ?>" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Link URL</label>
                                        <input type="text" class="form-control" name="links[<?php echo $index; ?>][url]" 
                                               value="<?php echo htmlspecialchars($link['url'], ENT_QUOTES); ?>" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary btn-remove-link" data-index="<?php echo $index; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label class="form-label">New Link Text</label>
                                <input type="text" class="form-control" id="new_link_text">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">New Link URL</label>
                                <input type="text" class="form-control" id="new_link_url">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" id="btn-add-link">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" name="update_footer_links" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Footer Links
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Social Media Links Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Social Media Links</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="facebook_link" class="form-label">Facebook Link</label>
                                    <input type="text" class="form-control" id="facebook_link" name="facebook_link" 
                                           value="<?php echo htmlspecialchars($admin['facebook_link'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="instagram_link" class="form-label">Instagram Link</label>
                                    <input type="text" class="form-control" id="instagram_link" name="instagram_link" 
                                           value="<?php echo htmlspecialchars($admin['instagram_link'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="x_link" class="form-label">X (Twitter) Link</label>
                                    <input type="text" class="form-control" id="x_link" name="x_link" 
                                           value="<?php echo htmlspecialchars($admin['x_link'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="tiktok_link" class="form-label">TikTok Link</label>
                                    <input type="text" class="form-control" id="tiktok_link" name="tiktok_link" 
                                           value="<?php echo htmlspecialchars($admin['tiktok_link'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="snapchat_link" class="form-label">Snapchat Link</label>
                                    <input type="text" class="form-control" id="snapchat_link" name="snapchat_link" 
                                           value="<?php echo htmlspecialchars($admin['snapchat_link'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="linkedin_link" class="form-label">LinkedIn Link</label>
                                    <input type="text" class="form-control" id="linkedin_link" name="linkedin_link" 
                                           value="<?php echo htmlspecialchars($admin['linkedin_link'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="google_business_link" class="form-label">Google Business Link</label>
                                    <input type="text" class="form-control" id="google_business_link" name="google_business_link" 
                                           value="<?php echo htmlspecialchars($admin['google_business_link'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="youtube_channel_link" class="form-label">YouTube Channel Link</label>
                                    <input type="text" class="form-control" id="youtube_channel_link" name="youtube_channel_link" 
                                           value="<?php echo htmlspecialchars($admin['youtube_channel_link'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" name="update_social_links" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Social Links
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
        const mainContent = document.getElementById('mainContent');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            mainContent.classList.toggle('active');
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

        // Footer links management
        const footerLinksContainer = document.getElementById('footer-links');
        const btnAddLink = document.getElementById('btn-add-link');
        
        if (btnAddLink && footerLinksContainer) {
            btnAddLink.addEventListener('click', function() {
                const text = document.getElementById('new_link_text').value;
                const url = document.getElementById('new_link_url').value;
                
                if (text && url) {
                    const index = Date.now(); // Unique index
                    
                    const linkHtml = `
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Link Text</label>
                                <input type="text" class="form-control" name="links[${index}][text]" value="${text.replace(/"/g, '&quot;')}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Link URL</label>
                                <input type="text" class="form-control" name="links[${index}][url]" value="${url.replace(/"/g, '&quot;')}" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-remove-link">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    
                    footerLinksContainer.insertAdjacentHTML('beforeend', linkHtml);
                    
                    // Clear inputs
                    document.getElementById('new_link_text').value = '';
                    document.getElementById('new_link_url').value = '';
                } else {
                    alert('Please enter both link text and URL');
                }
            });
            
            // Remove link handler
            footerLinksContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-link') || e.target.closest('.btn-remove-link')) {
                    const row = e.target.closest('.row.mb-3');
                    if (row) {
                        row.remove();
                    }
                }
            });
        }
    });
    </script>
</body>
</html>