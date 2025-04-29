<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// Fetch default_currency from the admin table
$stmt = $conn->query("SELECT default_currency FROM admin LIMIT 1");
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
if ($admin_settings) {
    $default_currency = $admin_settings['default_currency'];

    // Assign a rate based on the default_currency
    switch ($default_currency) {
        case 'AED':
            $rate = 3.67;
            break;
        case '$':
            $rate = 1.00;
            break;
        case 'SP':
            $rate = 0.27;
            break;
        default:
            $rate = 1.00;
            break;
    }
}

// Normal page load (not AJAX)
$page_title = 'Shopping Cart';
$current_page = 'cart';
$admin = $conn->query("SELECT * FROM admin LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$tax = (float) str_replace('%', '', $admin['tax_rate']);

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($is_ajax) {
    // Get the raw POST data
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);

    // Extract cart data
    $cart_items = $data['cart'] ?? [];

    // Fetch product details for cart items
    $cart_products = [];
    if (!empty($cart_items)) {
        $product_ids = array_keys($cart_items);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $conn->prepare("SELECT id, name, price, is_offer, discount_percentage, main_image, max_order FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        $fetched_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create a map for easier access
        $product_map = [];
        foreach ($fetched_products as $product) {
            $product_map[$product['id']] = $product;
        }

        // Build cart products with proper quantity and stock limits
        foreach ($cart_items as $product_id => $item) {
            if (isset($product_map[$product_id])) {
                $product = $product_map[$product_id];
                // Get quantity from the item object if it exists, otherwise fall back to the old way
                $quantity = is_array($item) ? $item['quantity'] : $item;
                $product['quantity'] = max(1, min((int)$quantity, (int)$product['max_order']));
                $cart_products[] = $product;
            }
        }
    }

    // Generate HTML for the cart table
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $page_title; ?> - E-commerce Store</title>
                                                        <!-- Favicon -->
    <link rel="icon" href="assets/img/icon/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/icon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/icon/apple-touch-icon.png">
    <link rel="manifest" href="assets/img/icon/site.webmanifest">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/includes/dynamic-styles.php">
        <link rel="stylesheet" href="/assets/css/base.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                        <!-- Font Awesome CSS -->
                        <link rel="stylesheet" href="/assets/css/font-awesome.min.css">
    <style>
        body {
            direction: <?= ($_SESSION['lang'] == 'ar') ? 'rtl' : 'ltr'; ?>;
            text-align: <?= ($_SESSION['lang'] == 'ar') ? 'right' : 'left'; ?>;
        }
    </style>
    </head>
    <body>

    <?php if (empty($cart_products)): ?>
    <div class="text-muted">Your cart is empty.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($cart_products as $product): 
            $discounted_price = $product['price'] * (1 - ($product['discount_percentage'] / 100));
            $subtotal = $discounted_price * $product['quantity'];
        ?>
        <div class="col-12" data-product-id="<?= htmlspecialchars($product['id']) ?>">
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md-3 col-4">
                        <a href="product_details.php?id=<?= $product['id'] ?>">
                            <img src="admin/images/<?= htmlspecialchars($product['main_image']) ?>" 
                                 class="img-fluid rounded-start p-2" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                    </div>
                    <div class="col-md-9 col-8">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">
                                    <a href="product_details.php?id=<?= $product['id'] ?>">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </h5>

                            </div>
                            
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="mb-2">


                                <?php if($product['is_offer'] == 1) { ?>
    <span class="text-muted text-decoration-line-through me-2">
        <?= $default_currency ?><?= number_format($product['price'], 2) ?>
    </span>
<?php } else { ?>
    <span class=" text-decoration-line-through me-2" style="color:transparent;">
        <?= $default_currency ?><?= number_format($product['price'], 2) ?>
    </span>
<?php } ?>
                                    <span class="fw-bold text-third">
                                        <?= $default_currency ?><?= number_format($discounted_price, 2) ?>
                                    </span>
                                </div>
                                
                                <div class="quantity-controls d-flex align-items-center">
                                    <button class="btn btn-sm decrease-quantity">-</button>
                                    <input type="number" 
                                           class="form-control quantity text-center mx-1" 
                                           value="<?= (int)$product['quantity'] ?>" 
                                           min="1" 
                                           max="<?= (int)$product['max_order'] ?>"
                                           style="width: 50px;">
                                    <button class="btn btn-sm increase-quantity">+</button>

                                </div>                                
                                <div class="w-100 d-md-none"></div> <!-- Break for mobile -->
                                
                                <div class="mt-2 mt-md-0 text-end">
                                    <span class="fw-bold">
                                        Subtotal: <?= $default_currency ?><?= number_format($subtotal, 2) ?>
                                    </span>
                                    <button class="btn btn-sm remove-item">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
    <?php
    $html = ob_get_clean();
    echo $html;
    exit;
}
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-d fixed-top" style="z-index:100000">
        <div class="container-fluid">
        <a class="navbar-brand" href="/"><img src="admin/<?php echo htmlspecialchars($admin['business_logo']); ?>"  width="136.5px" height="52.5px" alt="<?php echo htmlspecialchars($admin['store_name']); ?>"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a aria-current="page" href="/" <?php echo $current_page === 'home' ? 'class="nav-link active"' : ''; ?>>Home</a>
                    </li>
                    <li class="nav-item">
                                <a aria-current="page" href="shop.php" <?php echo $current_page === 'shop' ? 'class="nav-link active"' : ''; ?>>ALL</a>
                            </li>
                    <?php
                        $stmt = $conn->query("SELECT * FROM categories WHERE active = 1");
                        while ($category = $stmt->fetch()): 
                    ?>
                    <li class="nav-item">
                        <a href="/category.php?id=<?php echo $category['id']; ?>" 
                            <?php echo $current_page === 'category-'.$category['id'] ? 'class="nav-link active"' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    </li>
                    <?php endwhile; ?>
                   
                </ul>
               

                <form class="d-flex" action="results.php" method="GET">
    <input name="query" id="search-input" placeholder="Search" class="form-control" type="search" aria-label="Search" required>
    <button type="button" id="voice-search-btn" class="btn btn-outline-secondary">
        <i class="fas fa-microphone"></i>
    </button>
    <button type="submit" id="btn_search" class="btn btn-primary">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>
</form>

<!-- Voice search feedback -->
<div id="voice-feedback" style="display:none;" class="mt-2">
    <p id="voice-status" class="mb-1"><i class="fas fa-circle-notch fa-spin"></i> Listening...</p>
    <div id="voice-transcript" class="small text-muted"></div>
</div>

<!-- Unsupported browser warning (hidden by default) -->
<div id="voice-unsupported" class="alert alert-warning mt-2" style="display:none;">
    Voice search is not supported in your browser. Try Chrome, Edge, or Safari.
</div>
            </div>
        </div>
    </nav>
    
    <div class="header-social d-flex justify-content-around row fixed-top">
        <div class="col-sm-12 col-md-2">
            <?php if (!empty($admin['store_phone'])): ?>
            <a href="<?php echo $admin['store_phone']; ?>" target="_blank"><i class="fa fa-phone"></i><?php echo $admin['store_phone']; ?></a>
            <?php endif; ?>
        </div>
        <div class="col-sm-12 col-md-5">
            <?php if (!empty($admin['facebook_link'])): ?>
                <a href="<?php echo $admin['facebook_link']; ?>" target="_blank"><i class="fa fa-facebook"></i></a>
            <?php endif; ?>
            <?php if (!empty($admin['instagram_link'])): ?>
                <a href="<?php echo $admin['instagram_link']; ?>" target="_blank"><i class="fa fa-instagram"></i></a>
            <?php endif; ?>
            <?php if (!empty($admin['x_link'])): ?>
                <a href="<?php echo $admin['x_link']; ?>" target="_blank"><i class="fa fa-twitter"></i></a>
            <?php endif; ?>
            <?php if (!empty($admin['tiktok_link'])): ?>
                <a href="<?php echo $admin['tiktok_link']; ?>" target="_blank"><i class="fa fa-tiktok"></i></a>
            <?php endif; ?>
            <?php if (!empty($admin['snapchat_link'])): ?>
                <a href="<?php echo $admin['snapchat_link']; ?>" target="_blank"><i class="fa fa-snapchat"></i></a>
            <?php endif; ?>
            <?php if (!empty($admin['linkedin_link'])): ?>
                <a href="<?php echo $admin['linkedin_link']; ?>" target="_blank"><i class="fa fa-linkedin"></i></a>
            <?php endif; ?>
            <?php if (!empty($admin['google_business_link'])): ?>
                <a href="<?php echo $admin['google_business_link']; ?>" target="_blank"><i class="fa fa-google"></i></a>
            <?php endif; ?>
            <?php if (!empty($admin['youtube_channel_link'])): ?>
                <a href="<?php echo $admin['youtube_channel_link']; ?>" target="_blank"><i class="fa fa-youtube"></i></a>
            <?php endif; ?>
        </div> 
        <div class="col-sm-12 col-md-5">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="icosign" href="cart.php"><i class="fa fa-shopping-cart"></i>(<span id="cart-count">0</span>)</a>
                <a class="icosign" href="wishlist.php"><i class="fas fa-heart wishlist-icon"></i> (<span id="wishlist-count">0</span>)</a>
                <?php if ($admin['loyalty_program_enabled']==1): ?>
                    <a class="icosign" href="/redeem.php">Points <i class="fa-solid fa-gem"></i></a>
                    <?php endif; ?>  
            <a href="/profile.php" <?php echo $current_page === 'profile' ? 'class="active"' : ''; ?>><i class="fa fa-user" aria-hidden="true"></i></a>
            <a href="/logout.php"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a class="icosign" href="cart.php"><i class="fa fa-shopping-cart"></i>(<span id="cart-count">0</span>)</a>
                <a class="icosign" href="wishlist.php"><i class="fas fa-heart wishlist-icon"></i> (<span id="wishlist-count">0</span>)</a>
            <a href="/login.php" <?php echo $current_page === 'login' ? 'class="active"' : ''; ?>>Login</a>
            <a href="/register.php" <?php echo $current_page === 'register' ? 'class="active"' : ''; ?>>Register</a> 
            <?php endif; ?>     
        </div>
    </div>  
</header>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const voiceSearchBtn = document.getElementById('voice-search-btn');
    if (!voiceSearchBtn) return;

    // Browser support check
    if (!('webkitSpeechRecognition' in window)) {
        document.getElementById('voice-unsupported').style.display = 'block';
        voiceSearchBtn.style.display = 'none';
        return;
    }

    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = 'en-US';
    recognition.interimResults = false;

    voiceSearchBtn.addEventListener('click', function() {
        if (voiceSearchBtn.classList.contains('recording')) {
            recognition.stop();
            return;
        }

        // UI Feedback
        voiceSearchBtn.classList.add('recording', 'btn-danger');
        voiceSearchBtn.innerHTML = '<i class="fas fa-stop"></i>';
        document.getElementById('voice-feedback').style.display = 'block';
        document.getElementById('voice-status').innerHTML = 
            '<i class="fas fa-circle-notch fa-spin"></i> Listening...';

        // Start recognition with timeout
        recognition.start();
        setTimeout(() => {
            if (voiceSearchBtn.classList.contains('recording')) {
                recognition.stop();
                document.getElementById('voice-status').textContent = 
                    "No speech detected. Try again.";
            }
        }, 5000);
    });

    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript.trim();
        document.getElementById('search-input').value = transcript;
        
        // Submit only if query is 2+ characters
        if (transcript.length >= 2) {
            document.querySelector('form.d-flex').submit();
        }
    };

    recognition.onerror = function(event) {
        console.error('Voice error:', event.error);
        document.getElementById('voice-status').innerHTML = 
            `<i class="fas fa-exclamation-triangle"></i> ${getErrorMessage(event.error)}`;
    };

    recognition.onend = function() {
        voiceSearchBtn.classList.remove('recording', 'btn-danger');
        voiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        setTimeout(() => {
            document.getElementById('voice-feedback').style.display = 'none';
        }, 2000);
    };

    function getErrorMessage(error) {
        const errors = {
            'no-speech': 'No speech detected',
            'audio-capture': 'Microphone not available',
            'network': 'Network connection required',
            'not-allowed': 'Microphone access denied'
        };
        return errors[error] || 'Error occurred. Please try typing.';
    }
});
    </script>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
        </ol>
    </nav>
    <h2>Cart</h2>  
    
    <div class="row">
        <!-- First Column: Cart Content -->
        <div class="col-md-8">
            <main id="cart-content">
                <!-- Cart content will be loaded here via AJAX -->
            </main>
        </div>

        <!-- Second Column: Summary -->
        <div class="col-md-4">


            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Order Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total MRP
                            <span id="summary-subtotal"><?php echo $default_currency; ?>0.00</span>
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
                            <strong>Total</strong>
                            <strong><span id="summary-total">Calculated during checkout</span></strong>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="index.php" class="btn btn-secondary" style="width:100%;">Continue Shopping</a>
                    <br/><br/>
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'checkout.php' : 'login.php'; ?>" class="btn btn-primary" style="width:100%;">Checkout</a>
                </div>
            </div>
        </div>
    </div>
