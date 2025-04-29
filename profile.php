<?php
// Enforce HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

session_start();
include 'db.php';
// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}



// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if this is the first visit to profile.php in the current session
if (!isset($_SESSION['profile_visited'])) {
    $_SESSION['profile_visited'] = true;
    $show_welcome_message = true; // Flag to show JavaScript message
} else {
    $show_welcome_message = false;
}

$user_id = $_SESSION['user_id'];

// Initialize empty arrays for user info and delivery address
$user_info = [
    'username' => '',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'birth_date' => ''
];

$delivery_address = [
    'country' => '',
    'city' => '',
    'street' => '',
    'building_name' => '',
    'building_number' => '',
    'floor_number' => '',
    'flat_number' => '',
    'alternative_phone' => ''
];

// First fetch username and email from users table
$stmt = $conn->prepare("SELECT username, email, phone, birth_date FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    $user_info['username'] = $result['username'];
    $user_info['email'] = $result['email'];
    $user_info['phone'] = $result['phone'];
    $user_info['birth_date'] = $result['birth_date'];
}

// Then fetch additional info from profiles
$stmt = $conn->prepare("SELECT first_name, last_name FROM profiles WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    $user_info['first_name'] = $result['first_name'];
    $user_info['last_name'] = $result['last_name'];
}

// Fetch delivery address
$stmt = $conn->prepare("SELECT country, city, street, building_name, building_number, floor_number, flat_number, alternative_phone FROM delivery_addresses WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    $delivery_address = $result;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    if (isset($_POST['update_user_info'])) {
        // Update user information
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone = $_POST['phone'];

        // Check if profile exists
        $stmt = $conn->prepare("SELECT user_id FROM profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            // Update existing profile
            $stmt = $conn->prepare("UPDATE profiles SET first_name = :first_name, last_name = :last_name WHERE user_id = :user_id");
        } else {
            // Insert new profile
            $stmt = $conn->prepare("INSERT INTO profiles (user_id, first_name, last_name) VALUES (:user_id, :first_name, :last_name)");
        }
        
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Update phone in users table
        $stmt = $conn->prepare("UPDATE users SET phone = :phone WHERE id = :user_id");
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Store active tab in session
        $_SESSION['active_tab'] = 'user-info';
        
        // Redirect to prevent form resubmission
        header("Location: profile.php");
        exit();
    } elseif (isset($_POST['update_delivery_address'])) {
        // Update delivery address
        $country = $_POST['country'];
        $city = $_POST['city'];
        $street = $_POST['street'];
        $building_name = $_POST['building_name'];
        $building_number = $_POST['building_number'];
        $floor_number = $_POST['floor_number'];
        $flat_number = $_POST['flat_number'];
        $alternative_phone = $_POST['alternative_phone'];

        // Check if address exists
        $stmt = $conn->prepare("SELECT id FROM delivery_addresses WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            // Update existing address
            $stmt = $conn->prepare("UPDATE delivery_addresses SET country = :country, city = :city, street = :street, building_name = :building_name, building_number = :building_number, floor_number = :floor_number, flat_number = :flat_number, alternative_phone = :alternative_phone WHERE user_id = :user_id");
        } else {
            // Insert new address
            $stmt = $conn->prepare("INSERT INTO delivery_addresses (user_id, country, city, street, building_name, building_number, floor_number, flat_number, alternative_phone) VALUES (:user_id, :country, :city, :street, :building_name, :building_number, :floor_number, :flat_number, :alternative_phone)");
        }

        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':street', $street);
        $stmt->bindParam(':building_name', $building_name);
        $stmt->bindParam(':building_number', $building_number);
        $stmt->bindParam(':floor_number', $floor_number);
        $stmt->bindParam(':flat_number', $flat_number);
        $stmt->bindParam(':alternative_phone', $alternative_phone);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Store active tab in session
        $_SESSION['active_tab'] = 'delivery-address';
        
        // Redirect to prevent form resubmission
        header("Location: profile.php");
        exit();
    }
}

