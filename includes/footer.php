<?php
$admin = $conn->query("SELECT * FROM admin LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>

<footer class="footer-section">
        <div class="container-fluid">
            <div class="footer-cta pt-5 pb-5">
                <div class="row">
                    <div class="col-xl-4 col-md-4 mb-30">
                        <div class="single-cta">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="cta-text">
                                <h4>Find us</h4>
                                <span><?php 
                        echo ($admin['store_address'] ?? '123 Street') . ', ' . 
                             ($admin['store_city'] ?? 'City') . ', ' . 
                             ($admin['store_country'] ?? 'Country'); 
                        ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4 mb-30">
                        <div class="single-cta">
                            <i class="fas fa-phone"></i>
                            <div class="cta-text">
                                <h4>Call us</h4>
                                <span><?php echo $admin['store_phone'] ?? '+1234567890'; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4 mb-30">
                        <div class="single-cta">
                            <i class="far fa-envelope-open"></i>
                            <div class="cta-text">
                                <h4>Mail us</h4>
                                <span><?php echo $admin['store_email'] ?? 'contact@example.com'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-content pt-5 pb-5">
                <div class="row">
                    <div class="col-xl-4 col-lg-4 mb-50">
                        <div class="footer-widget">
                            <div class="footer-logo">
                                <a href="index.html"><img src="admin/<?php echo htmlspecialchars($admin['business_logo']); ?>"  width="136.5px" height="52.5px" alt="<?php echo htmlspecialchars($admin['store_name']); ?>"></a>
                            </div>
                            <div class="footer-text">
                                <p>Your trusted online shopping destination for quality products and excellent service.</p>
                            </div>
                            <div class="footer-social-icon">
                                <span>Follow us</span>
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
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                        <div class="footer-widget">
                            <div class="footer-widget-heading">
                                <h3>Useful Links</h3>
                            </div>
                            <ul id="links">
<li><a href="index.php">Home</a></li>
<li><a href="about.php">About</a></li>
<li><a href="track.php">Track Order</a></li>
<li><a href="loyaltyprogram.php">Loyalty program</a></li>
<li><a href="news.php">Latest News</a></li>
<li><a href="blog.php">blog</a></li>
<li><a href="contact.php">Contact</a></li>
<li><a href="admin/admin_login.php">Admin Login</a></li>
<li><a href="terms.php">Terms and Conditions</a></li>
<li><a href="return-refund-policy.php">Return & Refund Policy</a></li>
<li><a href="privacy-policy.php">Privacy Policy</a></li>
</ul>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-50">
                        <div class="footer-widget">
                            <div class="footer-widget-heading">
                                <h3>Subscribe</h3>
                            </div>
                            <div class="footer-text mb-25">
                                <p>Don’t miss to subscribe to our new feeds, kindly fill the form below.</p>
                            </div>
                            <div class="subscribe-form">
                                <form action="#">
                                    <input type="text" placeholder="Email Address">
                                    <button><i class="fab fa-telegram-plane"></i></button>
                                </form>
                            </div>
                            <div class="footer-widget mt-2">
                            <div class="footer-widget-heading">
                                <h3>Payment Options</h3>
                            </div>
                            <img class="img-fluid" src="assets/img/payments.png" alt="Payment Options">
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright-area">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6 col-lg-6 text-center text-lg-left">
                        <div class="copyright-text">
                            <p>&copy; <?php echo date('Y'); ?> <?php echo $admin['store_name'] ?? 'E-commerce Store'; ?>. All rights reserved.</p>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 d-none d-lg-block text-right">
                        <div class="footer-menu">
                            <a href="https://www.advancedpromedia.com">developed by Advanced Promedia Digital Marketing, UAE</a>

                            
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </footer>
    <!-- Popup for Cart and Wishlist -->
<div id="popup" class="popup">
    <div class="popup-overlay" onclick="hidePopup()"></div>
    <div class="popup-content">
        <div class="top-cart">
        <span class="close-btn btn-close" onclick="hidePopup()"></span>
        </div>
        <div class="tabs">
            <button class="tab-button" onclick="showTab('cart')">
                Shopping Cart (<span id="popup-cart-count">0</span>)
            </button>
            <button class="tab-button" onclick="showTab('wishlist')">
                Wishlist (<span id="popup-wishlist-count">0</span>)
            </button>
        </div>
        <div id="cart-tab" class="tab-contents">
           <hr/>
        <h5>Your Cart</h5>
            <div id="cart-items"></div>
        </div>
        <div id="wishlist-tab" class="tab-contents" style="display: none;">
        <hr/>
        <h5>Your Wishlist</h5>
            <div id="wishlist-items"></div>
        </div>
    </div>
</div>


<!-- Abandoned Cart Popup -->
<div id="abandoned-cart-popup" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <?php if ($admin['auto_registration_coupon']) echo'<h3>Great Deals are here!</h3>
        <p>Register now and get <strong>'.$admin['registration_coupon_percentage'].' discount</strong> on your first order!</p>'; else echo'<h3>Great Deals are here!</h3>
        <p>Register now and start you shopping experience!</p>';?>
        
       <button class="btn btn-primary mb-2"><a href="register.php" class="text-light">Register</a></button>
        <p>Subscribe to <strong>Best Deals</strong> notifications via our Newsletter!</p>
        <form id="save-cart-form">
            <input type="email" id="visitor-email" placeholder="Enter your email" required class="mb-3">
            <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('abandoned-cart-popup');
    const closeBtn = document.querySelector('.close');
    const saveCartForm = document.getElementById('save-cart-form');
    const visitorEmailInput = document.getElementById('visitor-email');

    // Check if user is logged in (from PHP session)
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    // Check if we've shown the popup before (using sessionStorage)
    const popupShown = sessionStorage.getItem('popupShown');

    // Only set up the exit intent if:
    // 1. User is NOT logged in
    // 2. Popup hasn't been shown before
    if (!isLoggedIn && !popupShown) {
        window.addEventListener('mouseout', function(e) {
            // Only trigger when mouse leaves top of window
            if (e.clientY < 50) {
                // Show the popup
                modal.style.display = 'block';
                
                // Mark as shown (using sessionStorage so it persists until browser closes)
                sessionStorage.setItem('popupShown', 'true');
                
                // Remove the event listener so it doesn't trigger again
                window.removeEventListener('mouseout', arguments.callee);
            }
        });
    }

    // Close button handler
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Form submission handler
    saveCartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = visitorEmailInput.value.trim();

        if (!email) {
            alert('Please enter a valid email address.');
            return;
        }

        fetch('save_abandoned_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                cart_data: localStorage.getItem('cart'),
                check_existing: true
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                alert('You have already subscribed to our newsletter!');
            } else if (data.success) {
                alert('Thank you for subscribing!');
            }
            modal.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});
