<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'About';
$current_page = 'about';
require_once __DIR__ . '/includes/header.php';
?>

<main>

  <!-- Home | Parallex background -->
  <header id="title">
    <div class="bgimg-1">    
        <h1 class="outline">ABOUT US</h1>       
    </div>
  </header>
  <!-- End of Home | Parallex background -->
  
  
  <!-- About -->
  <section class="section">
    <div class="container"> <!-- Start of container -->
    <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
    <!-- New Products Section -->
    <section class="section">
    <h2 class="fw-bold">ABOUT</h2>
        <h3>We are Designers, Engineers and Planners</h3>
        <p>ADVANCED PROMEDIA DIGITAL MARKETING was established in 2012, to be your One-Stop Shop for best of a kind  Integrated Digital Marketing & Creative Services.</p>
        <p>We offer the A to Z full spectrum of professional digital and conventional marketing along with remarkable design services to help all kind of verticals having correct tools that reflect their messages, enhance their client's access and achieve their marketing goals.</p>
<p>        More than 35 engineer, programmer, designer, marketing expert and planner are working with passion in our production house to fulfill the increasing demand of creative services in MENA Region.</p>
        </div>
    </section>
    </div> <!-- End of container -->
  </section>
  <!-- End of About -->
  
  
  <!-- Parallax background -->
  <div class="bgimg-2">
  </div>
  <!-- End of Parallax background -->
  
  
  <!-- Start of Places -->
  <section id="places">
   <div class="container"> <!-- Start of container -->
    



  <!-- Best Selling Products Section -->
  <section class="section">

        <h3>Values</h3>
     <p>COMMITMENT to support you at every step of the mission until your objectives are achieved at highest QUALITY using deep academic KNOWLEDGE and latest TECHNOLOGY.
</p>
    </section>

    </div>  <!-- End of container -->
  </section>
  <!-- End of Places -->
  
  
   <!-- Parallax background -->
   <div class="bgimg-3">
   </div>
   <!-- End of Parallax background -->
  
  
   <!-- Resources -->
   <section id="resources">
    <div class="container"> <!-- Start of container -->
     
<!-- Special Offers Section -->
<section class="section">
        <h3>We boost your brand traffic</h3>
<p>Unlock your brand’s full potential with our expert design and marketing solutions! 🚀 From stunning visuals to powerful campaigns, we craft strategies that captivate and convert. Our team blends creativity with data-driven insights to elevate your business. Stand out from the competition with compelling branding and digital excellence. Let’s turn your vision into success—partner with us today!
</p>
    </section>


    </div> <!-- End of container -->
  </section>
  <!-- End of Resources -->
  
  

    
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