</div>
   
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
// Function to send cart data to PHP
function sendCartData() {
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    console.log('Sending cart data:', cart); // Debug log

    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ cart })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.text();
    })
    .then(data => {
        document.getElementById('cart-content').innerHTML = data;
        updateTotal();
        attachEventListeners();
        updateCartCount();
    })
    .catch(error => {
        console.error('Error fetching cart:', error);
        alert('Error updating cart. Please try again.');
    });
}

// Function to update cart count in header
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    const count = Object.keys(cart).length;
    document.getElementById('cart-count').textContent = count;

    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};
    const wishlistCount = Object.keys(wishlist).length;
    document.getElementById('wishlist-count').textContent = wishlistCount;
    console.log('Cart count updated:', count); // Debug log
}

// Function to update the total price
function updateTotal() {
    const taxRate = <?php echo (float) str_replace('%', '', $admin['tax_rate']); ?> / 100;
    let subtotal = 0;
    let discountedTotal = 0;
    let hasItems = false;

    document.querySelectorAll('[data-product-id]').forEach(card => {
        try {
            const priceText = card.querySelector('.text-decoration-line-through').textContent;
            const discountedPriceText = card.querySelector('.text-third').textContent;
            const quantityInput = card.querySelector('.quantity');
            const subtotalElement = card.querySelector('.fw-bold span');
            const plusBtn = card.querySelector('.increase-quantity');

            // Parse numeric values
            const price = parseFloat(priceText.replace(/[^\d.]/g, ''));
            const discountedPrice = parseFloat(discountedPriceText.replace(/[^\d.]/g, ''));
            const quantity = parseInt(quantityInput.value) || 1;
            const maxOrder = parseInt(quantityInput.max) || 99;

            // Calculate values
            const itemSubtotal = price * quantity;
            const itemDiscountedTotal = discountedPrice * quantity;
            
            subtotal += itemSubtotal;
            discountedTotal += itemDiscountedTotal;
            hasItems = true;

            // Update display
            if (subtotalElement) {
                subtotalElement.textContent = `Subtotal: <?php echo $default_currency; ?>${itemDiscountedTotal.toFixed(2)}`;
            }

            // Update button state
            if (plusBtn) {
                plusBtn.disabled = quantity >= maxOrder;
                plusBtn.classList.toggle('force-disabled', quantity >= maxOrder);
            }
        } catch (e) {
            console.error('Error processing cart item:', e);
        }
    });

    // Calculate summary values
    const discount = subtotal - discountedTotal;
    const tax = discountedTotal * taxRate;
    const totalWithTax = discountedTotal + tax;

    // Update summary display
    const currency = '<?php echo $default_currency; ?>';
    const updateSummary = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = hasItems ? `${currency}${value.toFixed(2)}` : `${currency}0.00`;
    };

    updateSummary('summary-subtotal', subtotal);
    updateSummary('summary-discount', discount);
    updateSummary('summary-discounted-total', discountedTotal);
    updateSummary('summary-tax', tax);
    updateSummary('summary-total', totalWithTax);

    console.log('Totals updated:', { subtotal, discountedTotal, tax, totalWithTax }); // Debug log
}

