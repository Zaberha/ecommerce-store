<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Loyalty Program';
$current_page = 'loyalty programs';
require_once __DIR__ . '/includes/header.php';
?>

<main>

  <!-- Home | Parallex background -->
  <header id="title">
    <div class="bgimg-4">    
        <h1 class="outline">LOYALTY PROGRAM</h1>   
        
    </div>
  </header>
  <!-- End of Home | Parallex background -->
  
 

    <section class="hero-section text-center">
        <div class="container">

            <h2 class="fw-bold mb-4"><?php echo $admin['store_name'];?> Loyalty Program</h2>
            <p class="lead text-muted mb-5">Earn points with every purchase and unlock exclusive rewards!</p>
           <?php if (!isset($_SESSION['user_id'])) { echo '<a href="register.php" class="btn  btn-light btn-lg px-4 me-2">Join Now</a>';}?> 
            
            
            <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4">Learn More</a>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="container mb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How Our Loyalty Program Works</h2>
            <p class="lead text-muted">It's simple - shop, earn, and redeem!</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card benefit-card p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-shopping-cart fa-3x text-forth"></i>
                    </div>
                    <h3>1. Shop</h3>
                    <p>Make purchases as you normally would. Every Currency unit you spend earns you points.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card benefit-card p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-coins fa-3x text-forth"></i>
                    </div>
                    <h3>2. Earn Points</h3>
                    <p>Watch your points grow with each purchase. More spending = more points!</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card benefit-card p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-tag fa-3x text-forth"></i>
                    </div>
                    <h3>3. Redeem</h3>
                    <p>Convert your points to discount coupons for future purchases.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Point System Section -->
    <section class="container mb-5">
        <div class="point-system">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">Our Point System</h2>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle text-forth me-2"></i> <strong>1 <?php echo  $default_currency; ?> spent = 1 point</strong> earned</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-forth me-2"></i> <strong><?php echo $admin['COLLOYALTY_coupon_threshold'];?> points = <?php echo $admin['loyalty_coupon_percentage'];?> % discount</strong> coupon on next order</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-forth me-2"></i> <strong><?php echo 2*$admin['COLLOYALTY_coupon_threshold'];?> points = <?php echo 2*$admin['loyalty_coupon_percentage'];?> % discount</strong> coupon on next order</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-forth me-2"></i> Points never expires, but once redeemed to Coupon they should be used within <?php echo $admin['loyalty_coupon_expiry_days'];?> days.</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <img src="assets/img/loyalty.jpg" alt="Points chart" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="container mb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Exclusive Member Benefits</h2>
            <p class="lead text-muted">More reasons to join our loyalty program</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card benefit-card p-4 text-center h-100">
                    <div class="mb-3">
                        <i class="fas fa-birthday-cake fa-3x text-forth"></i>
                    </div>
                    <h4>Birthday Rewards</h4>
                    <p>Receive special bonus points during your birthday month.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card benefit-card p-4 text-center h-100">
                    <div class="mb-3">
                        <i class="fas fa-gift fa-3x text-forth"></i>
                    </div>
                    <h4>Exclusive Offers</h4>
                    <p>Access to members-only sales and promotions.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card benefit-card p-4 text-center h-100">
                    <div class="mb-3">
                        <i class="fas fa-rocket fa-3x text-forth"></i>
                    </div>
                    <h4>Early Access</h4>
                    <p>Shop new products before they're available to the public.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card benefit-card p-4 text-center h-100">
                    <div class="mb-3">
                        <i class="fas fa-star fa-3x text-forth"></i>
                    </div>
                    <h4>VIP Tier</h4>
                    <p>Earn bonus points when you reach VIP status.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section text-center">
        <div class="container">
            <h2 class="display-5 fw-bold mb-4">Ready to Start Earning Rewards?</h2>
            <p class="lead text-muted mb-5">Join thousands of members already enjoying exclusive benefits</p>
            <div class="d-flex justify-content-center gap-3">
            <?php if (!isset($_SESSION['user_id'])) { echo '<a href="register.php" class="btn btn-light btn-lg px-4">Sign Up Free</a>';}?> 
                
                <a href="redeem.php" class="btn btn-outline-light btn-lg px-4">Redeem Points Now</a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="container my-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Frequently Asked Questions</h2>
        </div>
        
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        How do I join the loyalty program?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Simply create an account on our website and you'll automatically be enrolled in our loyalty program. There are no fees or special requirements to join.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        How do I check my points balance?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        You can view your current points balance anytime by logging into your account and visiting your profile page. Your points balance is displayed prominently at the top of the page.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        How do I redeem my points?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Visit your profile page and click the "Redeem Points" button. You can choose the amount you want to redeem and the system will generate a discount coupon code for you to use at checkout.
                    </div>
                </div>
            </div>
        </div>
    </section>
  
    </main>

<!-- Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <!-- Custom JS -->
       <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
            </body>
            </html>