// Fetch orders
$stmt = $conn->prepare("
    SELECT o.id AS order_id, o.created_at, o.grand_total, o.order_status, oi.product_id, oi.quantity, oi.price, p.name AS product_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = :user_id
    ORDER BY o.created_at DESC
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group orders by order ID
$grouped_orders = [];
foreach ($orders as $order) {
    $order_id = $order['order_id'];
    if (!isset($grouped_orders[$order_id])) {
        $grouped_orders[$order_id] = [
            'created_at' => $order['created_at'],
            'total' => $order['grand_total'],
            'status' => $order['order_status'],
            'products' => []
        ];
    }
    $grouped_orders[$order_id]['products'][] = [
        'product_name' => $order['product_name'],
        'quantity' => $order['quantity'],
        'price' => $order['price']
    ];
}

// Determine active tab
$active_tab = $_SESSION['active_tab'] ?? 'user-info';
unset($_SESSION['active_tab']); // Clear after use

// Set page title
$page_title = 'My Profile';
$current_page = 'profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
        </ol>
    </nav>

    <h2 class="fw-bold">My Profile</h2>

    <!-- Bootstrap 5 Tabs -->
    <div class="row">
        <div class="col-md-3">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <button class="nav-link <?= $active_tab === 'user-info' ? 'active' : '' ?>" id="v-pills-user-info-tab" data-bs-toggle="pill" data-bs-target="#v-pills-user-info" type="button" role="tab" aria-controls="v-pills-user-info" aria-selected="<?= $active_tab === 'user-info' ? 'true' : 'false' ?>">User Information</button>
                <button class="nav-link <?= $active_tab === 'delivery-address' ? 'active' : '' ?>" id="v-pills-delivery-address-tab" data-bs-toggle="pill" data-bs-target="#v-pills-delivery-address" type="button" role="tab" aria-controls="v-pills-delivery-address" aria-selected="<?= $active_tab === 'delivery-address' ? 'true' : 'false' ?>">Delivery Address</button>
                <button class="nav-link <?= $active_tab === 'orders' ? 'active' : '' ?>" id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#v-pills-orders" type="button" role="tab" aria-controls="v-pills-orders" aria-selected="<?= $active_tab === 'orders' ? 'true' : 'false' ?>">Orders</button>
            </div>
        </div>
        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- User Information Tab -->
                <div class="tab-pane fade <?= $active_tab === 'user-info' ? 'show active' : '' ?>" id="v-pills-user-info" role="tabpanel" aria-labelledby="v-pills-user-info-tab">
                    <h4>User Information</h4>
                    <form method="POST" action="" class="form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label"><strong>Username:</strong></label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user_info['username']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label"><strong>First Name:</strong></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_info['first_name']); ?>" placeholder="Enter your first name">
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label"><strong>Last Name:</strong></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_info['last_name']); ?>" placeholder="Enter your last name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><strong>Email Address:</strong></label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label"><strong>Phone Number:</strong></label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone']); ?>" placeholder="Enter your phone number" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="birth_date" class="form-label"><strong>Birth Date:</strong></label>
                            <input type="text" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user_info['birth_date']); ?>" disabled>
                        </div>
                        <button type="submit" name="update_user_info" class="btn btn-primary">Update User Information</button>
                    </form>
                </div>

                <!-- Delivery Address Tab -->
                <div class="tab-pane fade <?= $active_tab === 'delivery-address' ? 'show active' : '' ?>" id="v-pills-delivery-address" role="tabpanel" aria-labelledby="v-pills-delivery-address-tab">
                    <h4>Delivery Address</h4>
                    <form method="POST" action="" class="form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label for="country" class="form-label"><strong>Country*:</strong></label>           
                            <select id="country" name="country" class="form-control" required>
                                <option value="<?= htmlspecialchars($delivery_address['country'] ?? '') ?>"><?= htmlspecialchars($delivery_address['country'] ?? '') ?></option>
                                <?php
                                // Fetch countries from database
                                $stmt = $conn->query("SELECT country_code, country_name FROM countries ORDER BY country_name");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = (isset($country) && $country === $row['country_name']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($row['country_name']) . '" ' . $selected . '>' . 
                                         htmlspecialchars($row['country_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label"><strong>City*:</strong></label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($delivery_address['city']); ?>" placeholder="Enter your city" required>
                        </div>
                        <div class="mb-3">
                            <label for="street" class="form-label"><strong>Street*:</strong></label>
                            <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars($delivery_address['street']); ?>" placeholder="Enter your street" required>
                        </div>
                        <div class="mb-3">
                            <label for="building_name" class="form-label"><strong>Building Name*:</strong></label>
                            <input type="text" class="form-control" id="building_name" name="building_name" value="<?php echo htmlspecialchars($delivery_address['building_name']); ?>" placeholder="Enter building name" required>
                        </div>
                        <div class="mb-3">
                            <label for="building_number" class="form-label"><strong>Building Number:</strong></label>
                            <input type="text" class="form-control" id="building_number" name="building_number" value="<?php echo htmlspecialchars($delivery_address['building_number']); ?>" placeholder="Enter building number">
                        </div>
                        <div class="mb-3">
                            <label for="floor_number" class="form-label"><strong>Floor Number:</strong></label>
                            <input type="text" class="form-control" id="floor_number" name="floor_number" value="<?php echo htmlspecialchars($delivery_address['floor_number']); ?>" placeholder="Enter floor number">
                        </div>
                        <div class="mb-3">
                            <label for="flat_number" class="form-label"><strong>Flat Number:</strong></label>
                            <input type="text" class="form-control" id="flat_number" name="flat_number" value="<?php echo htmlspecialchars($delivery_address['flat_number']); ?>" placeholder="Enter flat number">
                        </div>
                        <div class="mb-3">
                            <label for="alternative_phone" class="form-label"><strong>Alternative Phone:</strong></label>
                            <input type="tel" class="form-control" id="alternative_phone" name="alternative_phone" value="<?php echo htmlspecialchars($delivery_address['alternative_phone']); ?>" placeholder="Enter alternative phone">
                        </div>
                        <button type="submit" name="update_delivery_address" class="btn btn-primary">Update Delivery Address</button>
                    </form>
                </div>

                <!-- Previous Orders Tab -->
                <div class="tab-pane fade <?= $active_tab === 'orders' ? 'show active' : '' ?>" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                    <h4>Orders</h4>
                    <div class="mb-3">
                        <input type="text" id="search-order" class="form-control" placeholder="Search by Order Number...">
                    </div>
                    <?php if (!empty($grouped_orders)): ?>
                        <div id="orders-list">
                            <?php foreach ($grouped_orders as $order_id => $order): ?>
                                <div class="card mb-3 order-item" data-order-id="<?php echo htmlspecialchars($order_id); ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <h5 class="card-title">
                                                    <a href="order_details.php?order_id=<?php echo htmlspecialchars($order_id); ?>">
                                                        Order #<?php echo htmlspecialchars($order_id); ?>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="card-text">
                                                    <strong>Date:</strong><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['created_at']))); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="card-text">
                                                    <strong>Total:</strong> <?php echo $default_currency; ?><?php echo htmlspecialchars(number_format($order['total'], 2)); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="card-text">
                                                    <strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <nav aria-label="Orders pagination">
                            <ul class="pagination justify-content-center mt-4">
                                <?php
                                $totalOrders = count($grouped_orders);
                                $ordersPerPage = 20;
                                $totalPages = ceil($totalOrders / $ordersPerPage);
                                for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <p>No previous orders found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-order');
        const orderItems = document.querySelectorAll('.order-item');
        const paginationLinks = document.querySelectorAll('.pagination .page-link');

        // Search by Order Number
        searchInput.addEventListener('input', function () {
            const searchTerm = searchInput.value.trim().toLowerCase();

            orderItems.forEach(order => {
                const orderId = order.getAttribute('data-order-id').toLowerCase();
                if (orderId.includes(searchTerm)) {
                    order.style.display = 'block';
                } else {
                    order.style.display = 'none';
                }
            });
        });

        // Pagination
        paginationLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const page = parseInt(link.getAttribute('data-page'));
                const ordersPerPage = 20;
                const startIndex = (page - 1) * ordersPerPage;
                const endIndex = startIndex + ordersPerPage;

                orderItems.forEach((order, index) => {
                    if (index >= startIndex && index < endIndex) {
                        order.style.display = 'block';
                    } else {
                        order.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
<script>
    // Pass the PHP variable to JavaScript
    const defaultCurrency = "<?php echo $default_currency; ?>";
</script>
<script src="assets/js/script.js"></script>
</body>
</html>