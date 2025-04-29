<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
// Get current settings
$settings = [];
try {
    $stmt = $conn->query("SELECT * FROM alert_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error = "Error fetching settings: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Delete all existing settings
        $conn->query("DELETE FROM alert_settings");
        
        // Insert new settings
        $stmt = $conn->prepare("INSERT INTO alert_settings (setting_key, setting_value) VALUES (?, ?)");
        
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        
        $conn->commit();
        $success = "Alert settings updated successfully!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// Get notification methods
$notification_methods = ['email', 'sms', 'dashboard', 'slack', 'webhook'];
$page_title = 'Alerts settings';
$current_page = 'Alerts Settings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Alert Settings</h1>
        <a href="inventory_alerts.php" class="d-none d-sm-inline-block btn btn-sm btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Alerts
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Notification Settings</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label>Notification Methods</label>
                    <div class="row">
                        <?php foreach ($notification_methods as $method): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        id="notify_<?= $method ?>" name="settings[notify_<?= $method ?>]" 
                                        value="1" <?= ($settings['notify_' . $method] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notify_<?= $method ?>">
                                        <?= ucfirst($method) ?> Notifications
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email_recipients">Email Recipients</label>
                    <input type="text" class="form-control" id="email_recipients" 
                        name="settings[email_recipients]" 
                        value="<?= htmlspecialchars($settings['email_recipients'] ?? '') ?>">
                    <small class="form-text text-muted">Comma-separated email addresses</small>
                </div>

                <div class="form-group">
                    <label for="sms_recipients">SMS Recipients</label>
                    <input type="text" class="form-control" id="sms_recipients" 
                        name="settings[sms_recipients]" 
                        value="<?= htmlspecialchars($settings['sms_recipients'] ?? '') ?>">
                    <small class="form-text text-muted">Comma-separated phone numbers</small>
                </div>

                <div class="form-group">
                    <label for="slack_webhook">Slack Webhook URL</label>
                    <input type="url" class="form-control" id="slack_webhook" 
                        name="settings[slack_webhook]" 
                        value="<?= htmlspecialchars($settings['slack_webhook'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="webhook_url">Custom Webhook URL</label>
                    <input type="url" class="form-control" id="webhook_url" 
                        name="settings[webhook_url]" 
                        value="<?= htmlspecialchars($settings['webhook_url'] ?? '') ?>">
                </div>

                <hr>

                <div class="form-group">
                    <label for="low_stock_threshold">Low Stock Threshold (%)</label>
                    <input type="number" class="form-control" id="low_stock_threshold" 
                        name="settings[low_stock_threshold]" min="0" max="100" step="1"
                        value="<?= htmlspecialchars($settings['low_stock_threshold'] ?? '20') ?>">
                    <small class="form-text text-muted">Percentage of minimum stock level to trigger alerts</small>
                </div>

                <div class="form-group">
                    <label for="over_stock_threshold">Over Stock Threshold (%)</label>
                    <input type="number" class="form-control" id="over_stock_threshold" 
                        name="settings[over_stock_threshold]" min="0" max="500" step="1"
                        value="<?= htmlspecialchars($settings['over_stock_threshold'] ?? '150') ?>">
                    <small class="form-text text-muted">Percentage of stock limit to trigger overstock alerts</small>
                </div>

                <div class="form-group">
                    <label for="alert_frequency">Alert Frequency (hours)</label>
                    <input type="number" class="form-control" id="alert_frequency" 
                        name="settings[alert_frequency]" min="1" max="168" step="1"
                        value="<?= htmlspecialchars($settings['alert_frequency'] ?? '24') ?>">
                    <small class="form-text text-muted">Minimum time between alerts for the same product</small>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enable_alerts" 
                        name="settings[enable_alerts]" value="1" 
                        <?= ($settings['enable_alerts'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="enable_alerts">
                        Enable Alerts System
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </form>
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