// Function to attach event listeners
function attachEventListeners() {
    // Remove previous event listeners to avoid duplicates
    const removeListeners = (selector, event) => {
        document.querySelectorAll(selector).forEach(el => {
            el.cloneNode(true).replaceWith(el);
        });
    };
    removeListeners('.increase-quantity', 'click');
    removeListeners('.decrease-quantity', 'click');
    removeListeners('.remove-item', 'click');
    removeListeners('.quantity', 'change');

    // Increase quantity
    document.querySelectorAll('.increase-quantity').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const input = button.parentElement.querySelector('.quantity');
            const currentValue = parseInt(input.value) || 1;
            const maxOrder = parseInt(input.max) || 99;
            
            if (currentValue < maxOrder) {
                input.value = currentValue + 1;
                updateCart(input);
                updateTotal();
            }
        });
    });

    // Decrease quantity
    document.querySelectorAll('.decrease-quantity').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const input = button.parentElement.querySelector('.quantity');
            const currentValue = parseInt(input.value) || 1;
            
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateCart(input);
                updateTotal();
            }
        });
    });

    // Remove item
    document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const card = this.closest('[data-product-id]');
        const productId = card.getAttribute('data-product-id');
        
        // Show loading indicator
        const originalHTML = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        this.disabled = true;
        
        removeFromCart(productId);
        
        // Restore button after removal completes
        setTimeout(() => {
            this.innerHTML = originalHTML;
            this.disabled = false;
        }, 300);
    });
});

    // Quantity input change
    document.querySelectorAll('.quantity').forEach(input => {
        input.addEventListener('change', () => {
            const maxOrder = parseInt(input.max) || 99;
            let value = parseInt(input.value) || 1;
            
            if (value > maxOrder) {
                value = maxOrder;
                input.value = value;
                alert(`Maximum quantity is ${maxOrder}`);
            } else if (value < 1) {
                value = 1;
                input.value = value;
            }
            
            updateCart(input);
            updateTotal();
        });
    });
}

