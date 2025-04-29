const $default_currency = defaultCurrency; // Ensure this is defined globally

// Function to update cart and wishlist counts
function updateCounts() {
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};

    const cartCount = Object.keys(cart).length;
    const wishlistCount = Object.keys(wishlist).length;

    document.getElementById('cart-count').textContent = cartCount;
    document.getElementById('wishlist-count').textContent = wishlistCount;
}

// Function to display wishlist items
async function displayWishlistItems() {
    const wishlistContainer = document.getElementById('wishlist-container');
    const removeAllButton = document.getElementById('remove-all-button');
    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};

    wishlistContainer.innerHTML = '';

    if (Object.keys(wishlist).length === 0) {
        wishlistContainer.innerHTML = `
            <div class="text-muted"">
                Your wishlist is empty.
            </div>
        `;
        removeAllButton.style.display = 'none';
    } else {
        removeAllButton.style.display = 'block';
    }

    for (const [productId, quantity] of Object.entries(wishlist)) {
        const product = await fetchProductDetails(productId);

        if (product && product.price !== undefined) {
            const item = document.createElement('div');
            item.className = 'card mb-3';
            item.innerHTML = `
                <div class="row g-0">
                    <div class="col-md-3">
                    <a href="product_details.php?id=${productId} ?>">
                        <img src="admin/images/${product.main_image}" class="product-img rounded-start p-2" alt="${product.name}">
                        </a>
                    </div>
                    <div class="col-md-9">
                        <div class="card-body">
                        <a href="product_details.php?id=${productId} ?>">
                            <h5 class="card-title">${product.name}</h5></a>
                            <p class="card-text">Price: ${$default_currency}${product.price.toFixed(2)}</p>
                            <p class="card-text">Quantity: ${quantity}</p>
                            <div class="d-flex gap-2">
                                <a class="ico" onclick="removeFromWishlist('${productId}')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                                <a class="ico" onclick="moveToCart('${productId}')">
                                    <i class="fa-solid fa-cart-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            wishlistContainer.appendChild(item);
        } else {
            console.error('Product details not found or invalid for ID:', productId);
        }
    }
}

// Function to remove an item from the wishlist
function removeFromWishlist(productId) {
    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};
    delete wishlist[productId];
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    displayWishlistItems();
    updateCounts(); // Update counts after removing from wishlist
}

// Function to move an item from the wishlist to the cart
function moveToCart(productId) {
    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};
    const cart = JSON.parse(localStorage.getItem('cart')) || {};

    cart[productId] = (cart[productId] || 0) + 1;
    localStorage.setItem('cart', JSON.stringify(cart));

    delete wishlist[productId];
    localStorage.setItem('wishlist', JSON.stringify(wishlist));

    displayWishlistItems();
    updateCounts(); // Update counts after moving to cart
    alert('Item moved to cart!');
}

// Function to remove all items from the wishlist
function removeAllItems() {
    localStorage.removeItem('wishlist');
    displayWishlistItems();
    updateCounts(); // Update counts after removing all items
}

// Function to fetch product details
async function fetchProductDetails(productId) {
    try {
        const response = await fetch(`get_product_details.php?id=${productId}`);
        if (!response.ok) {
            throw new Error('Failed to fetch product details');
        }
        const product = await response.json();
        return product;
    } catch (error) {
        console.error('Error fetching product details:', error);
        return null;
    }
}

// Initialize the wishlist display on page load
document.addEventListener('DOMContentLoaded', function () {
    const removeAllButton = document.getElementById('remove-all-button');
    removeAllButton.addEventListener('click', removeAllItems);
    displayWishlistItems();
    updateCounts(); // Initialize counts on page load
});