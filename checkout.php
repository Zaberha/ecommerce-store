<?php
if (session_status() === PHP_SESSION_NONE) {
    ob_start(); // Start output buffering
    session_start();
}
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to checkout';
    header('Location: /login.php');
    exit;
}
$page_title = 'Checkout';
$current_page = 'checkout';
require_once __DIR__ . '/includes/headercheckout.php';

// Fetch delivery options
$stmt = $conn->prepare("SELECT flat_rate, is_rate_by_product, is_delivery_by_area FROM delivery_options LIMIT 1");
$stmt->execute();
$delivery_options = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery_options) {
    $_SESSION['error'] = 'Delivery options not found';
    header('Location: /');
    exit;
}
$flat_rate = $delivery_options['flat_rate'];
$is_rate_by_product = $delivery_options['is_rate_by_product'];
$is_delivery_by_area = $delivery_options['is_delivery_by_area'];

// Fetch user's delivery address
$stmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$delivery_address = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch store information from the admin table
$stmt = $conn->prepare("SELECT store_name, store_phone, store_email, store_address, store_city, store_country FROM admin LIMIT 1");
$stmt->execute();
$store_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if store country matches delivery address country
$international_delivery_fee = 0;
$use_international_fee = false;
if ($delivery_address && $store_info) {
    if ($delivery_address['country'] !== $store_info['store_country']) {
        // Get international delivery charge from countries table
        $stmt = $conn->prepare("SELECT delivery_charges FROM countries WHERE country_name = ?");
        $stmt->execute([$delivery_address['country']]);
        $country_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($country_data && $country_data['delivery_charges'] !== null) {
            $international_delivery_fee = $country_data['delivery_charges'];
            $use_international_fee = true;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_addresses'])) {
    $update_stmt = $conn->prepare("
        UPDATE delivery_addresses SET 
            street = ?, 
            building_name = ?, 
            building_number = ?, 
            city = ?, 
            country = ?, 
            floor_number = ?, 
            flat_number = ?, 
            alternative_phone = ?
        WHERE user_id = ?
    ");
    
    $update_stmt->execute([
        $_POST['street'],
        $_POST['building_name'],
        $_POST['building_number'],
        $_POST['city'],
        $_POST['country'],
        $_POST['floor_number'],
        $_POST['flat_number'],
        $_POST['alternative_phone'],
        $_SESSION['user_id']
    ]);
    
    // Refresh to avoid resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle form submission to update delivery address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $street = $_POST['street'] ?? '';
    $building_name = $_POST['building_name'] ?? '';
    $building_number = $_POST['building_number'] ?? '';
    $floor_number = $_POST['floor_number'] ?? '';
    $flat_number = $_POST['flat_number'] ?? '';
    $alternative_phone = $_POST['alternative_phone'] ?? '';

    // Update or insert delivery address
    if ($delivery_address) {
        $stmt = $conn->prepare("
            UPDATE delivery_addresses 
            SET country = ?, city = ?, street = ?, building_name = ?, building_number = ?, floor_number = ?, flat_number = ?, alternative_phone = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$country, $city, $street, $building_name, $building_number, $floor_number, $flat_number, $alternative_phone, $_SESSION['user_id']]);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO delivery_addresses (user_id, country, city, street, building_name, building_number, floor_number, flat_number, alternative_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $country, $city, $street, $building_name, $building_number, $floor_number, $flat_number, $alternative_phone]);
    }

    // Set session variable to indicate address confirmation
    $_SESSION['address_confirmed'] = false;
    $_SESSION['success'] = 'Delivery address updated successfully';
    header('Location: checkout.php');
    exit;
}

// Check if the address has been confirmed in the current session
if (!isset($_SESSION['address_confirmed'])) {
    // Display the delivery address form
    ?>
    <div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Delivery Address</li>
        </ol>
    </nav>
        <h2>Confirm Your Delivery Address</h2>
        <form method="POST" action="">
           
            <div class="form-group">
    <label for="country">Country*:</label>
    <select id="country" name="country" required>
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
            <div class="form-group">
                <label for="city">City*:</label>
                <input type="text" name="city" id="city" value="<?php echo $delivery_address['city'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="street">Street*:</label>
                <input type="text" name="street" id="street" value="<?php echo $delivery_address['street'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="building_name">Building Name*:</label>
                <input type="text" name="building_name" id="building_name" value="<?php echo $delivery_address['building_name'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="building_number">Building Number:</label>
                <input type="text" name="building_number" id="building_number" value="<?php echo $delivery_address['building_number'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="floor_number">Floor Number:</label>
                <input type="text" name="floor_number" id="floor_number" value="<?php echo $delivery_address['floor_number'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="flat_number">Flat Number:</label>
                <input type="text" name="flat_number" id="flat_number" value="<?php echo $delivery_address['flat_number'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="alternative_phone">Alternative Phone:</label>
                <input type="text" name="alternative_phone" id="alternative_phone" value="<?php echo $delivery_address['alternative_phone'] ?? ''; ?>">
            </div>
            <button type="submit" name="update_address" class="btn btn-primary">Confirm Address</button>
        </form>
                    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch user's profile information
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
  </ol>
</nav>
    <div class="row">
        <div class="col-sm-12 col-md-8">
            <h2>Checkout</h2>
            <h5 class="card-title py-4">Order Items</h5>
            <div class="order-items">
        <!-- Cart items will be dynamically inserted here -->
    </div>

<div class="row mt-4 py-3">
<div class="col-6"><h5>Delivery Information</h5></div>
<div class="col-6 text-end"><a class="noclass mx-5" data-bs-toggle="modal" data-bs-target="#editAddressModal"><i class="fas fa-edit"></i> Edit</a>
</div>
</div>
    <div class="row ">
<div class="col-sm-12 col-md-6">
    

        <div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true" style="z-index:1000000;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressModalLabel">Edit Delivery Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City*</label>
                            <input type="text" class="form-control" name="city" 
                                   value="<?= htmlspecialchars($delivery_address['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country*</label>
                            <select class="form-control" name="country" required>
                                <option value="<?= htmlspecialchars($delivery_address['country'] ?? '') ?>"><?= htmlspecialchars($delivery_address['country'] ?? '') ?></option>
                                <?php
                                $stmt = $conn->query("SELECT country_code, country_name FROM countries ORDER BY country_name");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = (isset($country) && $country === $row['country_name']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($row['country_name']) . '" ' . $selected . '>' . 
                                         htmlspecialchars($row['country_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Street*</label>
                        <input type="text" class="form-control" name="street" 
                               value="<?= htmlspecialchars($delivery_address['street'] ?? '') ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Building Name*</label>
                            <input type="text" class="form-control" name="building_name" 
                                   value="<?= htmlspecialchars($delivery_address['building_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Building Number</label>
                            <input type="text" class="form-control" name="building_number" 
                                   value="<?= htmlspecialchars($delivery_address['building_number'] ?? '') ?>">
                        </div>
                    </div>
                    

                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Floor Number</label>
                            <input type="text" class="form-control" name="floor_number" 
                                   value="<?= htmlspecialchars($delivery_address['floor_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Flat Number</label>
                            <input type="text" class="form-control" name="flat_number" 
                                   value="<?= htmlspecialchars($delivery_address['flat_number'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alternative Phone</label>
                        <input type="tel" class="form-control" name="alternative_phone" 
                               value="<?= htmlspecialchars($delivery_address['alternative_phone'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_addresses" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="delivery-info">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></p>
        <p><strong>Country:</strong> <?php echo htmlspecialchars($delivery_address['country']); ?></p>
        
    
        <p><strong>Address:</strong> 
            <?php echo htmlspecialchars($delivery_address['street'] . ', Bld. Name: ' . $delivery_address['building_name'] . ', Bld. No: ' . $delivery_address['building_number']); ?>
        </p>
    
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($profile['phone']); ?></p>
    </div>
</div>

<?php
// Display Sender Information Section
?>
<div class="col-sm-12 col-md-6">
    <div class="sender-info">
    <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
    <p><strong>City:</strong> <?php echo htmlspecialchars($delivery_address['city']); ?></p>
        <p><strong>Floor Number:</strong> <?php echo htmlspecialchars($delivery_address['floor_number']); ?></p>
        <p><strong>Flat Number:</strong> <?php echo htmlspecialchars($delivery_address['flat_number']); ?></p>
        <p><strong>Alternative Phone:</strong> <?php echo htmlspecialchars($delivery_address['alternative_phone']); ?></p>

    </div>
</div>

</div>

        </div>
        <div class="col-sm-12 col-md-4">

            <!-- Coupon Form -->
            <div class="card mb-3">
                <div class="card-body">
                    <form id="coupon-form">
                        <div class="mb-3">
                            <label for="coupon-code" class="form-label">Coupon Code</label>
                            <input type="text" class="form-control" id="coupon-code" placeholder="Enter coupon code">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Apply Coupon</button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Order Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total MRP
                            <span id="subtotal"><?php echo $default_currency; ?>0.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Discount
                            <span id="summary-discount"><?php echo $default_currency; ?>0.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Discounted Total
                            <span id="summary-discounted-total"><?php echo $default_currency; ?>0.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                        Tax <?php echo $admin['tax_rate']; ?>
                            <span id="summary-tax"><?php echo $default_currency; ?>0.00</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Shipping
                            <span id="summary-shipping">Calculated during checkout</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Coupon
                            <span id="coupon">Please Apply Coupon</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Total <?php echo $default_currency; ?></strong>
                            <strong><span id="summary-total">Calculated during checkout</span></strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="justify-content-center mt-3 py-4 card">
          
          <h5 class="text-center mb-4">Choose Payment Method</h5>
         <?php
// Start session and include DB connection
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

// Fetch active payment methods
$paymentMethods = [];
try {
    $stmt = $conn->prepare("SELECT * FROM payment_methods WHERE active = 1 ORDER BY id ASC");
    $stmt->execute();
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching payment methods: " . $e->getMessage());
    $_SESSION['error'] = "Error loading payment options. Please try again.";
}
?>

<form id="payment-form">
    <?php if (empty($paymentMethods)): ?>
        <div class="alert alert-warning">No payment methods available at this time.</div>
    <?php else: ?>
        <?php foreach ($paymentMethods as $index => $method): ?>
            <div class="payment-option">
                <label class="d-flex align-items-center">
                    <input type="radio" name="payment_method" value="<?= htmlspecialchars($method['value']) ?>" <?= $index === 0 ? 'checked' : '' ?>>
                    
                    <?php if (!empty($method['image'])): ?>
                        <img src="admin/<?= htmlspecialchars($method['image']) ?>" alt="<?= htmlspecialchars($method['name']) ?>" class="payment-icon">
                    <?php else: ?>
                        <!-- Fallback icons based on payment method -->
                        <?php if (strpos(strtolower($method['name']), 'cash') !== false): ?>
                            <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                        <?php elseif (strpos(strtolower($method['name']), 'card') !== false): ?>
                            <i class="fab fa-cc-visa fa-2x text-primary me-3"></i>
                        <?php else: ?>
                            <i class="fas fa-credit-card fa-2x text-info me-3"></i>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div>
                        <h6 class="mb-1"><?= htmlspecialchars($method['name']) ?></h6>
                        <p class="mb-0 text-muted"><?= htmlspecialchars($method['description'] ?? 'Secure payment') ?></p>
                    </div>
                </label>
                
                <?php if (strpos(strtolower($method['name']), 'card') !== false): ?>
                    <div class="mt-2">
                        <img src="assets/img/payments.png" alt="Payment Options" class="img-fluid">
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Terms & Conditions -->
    <div class="terms-section">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="terms-checkbox">
            <label class="form-check-label" for="terms-checkbox">
                I agree to the <a href="/terms-and-conditions" target="_blank">Terms & Conditions</a> and <a href="/privacy-policy" target="_blank">Privacy Policy</a>
            </label>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-lg" id="checkout-btn" disabled>
        <i class="fas fa-lock me-2"></i> Place Order Securely
    </button>
    
    <a href="index.php" class="btn btn-secondary btn-lg mt-2" style="width:100%; font-weight:bold; font-size:1rem;">
        <i class="fa fa-shopping-cart me-2"></i> Continue Shopping
    </a>
</form>          
              
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
document.addEventListener('DOMContentLoaded', function() {
    // 1. Get all DOM elements
    const checkoutButton = document.querySelector('#checkout-btn');
    const termsCheckbox = document.querySelector('#terms-checkbox');
    const orderItemsContainer = document.querySelector('.order-items');
    const subtotalElement = document.getElementById('subtotal');
    const summaryDiscountedTotalElement = document.getElementById('summary-discounted-total');
    const summaryDiscountElement = document.getElementById('summary-discount');
    const summaryTaxElement = document.getElementById('summary-tax');
    const summaryShippingElement = document.getElementById('summary-shipping');
    const summaryTotalElement = document.getElementById('summary-total');
    const couponForm = document.getElementById('coupon-form');
    const couponCodeInput = document.getElementById('coupon-code');
    const couponElement = document.getElementById('coupon');

    // 2. Initialize variables
    const currencySymbol = '<?php echo $default_currency; ?>';
    let cartItems = [];
    let discountByCode = 0;
    let deliveryFee = <?php echo $use_international_fee ? $international_delivery_fee : $flat_rate; ?>;
    let taxAmount = 0;

    // 3. Helper functions
    function extractNumber(value) {
        if (value === null || value === undefined) return 0;
        if (typeof value === 'number') return value;
        if (typeof value === 'string') {
            return parseFloat(value.replace(currencySymbol, '').replace(/[^0-9.-]/g, '')) || 0;
        }
        if (typeof value === 'object' && value.quantity !== undefined) {
            return parseInt(value.quantity) || 1;
        }
        return 0;
    }

    function formatCurrency(amount) {
        return `${currencySymbol}${amount.toFixed(2)}`;
    }

    function parseJSON(response) {
        return response.text().then(function(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', text);
                throw new Error('Invalid server response');
            }
        });
    }

    function showError(message, element) {
        console.error(message);
        element = element || orderItemsContainer;
        element.innerHTML = `<div class="alert alert-danger">${message}</div>`;
    }

    // 4. Cart functions
    function updateOrderSummary() {
        const subtotal = cartItems.reduce(function(sum, item) {
            return sum + (extractNumber(item.price) * extractNumber(item.quantity));
        }, 0);
        
        const discountedTotal = cartItems.reduce(function(sum, item) {
            const discount = extractNumber(item.discount_percentage || 0);
            return sum + (extractNumber(item.price) * (1 - discount/100) * extractNumber(item.quantity));
        }, 0);
        
        const productDiscount = subtotal - discountedTotal;
        const taxRate = <?php echo (float)str_replace('%', '', $admin['tax_rate']); ?>;
        taxAmount = discountedTotal * (taxRate / 100);
        const grandTotal = discountedTotal + deliveryFee + taxAmount - discountByCode;
        
        subtotalElement.textContent = formatCurrency(subtotal);
        summaryDiscountedTotalElement.textContent = formatCurrency(discountedTotal);
        summaryDiscountElement.textContent = formatCurrency(productDiscount);
        summaryTaxElement.textContent = formatCurrency(taxAmount);
        summaryShippingElement.textContent = formatCurrency(deliveryFee);
        summaryTotalElement.textContent = formatCurrency(grandTotal);
    }

    function renderCartItems() {
        let html = '';
        
        cartItems.forEach(function(item) {
            const price = extractNumber(item.price);
            const discount = extractNumber(item.discount_percentage || 0);
            const quantity = extractNumber(item.quantity);
            const discountedPrice = price * (1 - discount/100);
            const itemTotal = price * quantity;
            const itemDiscountedTotal = discountedPrice * quantity;
            
            html += `
                <div class="cart-items mb-3">
                    <div class="row g-0 py-4 bg-white item-check">
                        <div class="col-12 col-md-2">
                            <a href="product_details.php?id=${item.id}">
                                <img src="admin/images/${item.main_image}" alt="${item.name}" class="img-fluid">
                            </a>
                        </div>
                        <div class="col-12 col-md-10">
                            <a href="product_details.php?id=${item.id}"><h5>${item.name}</h5></a>
                            <p>${item.product_code}</p>
                            <div class="ruby">
                                <p><strong>Price:</strong> ${formatCurrency(price)}</p>
                                <p><strong>Discount:</strong> ${discount}%</p>
                                <p><strong>Quantity:</strong> ${quantity}</p>
                                <p><strong>Total:</strong> ${formatCurrency(itemTotal)}</p>
                                <p class="subtotal"><strong>Discounted Total:</strong> ${formatCurrency(itemDiscountedTotal)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        orderItemsContainer.innerHTML = html || '<p>Your cart is empty.</p>';
    }

    function loadCartItems() {
        const cartData = localStorage.getItem('cart');
        console.log('Cart data from localStorage:', cartData);
        
        if (!cartData || cartData === '{}') {
            renderCartItems();
            return;
        }
        
        try {
            const cartObject = JSON.parse(cartData);
            const productIds = Object.keys(cartObject);
            
            if (productIds.length === 0) {
                renderCartItems();
                return;
            }
            
            fetch('fetch_cart_items.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    product_ids: productIds,
                    cart: cartObject 
                })
            })
            .then(parseJSON)
            .then(function(data) {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Merge product data with cart quantities
                cartItems = data.map(function(product) {
                    const cartItem = cartObject[product.id.toString()];
                    return {
                        ...product,
                        quantity: cartItem ? cartItem.quantity : 1,
                        delivery_rate: product.delivery_rate || 0
                    };
                });
                
                console.log('Merged cart items:', cartItems);
                
                // Only calculate shipping if not using international fee
                if (!<?php echo $use_international_fee ? 'true' : 'false'; ?>) {
                    if (<?php echo $is_rate_by_product; ?>) {
                        deliveryFee = cartItems.reduce(function(total, item) {
                            return total + (extractNumber(item.quantity) * extractNumber(item.delivery_rate || 0));
                        }, 0);
                    } else if (<?php echo $is_delivery_by_area; ?>) {
                        fetch('get_area_freight_rate.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ 
                                city: "<?php echo $delivery_address['city']; ?>" 
                            }),
                        })
                        .then(parseJSON)
                        .then(function(data) {
                            if (data.area_freight_rate) {
                                deliveryFee = extractNumber(data.area_freight_rate);
                                updateOrderSummary();
                            }
                        })
                        .catch(function(error) {
                            showError(error.message);
                        });
                    }
                }
                
                renderCartItems();
                updateOrderSummary();
            })
            .catch(function(error) {
                showError(error.message);
            });
        } catch (error) {
            showError('Error processing cart data');
            console.error('Error parsing cart:', error);
        }
    }

    // 5. Event handlers
    couponForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const couponCode = couponCodeInput.value.trim();
        
        if (!couponCode) {
            alert('Please enter a coupon code.');
            return;
        }
        
        fetch('validate_coupon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ couponCode })
        })
        .then(parseJSON)
        .then(function(data) {
            if (data.success) {
                window.currentCoupon = {
                    code: data.coupon_code,
                    id: data.coupon_id
                };
                const discountPercentage = extractNumber(data.discount_percentage);
                const discountedTotal = extractNumber(summaryDiscountedTotalElement.textContent);
                discountByCode = discountedTotal * (discountPercentage / 100);
                
                couponElement.innerHTML = `
                    <strong>${couponCode}</strong> (-${formatCurrency(discountByCode)})
                    <button class="remove-item" id="remove-coupon">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
                
                updateOrderSummary();
                couponCodeInput.disabled = true;
                couponForm.querySelector('button').disabled = true;
                
                document.getElementById('remove-coupon').addEventListener('click', function() {
                    discountByCode = 0;
                    couponElement.textContent = 'Please Apply Coupon';
                    updateOrderSummary();
                    couponCodeInput.disabled = false;
                    couponForm.querySelector('button').disabled = false;
                });
                
                alert('Coupon applied successfully!');
            } else {
                alert(data.message);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('An error occurred while validating the coupon.');
        });
    });

    checkoutButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (cartItems.length === 0) {
            alert('Your cart is empty.');
            return;
        }
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const orderData = {
            cart: cartItems.map(function(item) {
                return {
                    id: item.id,
                    quantity: extractNumber(item.quantity),
                    price: extractNumber(item.price),
                    discount_percentage: extractNumber(item.discount_percentage || 0),
                    delivery_rate: extractNumber(item.delivery_rate || 0)
                };
            }),
            total_amount: extractNumber(subtotalElement.textContent),
            payment_method: paymentMethod,
            delivery_charges: extractNumber(summaryShippingElement.textContent),
            tax_amount: extractNumber(summaryTaxElement.textContent),
            product_discount: extractNumber(summaryDiscountElement.textContent),
            coupon_discount: discountByCode,
            grand_total: extractNumber(summaryTotalElement.textContent),
            coupon_code: window.currentCoupon?.code || null,
            coupon_id: window.currentCoupon?.id || null
        };
        
        fetch('admin/place_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        })
        .then(parseJSON)
        .then(function(data) {
            if (data.success) {
                localStorage.removeItem('cart');
                if (paymentMethod === 'visa card') {
                    window.location.href = `/demo_payment.php?order_id=${data.order_id}`;
                } else {
                    window.location.href = `/order_confirmationcod.php?order_id=${data.order_id}`;
                }
            } else {
                alert(data.message);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('Error placing order: ' + error.message);
        });
    });

    termsCheckbox.addEventListener('change', function() {
        checkoutButton.disabled = !this.checked;
    });

    // 6. Initialize
    checkoutButton.disabled = true;
    loadCartItems();
});
</script>
<script>
    // Pass the PHP variable to JavaScript
    const defaultCurrency = "<?php echo $default_currency; ?>";
</script>
<script src="assets/js/script.js"></script>
</body>
</html>