const $default_currency = defaultCurrency; // Ensure this is defined globally
let cart = JSON.parse(localStorage.getItem('cart')) || {};
let wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};

// Initialize cart with legacy format support
function initCart() {
    // Convert legacy cart format (quantity-only) to new format (with stockLimit)
    for (const [productId, value] of Object.entries(cart)) {
        if (typeof value === 'number') {
            cart[productId] = {
                quantity: value,
                stockLimit: 10 // Default fallback, will be updated when product loads
            };
        }
    }
    saveCartToStorage();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initCart();
    updateCounts();
});

// Update cart and wishlist counts
function updateCounts() {
    const cartCount = Object.keys(cart).length;
    const wishlistCount = Object.keys(wishlist).length;

    document.getElementById('cart-count').textContent = cartCount;
    document.getElementById('wishlist-count').textContent = wishlistCount;
    document.getElementById('popup-cart-count').textContent = cartCount;
    document.getElementById('popup-wishlist-count').textContent = wishlistCount;
}

// Add item to cart with stock limit check
async function addToCart(productId) {
    try {
        const product = await fetchProductDetails(productId, 'cart');
        if (!product) {
            console.error('Product not found');
            return;
        }

        const stockLimit = product.max_order || product.stock_quantity || 10;
        const currentQty = cart[productId]?.quantity || 0;
        
        if (currentQty >= stockLimit) {
            alert(`Maximum quantity (${stockLimit}) reached for this item`);
            return;
        }
        
        cart[productId] = {
            quantity: currentQty + 1,
            stockLimit: stockLimit
        };
        
        saveCartToStorage();
        updateCounts();
        showPopup('cart');
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
}

// Add item to wishlist
function addToWishlist(productId) {
    console.log('addToWishlist called with productId:', productId);
    wishlist[productId] = (wishlist[productId] || 0) + 1;
    saveWishlistToStorage();
    updateCounts();
    showPopup('wishlist');
}

// Save cart to localStorage
function saveCartToStorage() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Save wishlist to localStorage
function saveWishlistToStorage() {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

// Show popup with the specified tab
function showPopup(tab) {
    console.log('showPopup called with tab:', tab);
    const popup = document.getElementById('popup');
    popup.style.display = 'block';
    setTimeout(() => {
        popup.classList.add('active');
    }, 10);
    showTab(tab);
}

// Hide popup
function hidePopup() {
    console.log('hidePopup called');
    const popup = document.getElementById('popup');
    popup.classList.remove('active');
    setTimeout(() => {
        popup.style.display = 'none';
    }, 500);
}

// Switch between tabs
function showTab(tab) {
    console.log('showTab called with tab:', tab);
    const cartTab = document.getElementById('cart-tab');
    const wishlistTab = document.getElementById('wishlist-tab');
    const cartButton = document.querySelector('.tab-button[onclick="showTab(\'cart\')"]');
    const wishlistButton = document.querySelector('.tab-button[onclick="showTab(\'wishlist\')"]');

    if (tab === 'cart') {
        cartTab.style.display = 'block';
        wishlistTab.style.display = 'none';
        cartButton.classList.add('active');
        wishlistButton.classList.remove('active');
        updateCartItems();
    } else if (tab === 'wishlist') {
        cartTab.style.display = 'none';
        wishlistTab.style.display = 'block';
        cartButton.classList.remove('active');
        wishlistButton.classList.add('active');
        updateWishlistItems();
    }
}

// Fetch product details from the server
async function fetchProductDetails(productId, type = 'cart') {
    try {
        const endpoint = type === 'cart' ? 'get_product_details.php' : 'get_wishlist_product_details.php';
        const response = await fetch(`${endpoint}?id=${productId}`);
        if (!response.ok) {
            throw new Error('Failed to fetch product details');
        }
        const product = await response.json();
        console.log('Fetched product:', product);
        return product;
    } catch (error) {
        console.error('Error fetching product details:', error);
        return null;
    }
}

// Update cart items in the popup
async function updateCartItems() {
    console.log('updateCartItems called');
    const cartItemsDiv = document.getElementById('cart-items');
    cartItemsDiv.innerHTML = '';
    let total = 0;

    for (const [productId, cartItem] of Object.entries(cart)) {
        const product = await fetchProductDetails(productId, 'cart');
        if (!product) continue;

        const quantity = cartItem.quantity;
        const stockLimit = cartItem.stockLimit;
        const price = parseFloat(product.price) || 0;
        const discountPercentage = parseFloat(product.discount_percentage) || 0;
        const discountedPrice = price * (1 - discountPercentage / 100);
        const subtotal = quantity * discountedPrice;
        total += subtotal;

        const item = document.createElement('div');
        item.className = 'cart-item';
        item.innerHTML = `
            <img src="admin/images/${product.main_image}" alt="${product.name}">
            <div class="cart-item-details">
                <a href="product_details.php?id=${productId}"><div class="the_name">${product.name}</div></a>
                <p>${$default_currency}${discountedPrice.toFixed(2)}</p>
                <p>Subtotal: ${$default_currency}${subtotal.toFixed(2)}</p>
            </div>
            <div class="cart-item-details">
                <p> 
                    <button onclick="event.stopPropagation(); decreaseQuantity(${productId}, event)">-</button>
                    <span>${quantity}</span>
                    <button onclick="event.stopPropagation(); increaseQuantity(${productId}, event)"
                        ${quantity >= stockLimit ? 'disabled' : ''}>
                        +
                    </button>
                </p>
                <p style="text-align:center;"> 
                    <a class="icosign" onclick="event.stopPropagation(); removeFromCart(${productId})">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                    <a class="icosign" onclick="event.stopPropagation(); moveToWishlist(${productId})">
                        <i class="fas fa-heart wishlist-icon"></i>
                    </a>
                </p>
            </div>
        `;
        cartItemsDiv.appendChild(item);
    }

    // Display total
    const totalDiv = document.createElement('div');
    totalDiv.innerHTML = `<h5>Total: ${$default_currency}${total.toFixed(2)}</h5>`;
    cartItemsDiv.appendChild(totalDiv);

    // Create footer
    const cartFooter = document.createElement('div');
    cartFooter.className = 'cart-footer';
    cartFooter.innerHTML = `
        <button onclick="window.location.href='cart.php'">Show Cart</button>
        <button onclick="window.location.href='checkout.php'">Go to Checkout</button>
    `;
    cartItemsDiv.appendChild(cartFooter);
}

// Cart quantity functions
function increaseQuantity(productId, event) {
    event.stopPropagation();
    const cartItem = cart[productId];
    
    if (!cartItem) return;
    
    if (cartItem.quantity >= cartItem.stockLimit) {
        alert(`Maximum quantity (${cartItem.stockLimit}) reached`);
        return;
    }
    
    cartItem.quantity += 1;
    saveCartToStorage();
    updateCartItems();
    updateCounts();
}

function decreaseQuantity(productId, event) {
    event.stopPropagation();
    const cartItem = cart[productId];
    
    if (!cartItem) return;
    
    if (cartItem.quantity > 1) {
        cartItem.quantity -= 1;
    } else {
        delete cart[productId];
    }
    
    saveCartToStorage();
    updateCartItems();
    updateCounts();
}

function removeFromCart(productId) {
    delete cart[productId];
    saveCartToStorage();
    updateCartItems();
    updateCounts();
}

function moveToWishlist(productId) {
    if (cart[productId]) {
        addToWishlist(productId);
        removeFromCart(productId);
    }
}

// Wishlist functions
async function updateWishlistItems() {
    console.log('updateWishlistItems called');
    const wishlistItemsDiv = document.getElementById('wishlist-items');
    wishlistItemsDiv.innerHTML = '';
    let total = 0;

    for (const [productId, quantity] of Object.entries(wishlist)) {
        const product = await fetchProductDetails(productId, 'wishlist');
        if (!product) continue;

        const price = parseFloat(product.price) || 0;
        const discountPercentage = parseFloat(product.discount_percentage) || 0;
        const discountedPrice = price * (1 - discountPercentage / 100);
        const subtotal = quantity * discountedPrice;
        total += subtotal;

        const item = document.createElement('div');
        item.className = 'wishlist-item';
        item.innerHTML = `
            <img src="admin/images/${product.main_image}" alt="${product.name}">
            <div class="wishlist-item-details">
             
                 <a href="product_details.php?id=${productId}"><div class="the_name">${product.name}</div></a>
                <p>${$default_currency}${price.toFixed(2)}</p>
                <p>Discounted Price: ${$default_currency}${discountedPrice.toFixed(2)}</p>
                </div>
            <div class="wishlist-item-details text-center">

                <p>
                    <a class="icosign" onclick="event.stopPropagation(); removeFromWishlist(${productId})">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                    <a class="icosign" onclick="event.stopPropagation(); moveToCart(${productId})">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                </p>
            </div>
        `;
        wishlistItemsDiv.appendChild(item);
    }

    // Display total
    const totalDiv = document.createElement('div');
    totalDiv.innerHTML = `<h5>Total: ${$default_currency}${total.toFixed(2)}</h5>`;
    wishlistItemsDiv.appendChild(totalDiv);

    // Create footer
    const wishlistFooter = document.createElement('div');
    wishlistFooter.className = 'wishlist-footer';
    wishlistFooter.innerHTML = `
        <button onclick="window.location.href='wishlist.php'">Go to Wishlist</button>
    `;
    wishlistItemsDiv.appendChild(wishlistFooter);
}

function removeFromWishlist(productId) {
    delete wishlist[productId];
    saveWishlistToStorage();
    updateWishlistItems();
    updateCounts();
}

function moveToCart(productId) {
    if (wishlist[productId]) {
        addToCart(productId);
        removeFromWishlist(productId);
    }
}

// Close popup when clicking outside
document.addEventListener('click', (event) => {
    const popup = document.getElementById('popup');
    const popupContent = document.querySelector('.popup-content');
    if (popup && popup.classList.contains('active') && !popupContent.contains(event.target)) {
        hidePopup();
    }
});