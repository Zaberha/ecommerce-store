<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

// Fetch default_currency from the admin table
$stmt = $conn->query("SELECT default_currency FROM admin LIMIT 1");
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
if ($admin_settings) {
    $default_currency = $admin_settings['default_currency'];

    // Assign a rate based on the default_currency
    switch ($default_currency) {
        case 'AED':
            $rate = 3.67; // Example: 1 USD = 3.67 AED
            break;
        case '$':
            $rate = 1.00; // Example: 1 USD = 1.00 USD
            break;
        case 'SP':
            $rate = 0.27; // Example: 1 USD = 0.27 SP (Saudi Riyal)
            break;
        default:
            $rate = 1.00; // Default rate if currency is not recognized
            break;
    }

}
// Set default page title if not set
$page_title = $page_title ?? 'Home';
$current_page = $current_page ?? '';
$admin = $conn->query("SELECT * FROM admin LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
    <html lang="<?= $_SESSION['lang']; ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $page_title; ?> - <?= $translations['E-Commerce Store']; ?></title>
             <!-- Favicon -->
    <link rel="icon" href="assets/img/icon/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/icon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/icon/apple-touch-icon.png">
    <link rel="manifest" href="assets/img/icon/site.webmanifest">




    <!-- Bootstrap 5 CSS (Latest Version) -->
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
                                <a aria-current="page" href="/" <?php echo $current_page === 'home' ? 'class="nav-link active"' : ''; ?>><?= $translations['Home']; ?></a>
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

                        <a class="icosign" onclick="showPopup('cart')"><i class="fa fa-shopping-cart"></i>(<span id="cart-count">0</span>)</a>
                    <a class="icosign" onclick="showPopup('wishlist')"><i class="fas fa-heart wishlist-icon"></i> (<span id="wishlist-count">0</span>)</a>
                   
                    <?php if ($admin['loyalty_program_enabled']==1): ?>
                    <a class="icosign" href="/redeem.php">Points <i class="fa-solid fa-gem"></i></a>
                    <?php endif; ?>  

                    <a href="/profile.php" <?php echo $current_page === 'profile' ? 'class="active"' : ''; ?>><i class="fa fa-user" aria-hidden="true"></i></a>
                    <a href="/logout.php"><i class="fas fa-sign-out-alt"></i></a>
                        <?php else: ?>
                     <a class="icosign" onclick="showPopup('cart')"><i class="fa fa-shopping-cart"></i>(<span id="cart-count">0</span>)</a>
                    <a class="icosign" onclick="showPopup('wishlist')"><i class="fas fa-heart wishlist-icon"></i> (<span id="wishlist-count">0</span>)</a>
                 
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