</script>




<script>
  //subscribe form
  document.addEventListener('DOMContentLoaded', function() {
    // Get the form element
    const form = document.querySelector('form[action="#"]');
    
    // Add submit event listener
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get the email input
        const emailInput = form.querySelector('input[type="text"]');
        const email = emailInput.value.trim();
        
        // Validate email
        if (!email) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Simple email validation
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Get cart data from localStorage (if available)
        const cartData = localStorage.getItem('cart') || '';
        
        // Send the data to the server
        fetch('save_abandoned_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                cart_data: cartData,
                check_existing: true
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.exists) {
                alert('This email is already subscribed to our newsletter!');
            } else if (data.success) {
                alert(data.message || 'Thank you for subscribing! Check your email for discount codes and offers.');
                // Clear the form
                emailInput.value = '';
            } else {
                throw new Error(data.message || 'Subscription failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'An error occurred. Please try again.');
        });
    });
});
</script>




<?php
// get cart and wishlist upon logout

// Only proceed if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    ?>
    <script>
    // Enhanced sync function with error handling
    async function syncUserData() {
        try {
            const cartData = localStorage.getItem('cart');
            const wishlistData = localStorage.getItem('wishlist');
            
            if (!cartData && !wishlistData) return true;
            
            const response = await fetch('sync_user_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: <?php echo $user_id; ?>,
                    cart_data: cartData,
                    wishlist_data: wishlistData
                })
            });
            
            if (!response.ok) {
                console.error('Sync failed:', await response.text());
                return false;
            }
            
            console.log('Data synced successfully');
            return true;
        } catch (error) {
            console.error('Sync error:', error);
            return false;
        }
    }

    // 1. Sync on tab/browser close
    window.addEventListener('beforeunload', function(e) {
        if (localStorage.getItem('cart') || localStorage.getItem('wishlist')) {
            // This will attempt to sync but won't block closing
            navigator.sendBeacon('sync_user_data.php', JSON.stringify({
                user_id: <?php echo $user_id; ?>,
                cart_data: localStorage.getItem('cart'),
                wishlist_data: localStorage.getItem('wishlist')
            }));
        }
    });

    // 2. Sync after 29 minutes of inactivity
    let inactivityTimer = setTimeout(() => {
        syncUserData();
    }, 1740000); // 29 minutes

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            syncUserData();
        }, 1740000);
    }

    // Activity listeners
    ['mousemove', 'keypress', 'click', 'scroll'].forEach(event => {
        document.addEventListener(event, resetInactivityTimer, { passive: true });
    });

    // 3. Enhanced logout handling
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a[href*="logout.php"]').forEach(link => {
            link.addEventListener('click', async function(e) {
                e.preventDefault();
                const logoutUrl = this.href;
                
                // Show loading indicator if needed
                // document.body.style.cursor = 'wait';
                
                try {
                    const syncSuccess = await syncUserData();
                    if (syncSuccess) {
                        window.location.href = logoutUrl;
                    } else {
                        // Option 1: Proceed with logout anyway
                        window.location.href = logoutUrl;
                        
                        // Option 2: Show error and retry
                        // alert('Sync failed. Please try again.');
                        // setTimeout(() => window.location.href = logoutUrl, 2000);
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                    window.location.href = logoutUrl;
                }
            });
        });
    });
    </script>
    <?php
}
?>