// Function to update the cart in localStorage
function updateCart(input) {
    try {
        const card = input.closest('[data-product-id]');
        const productId = card.getAttribute('data-product-id');
        const quantity = parseInt(input.value) || 1;
        const maxOrder = parseInt(input.max) || 99;

        const cart = JSON.parse(localStorage.getItem('cart')) || {};
        
        if (quantity > 0 && quantity <= maxOrder) {
            cart[productId] = {
                quantity: quantity,
                stockLimit: maxOrder
            };
        } else {
            delete cart[productId];
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        console.log('Cart updated:', cart); // Debug log
    } catch (e) {
        console.error('Error updating cart:', e);
    }
}


// Function to remove an item from the cart (FIXED VERSION)
function removeFromCart(productId) {
    // 1. Remove from localStorage
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    delete cart[productId];
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // 2. Remove from UI
    window.location.reload();
    const card = document.querySelector(`[data-product-id="${productId}"]`);
    if (card) {
        card.style.opacity = '0';
        setTimeout(() => {
            card.remove();
            updateCartCount();
            updateTotal();
            
            // If cart is now empty, show message
            if (Object.keys(cart).length === 0) {
                document.getElementById('cart-content').innerHTML = '<div class="alert alert-info">Your cart is empty.</div>';
            }
        }, 300);
    }
}


// Initialize cart on page load
document.addEventListener('DOMContentLoaded', () => {
    // Add disabled button styles
    const style = document.createElement('style');
    style.textContent = `
        .increase-quantity:disabled,
        .force-disabled {
            background-color: var(--third-color) !important;
            color: white !important;
            opacity: 0.7 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            border-color: #ccc !important;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
        }
        
        .quantity-controls .btn {
            padding: 0.25rem 0.5rem;
        }
        
        .quantity-controls .quantity {
            width: 50px;
            text-align: center;
            margin: 0 0.5rem;
        }
    `;
    document.head.appendChild(style);

    // Initialize cart
    sendCartData();
});

// Pass PHP variable to JavaScript
const defaultCurrency = "<?php echo $default_currency; ?>";
console.log('Default currency:', defaultCurrency); // Debug log

</script>


</body>
</html>


                    
