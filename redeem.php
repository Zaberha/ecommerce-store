<?php
session_start();
require_once __DIR__ . '/db.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get admin settings and user data
$admin = []; // Assuming you have this from your admin settings table
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin['loyalty_program_enabled'] == 0) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_points = 0;
$user_coupons = [];

// Get user's current points
$stmt = $conn->prepare("SELECT points FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
$user_points = $user_data['points'] ?? 0;

// Get user's existing coupons
$stmt = $conn->prepare("SELECT * FROM discount_codes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$user_coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle points redemption form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token. Please try again.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['redeem_points'])) {
        $selected_points = (int)$_POST['points_to_redeem'];
        $threshold = (int)$admin['COLLOYALTY_coupon_threshold'];
        
        // Check if user already has an active coupon
        $has_active_coupon = false;
        foreach ($user_coupons as $coupon) {
            if ($coupon['active_flag'] == 1 && strtotime($coupon['expiry_date']) > time()) {
                $has_active_coupon = true;
                break;
            }
        }
        
        if ($has_active_coupon) {
            $_SESSION['error'] = "You already have an active coupon. Please use your current coupon before getting a new one!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } elseif ($selected_points > $user_points) {
            $_SESSION['error'] = "You don't have enough points to redeem this amount!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } elseif ($selected_points < $threshold) {
            $_SESSION['error'] = "You have not reached the minimum redeem threshold of {$threshold} points!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } else {
            // Calculate discount percentage
            $multiplier = $selected_points / $threshold;
            $discount_percentage = $admin['loyalty_coupon_percentage'] * $multiplier;
            
            // Generate unique coupon code
            $coupon_code = 'LOYALTY' . strtoupper(substr(md5(uniqid()), 0, 8));
            $coupon_name = "Loyalty Reward (" . round($discount_percentage, 2) . "% Off)";
            $expiry_days = (int)$admin['loyalty_coupon_expiry_days'] ?? 30;
            $expiry_date = date('Y-m-d H:i:s', strtotime("+{$expiry_days} days"));
            
            try {
                $conn->beginTransaction();
                
                // Create new coupon
                $stmt = $conn->prepare("
                    INSERT INTO discount_codes 
                    (code_name, code, discount_percentage, created_at, active_flag, expiry_date, 
                     usage_limit, type, assigned_to, user_id) 
                    VALUES (?, ?, ?, NOW(), 1, ?, 1, 'loyalty', 'specific_user', ?)
                ");
                $stmt->execute([
                    $coupon_name,
                    $coupon_code,
                    $discount_percentage,
                    $expiry_date,
                    $user_id
                ]);
                
                // Update user's points
                $stmt = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
                $stmt->execute([$selected_points, $user_id]);
                
                $conn->commit();
                
                // Regenerate CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                // Store success message
                $_SESSION['success'] = "Success! Your coupon code is: <strong>$coupon_code</strong> ($coupon_name)";
                
                // Redirect to prevent resubmission
                header("Location: ".$_SERVER['PHP_SELF']);
                exit;
                
            } catch (PDOException $e) {
                $conn->rollBack();
                $_SESSION['error'] = "Error processing your request: " . $e->getMessage();
                header("Location: ".$_SERVER['PHP_SELF']);
                exit;
            }
        }
    }
}

// Retrieve messages from session
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$page_title = 'Redeem Points';
$current_page = 'Loyalty Program';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .coupon-card {
        border-left: 4px solid var(--forth-color);
        background-color: #f8f9fa;
    }
    .coupon-code {
        font-family: monospace;
        font-size: 1.2rem;
        letter-spacing: 1px;
    }
    .expired {
        opacity: 0.6;
    }
</style>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header colored-second text-white">
                        <h3 class="mb-0">Redeem Your Loyalty Points</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-4 p-3 bg-light rounded">
                            <h4>Your Current Points: <span class="badge bg-success"><?= number_format($user_points) ?></span></h4>
                            <p class="mb-1">Minimum redeem threshold: <?= number_format($admin['COLLOYALTY_coupon_threshold']) ?> points</p>
                            <p class="mb-0">Each <?= number_format($admin['COLLOYALTY_coupon_threshold']) ?> points = <?= $admin['loyalty_coupon_percentage'] ?>% discount</p>
                        </div>
                        
                        <form method="post" class="mb-5">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="mb-3">
                                <label for="points_to_redeem" class="form-label fw-bold">Select Points to Redeem:</label>
                                <select class="form-select form-select-lg" id="points_to_redeem" name="points_to_redeem" required>
                                    <option value="">-- Select Amount --</option>
                                    <option value="<?= $admin['COLLOYALTY_coupon_threshold'] ?>">
                                        1x Threshold (<?= number_format($admin['COLLOYALTY_coupon_threshold']) ?> pts → <?= $admin['loyalty_coupon_percentage'] ?>% discount)
                                    </option>
                                    <option value="<?= 2 * $admin['COLLOYALTY_coupon_threshold'] ?>">
                                        2x Threshold (<?= number_format(2 * $admin['COLLOYALTY_coupon_threshold']) ?> pts → <?= 2 * $admin['loyalty_coupon_percentage'] ?>% discount)
                                    </option>
                                    <option value="<?= 3 * $admin['COLLOYALTY_coupon_threshold'] ?>">
                                        3x Threshold (<?= number_format(3 * $admin['COLLOYALTY_coupon_threshold']) ?> pts → <?= 3 * $admin['loyalty_coupon_percentage'] ?>% discount)
                                    </option>
                                </select>
                            </div>
                            <button type="submit" name="redeem_points" class="btn btn-primary btn-lg w-100">
                                Redeem Points
                            </button>
                        </form>
                        
                        <h4 class="mb-3 border-bottom pb-2">Your Coupons</h4>
                        <?php if (empty($user_coupons)): ?>
                            <div class="text-muted">You don't have any loyalty coupons yet.</div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($user_coupons as $coupon): 
                                    $is_expired = strtotime($coupon['expiry_date']) < time();
                                    $is_active = $coupon['active_flag'] == 1 && !$is_expired;
                                ?>
                                    <div class="col-md-6">
                                        <div class="card coupon-card <?= $is_expired ? 'expired' : '' ?>">
                                            <div class="card-body">
                                                <h5 class="card-title">Code: <?= htmlspecialchars($coupon['code']) ?></h5>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="coupon-code">Type:<?= htmlspecialchars($coupon['type']) ?></span>
                                                    <span class="badge bg-<?= $is_active ? 'success' : ($is_expired ? 'secondary' : 'primary') ?>">
                                                        Status: <?= $is_active ? 'Active' : ($is_expired ? 'Expired' : 'Used') ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">
                                                        <strong><?= $coupon['discount_percentage'] ?>%</strong> discount
                                                    </small>
                                                    <small class="text-muted">
                                                        Expires: <?= date('M d, Y', strtotime($coupon['expiry_date'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>