<div id="chatbot-container">
  <div id="chatbot-button">
    <i class="fas fa-robot"></i>
  </div>
  <div id="chatbot-window" style="display:none;">
    <div class="chatbot-header">
      <span>Store Assistant</span>
      <button id="chatbot-close"><i class="fas fa-times"></i></button>
    </div>
    <div id="chatbot-messages">
      <div class="bot-message">
        How can I help you today?
        <div style="margin-top:10px">
          <button class="quick-btn" data-action="track-order">Track Your Order</button>
          <button class="quick-btn" data-action="trending-products">Best Selling</button>
          <button class="quick-btn" data-action="promotions">Show All Promotions</button>
          <button class="quick-btn" data-action="create-account">Create Account</button>
          <button class="quick-btn" data-action="open-case">Open Case</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CSS -->
<style>
  #chatbot-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
  }
  #chatbot-button {
    background: var(--main-color);
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
  }
  #chatbot-window {
    width: 320px;
    height: 400px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
  }
  .chatbot-header {
    background: var(--second-color);
    color: white;
    padding: 12px 15px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
  }
  #chatbot-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
  }
  .bot-message, .user-message {
    padding: 10px 12px;
    margin: 5px 0;
    border-radius: 10px;
    max-width: 80%;
  }
  .bot-message {
    background: #f1f1f1;
  }
  .quick-btn {
    display: block;
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    background: var(--second-color);
    border: none;
    color: var(--main-color);
    border-radius: 5px;
    cursor: pointer;
    text-align: left;
  }
</style>

