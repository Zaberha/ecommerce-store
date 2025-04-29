<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = 'My Wish List';
$current_page = 'wishlist';
require_once __DIR__ . '/includes/headercheckout.php';
// Redirect to login if user is not logged in

?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Wish List</li>
        </ol>
    </nav>

    <h2>My Wishlist</h2>
    
    <button id="remove-all-button" class="btn btn-primary mb-3">
            <i class="fa-solid fa-trash"></i> Remove All
        </button>
        <div id="wishlist-container"></div>
           
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <!-- Custom JS -->
       <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/wish.js"></script>


 
</body>
</html>