<!-- Update your JavaScript to this: -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Get all elements
  const chatbotButton = document.getElementById('chatbot-button');
  const chatbotWindow = document.getElementById('chatbot-window');
  const chatbotClose = document.getElementById('chatbot-close');
  const chatbotMessages = document.getElementById('chatbot-messages');
  
  // Create input area for text chat
  const chatInputContainer = document.createElement('div');
  chatInputContainer.style.display = 'flex';
  chatInputContainer.style.padding = '10px';
  chatInputContainer.style.borderTop = '1px solid #eee';
  
  const chatInput = document.createElement('input');
  chatInput.type = 'text';
  chatInput.placeholder = 'Type your message...';
  chatInput.style.flex = '1';
  chatInput.style.padding = '8px';
  chatInput.style.border = '1px solid #ddd';
  chatInput.style.borderRadius = '4px';
  
  const sendButton = document.createElement('button');
  sendButton.innerHTML = 'Send';
  sendButton.style.marginLeft = '8px';
  sendButton.style.padding = '8px 12px';
  sendButton.style.background = '#4e8cff';
  sendButton.style.color = 'white';
  sendButton.style.border = 'none';
  sendButton.style.borderRadius = '4px';
  sendButton.style.cursor = 'pointer';
  
  chatInputContainer.appendChild(chatInput);
  chatInputContainer.appendChild(sendButton);
  chatbotWindow.appendChild(chatInputContainer);

  // Toggle chat window visibility
  chatbotButton.addEventListener('click', function() {
    chatbotWindow.style.display = chatbotWindow.style.display === 'none' ? 'flex' : 'none';
    if (chatbotWindow.style.display !== 'none') {
      chatInput.focus();
    }
  });
  
  // Close chat window
  chatbotClose.addEventListener('click', function() {
    chatbotWindow.style.display = 'none';
  });
  
  // Add message to chat and return the element
  function addMessage(text, sender) {
    const msgDiv = document.createElement('div');
    msgDiv.className = sender + '-message';
    msgDiv.innerHTML = text;
    chatbotMessages.appendChild(msgDiv);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    return msgDiv;
  }
  
  // Handle all button actions
  document.querySelectorAll('.quick-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const action = this.getAttribute('data-action');
      handleAction(action);
    });
  });
  
  // Handle text input
  function handleTextInput(text) {
    addMessage(text, 'user');
    
    // Process the text input and generate appropriate response
    const normalizedText = text.toLowerCase().trim();
    
    // Greetings
    if (/^(hi|hello|hey|good morning|good afternoon|good evening)/i.test(normalizedText)) {
      const greetings = ["Hello! How can I help you today?", "Hi there! What can I do for you?", "Good day! How may I assist you?"];
      addMessage(greetings[Math.floor(Math.random() * greetings.length)], 'bot');
      return;
    }
    

    if (/^(stupid|fuck you|bad)/i.test(normalizedText)) {
      const greetings = ["Please show some respect", "only respectful languge is accepted", "Sorry we can not help you?"];
      addMessage(greetings[Math.floor(Math.random() * greetings.length)], 'bot');
      return;
    }
    if (/^(delivery problem|problem|I have payment problem)/i.test(normalizedText)) {
      const greetings = ["We are sorry for your problem please send us a message or call 9999", "We are sorry for your problem, please call us for direct reporting"];
      addMessage(greetings[Math.floor(Math.random() * greetings.length)], 'bot');
      return;
    }
    // Delivery questions
    if (normalizedText.includes('deliver') || normalizedText.includes('delivery')) {
      if (normalizedText.includes('dubai')) {
        addMessage('Yes, we deliver to Dubai! Delivery typically takes 3-5 business days.', 'bot');
      } else if (normalizedText.includes('late') || normalizedText.includes('delay')) {
        addMessage('For delivery delays, please check your order status <a href="track.php" style="color:#4e8cff">here</a> or contact our support team.', 'bot');
      } else {
        addMessage('We offer delivery in GCC Countries only. Standard delivery takes 3-7 business days depending on location.', 'bot');
      }
      return;
    }
    
    // Order tracking
    if (normalizedText.includes('track') || normalizedText.includes('order status')) {
      handleAction('track-order');
      return;
    }
    
    // Products
    if (normalizedText.includes('trending') || normalizedText.includes('popular')) {
      handleAction('trending-products');
      return;
    }
    
    if (normalizedText.includes('bestsell') || normalizedText.includes('top product')) {
      addMessage('You asked: Bestselling products', 'user');
      // You could create a separate bestsellers action similar to trending-products
      handleAction('trending-products'); // Using same for now
      return;
    }
    
    // Promotions and offers
    if (normalizedText.includes('promo') || normalizedText.includes('offer') || normalizedText.includes('discount')) {
      handleAction('promotions');
      return;
    }
    
    // Account registration
    if (normalizedText.includes('register') || normalizedText.includes('account') || normalizedText.includes('sign up')) {
      handleAction('create-account');
      return;
    }
    
    // Support cases
    if (normalizedText.includes('case') || normalizedText.includes('support') || normalizedText.includes('help')) {
      handleAction('open-case');
      return;
    }
    
    // Default response for unrecognized queries
    const defaultResponses = [
      "I'm not sure I understand. Could you rephrase that?",
      "I can help with order tracking, products, promotions and more. What do you need?",
      "Sorry, I didn't get that. Here are some things I can help with:",
    ];
    
    const defaultResponse = defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
    addMessage(defaultResponse, 'bot');
    
    // Show quick buttons again for guidance
    const quickButtons = `
      <div style="margin-top:10px">
        <button class="quick-btn" data-action="track-order">Track Your Order</button>
        <button class="quick-btn" data-action="trending-products">Best selling</button>
        <button class="quick-btn" data-action="promotions">Show All Promotions</button>
        <button class="quick-btn" data-action="create-account">Create Account</button>
        <button class="quick-btn" data-action="open-case">Open Case</button>
      </div>
    `;
    addMessage(quickButtons, 'bot');
    
    // Reattach event listeners to new buttons
    document.querySelectorAll('.quick-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const action = this.getAttribute('data-action');
        handleAction(action);
      });
    });
  }
  
  // Send message when button is clicked
  sendButton.addEventListener('click', function() {
    if (chatInput.value.trim() !== '') {
      handleTextInput(chatInput.value);
      chatInput.value = '';
    }
  });
  
  // Send message when Enter is pressed
  chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && chatInput.value.trim() !== '') {
      handleTextInput(chatInput.value);
      chatInput.value = '';
    }
  });

  // Format price (handles comma-separated values)
  function formatPrice(price) {
    if (typeof price === 'string') {
      return parseFloat(price.replace(/,/g, '')).toFixed(2);
    }
    return price.toFixed(2);
  }
  
  // Main action handler
  async function handleAction(action) {
    switch(action) {
      case 'track-order':
        addMessage('You asked: Track my order', 'user');
        addMessage('You can track your order <a href="track.php" style="color:#4e8cff;text-decoration:underline">here</a>', 'bot');
        break;
        
      case 'trending-products':
        addMessage('You asked: Best selling products', 'user');
        const loadingMsg = addMessage('⌛ Loading our best products...', 'bot');
        
        try {
          const response = await fetch('trending_products.php?_=' + Date.now());
          
          if (!response.ok) {
            throw new Error(`Server returned ${response.status} status`);
          }
          
          const products = await response.json();
          
          // Remove loading message
          loadingMsg.remove();
          
          if (!products || products.length === 0) {
            addMessage('No trending products available right now', 'bot');
            return;
          }
          
          let productsHTML = `
            <div style="font-weight:bold;margin-bottom:8px">🔥 Best Selling Now</div>
            <div style="display:grid;gap:12px">
          `;
          
          products.forEach(product => {
            const priceValue = parseFloat(formatPrice(product.price));
            let priceHTML = `${priceValue.toFixed(2)}`;
            
            if (product.discount_percentage && !isNaN(product.discount_percentage)) {
              const discount = parseFloat(product.discount_percentage);
              const discountedPrice = priceValue * (1 - discount/100);
              priceHTML = `
                <span style="text-decoration:line-through;color:#999;margin-right:5px">
                  $${priceValue.toFixed(2)}
                </span>
                <span style="color:#ff4757;font-weight:bold">
                  $${discountedPrice.toFixed(2)}
                </span>
                <span style="background:#ff4757;color:white;padding:2px 6px;border-radius:4px;font-size:0.8em;margin-left:5px">
                  ${discount}% OFF
                </span>
              `;
            }
            
            productsHTML += `
              <div style="border:1px solid #eee;border-radius:8px;padding:10px">
                <a href="product_details.php?id=${product.id}" style="text-decoration:none;color:inherit">
                  <div style="display:flex;gap:12px">
                    <img src="admin/images/${product.image || 'images/default-product.jpg'}" 
                         style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #eee"
                         alt="${product.name}">
                    <div style="flex:1">
                      <div style="font-weight:500">${product.name}</div>
                      <div style="margin-top:5px">
                        ${priceHTML}
                      </div>
                    </div>
                  </div>
                </a>
              </div>
            `;
          });
          
          productsHTML += '</div>';
          addMessage(productsHTML, 'bot');
          
        } catch (error) {
          console.error('Error loading products:', error);
          loadingMsg.remove();
          
          addMessage(`
            <div>⚠️ Couldn't load products right now.</div>
            <div style="margin-top:8px">
              <a href="products.php" style="color:#4e8cff;text-decoration:underline">
                Browse all products
              </a>
            </div>
          `, 'bot');
        }
        break;
        
      case 'promotions':
        addMessage('You asked: Current promotions', 'user');
        window.location.href = 'promotions.php';
        break;
        
      case 'create-account':
        addMessage('You asked: Create account', 'user');
        window.location.href = 'register.php';
        break;
        
      case 'open-case':
        addMessage('You asked: Open support case', 'user');
        window.location.href = 'contact.php';
        break;
        
      default:
        addMessage('How else can I help you today?', 'bot');
    }
  }
});

// Global function for Add to Cart buttons
function addToCart(productId) {
  fetch('add_to_cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ product_id: productId })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      const chatMessages = document.getElementById('chatbot-messages');
      const msgDiv = document.createElement('div');
      msgDiv.className = 'bot-message';
      msgDiv.innerHTML = '✓ Added to cart!';
      chatMessages.appendChild(msgDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
  })
  .catch(error => {
    console.error('Add to Cart Error:', error);
  });
}
</script>

