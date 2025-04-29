<?php
// Enforce HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
session_start();

if (isset($_SESSION['user_id'])) {
    // Redirect back to the previous page
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        // If there's no referrer, redirect to a default page (e.g., home page)
        header('Location: index.php');
    }
    exit(); // Make sure to exit after redirect
}

// Load required files
require 'admin/PHPMailer/src/PHPMailer.php';
require 'admin/PHPMailer/src/SMTP.php';
require 'admin/PHPMailer/src/Exception.php';
require 'db.php'; // Uses existing connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
ob_start(); // Start output buffering

// Set page title
$page_title = 'Register';
$current_page = 'register';
require_once __DIR__ . '/includes/header.php';

// Generate Welcome Coupon
function generateCouponCode($length = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

// Initialize error variable
$error = '';
// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to send welcome email
function sendWelcomeEmail($to, $first_name, $username, $password, $store_info, $showcode,$loyal) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username = 'promotions@advancedpromedia.net';
        $mail->Password = 'Myweb2025@';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption
        $mail->Port       = 465;                         // TCP port (465 for SSL, 587 for TLS)
        $mail->SMTPSecure = 'ssl';
        $mail->CharSet = 'UTF-8';
    
        $mail->setFrom('promotions@advancedpromedia.net', 'Advanced Promedia');
        $mail->addReplyTo('info@advancedpromedia.com', 'Customer Support');
        $mail->Sender = 'promotions@advancedpromedia.net'; // Envelope sender
        $mail->addCustomHeader('X-Originating-IP: ' . $_SERVER['SERVER_ADDR']);
        $mail->addCustomHeader('Precedence: bulk');
        $mail->addCustomHeader('List-Unsubscribe: <mailto:unsubscribe@advancedpromedia.net>');
        
        // Recipients
        $mail->setFrom($store_info['store_email'], $store_info['store_name']);
        $mail->addAddress($to, $first_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Welcome to " . htmlspecialchars($store_info['store_name']);
        
        // HTML email content (your existing template)
        $mail->Body = '<!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
                .button { 
                    display: inline-block; 
                    padding: 10px 20px; 
                    background-color: #007bff; 
                    color: white !important; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 10px 0;
                }
                .credentials { 
                    background-color: #f0f0f0; 
                    padding: 15px; 
                    border-radius: 5px; 
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Welcome to ' . htmlspecialchars($store_info['store_name']) . '</h1>
                </div>
                
                <div class="content">
                    <p>Hi ' . htmlspecialchars($first_name) . ',</p>
                    
                    <p>A warm welcome to ' . htmlspecialchars($store_info['store_name']) . '! Thank you for registering with us.</p>
                    '.$showcode.'
                    <div class="credentials">
                        <p>Your account details:</p>
                        <p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
                        <p><strong>Password:</strong> ' . htmlspecialchars($password) . '</p>
                    </div>
                    
                    <p>You may now login to your account here:</p>
                    <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/login.php" class="button">Login to Your Account</a></p>
                    
                    <p>Check out our current offers and promotions:</p>
                    <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/promotions.php" class="button">View Our Offers</a></p>
                    
                    '.$loyal.'
                </div>
                
                <div class="footer">
                    <p><img src="https://' . $_SERVER['HTTP_HOST'] . '/admin/' . htmlspecialchars($store_info['business_logo']) . '" alt="' . htmlspecialchars($store_info['store_name']) . '" style="max-width: 150px;"></p>
                    <p>' . htmlspecialchars($store_info['store_name']) . '</p>
                    <p>Phone: ' . htmlspecialchars($store_info['store_phone']) . '</p>
                    <p>Email: ' . htmlspecialchars($store_info['store_email']) . '</p>
                    <p>' . htmlspecialchars($store_info['store_address']) . ', ' . htmlspecialchars($store_info['store_city']) . ', ' . htmlspecialchars($store_info['store_country']) . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    // Check registration attempts
    if (!isset($_SESSION['register_attempts'])) {
        $_SESSION['register_attempts'] = 0;
    }

    if ($_SESSION['register_attempts'] >= 5) {
        die("Too many registration attempts. Please try again later.");
    }

    $username = $_POST['username'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $city = $_POST['city'] ?? '';
    $thecountry = $_POST['country'] ?? '';
    $birth_day = $_POST['birth_day'] ?? '';
    $birth_month = $_POST['birth_month'] ?? '';
    $birth_year = $_POST['birth_year'] ?? '';
    
    // Combine birth date parts into a single date string
    $birth_date = '';
    if (!empty($birth_year)) {
        $birth_month = !empty($birth_month) ? $birth_month : '01';
        $birth_day = !empty($birth_day) ? $birth_day : '01';
        $birth_date = $birth_year . '-' . $birth_month . '-' . $birth_day;
    }
    
    // Basic validation
    if (empty($username) || empty($email) || empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password) || empty($phone) || empty($city) || empty($thecountry)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^\+[0-9]{8,15}$/', $phone)) {
        $error = "Invalid phone number format. Please use international format starting with + (e.g., +971501234567).";
    } else {
        // Check if username, email, or phone already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR phone = ?");
        $stmt->execute([$username, $email, $phone]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_user) {
            // Check which field is duplicate
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username already taken.";
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "Email already registered.";
                } else {
                    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
                    $stmt->execute([$phone]);
                    if ($stmt->fetch()) {
                        $error = "Phone number already registered.";
                    }
                }
            }
        } else {
            // Create new user with birth date
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, birth_date) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $phone, $birth_date])) {
                $user_id = $conn->lastInsertId();

                // Insert city and alternative_phone into delivery_addresses table
                $stmt = $conn->prepare("INSERT INTO delivery_addresses (user_id, country, city, alternative_phone) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$user_id, $thecountry, $city, $phone])) {
                    // Insert user_id, phone, and email into profile table
                    $stmt = $conn->prepare("INSERT INTO profiles (user_id, username, first_name, last_name, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$user_id, $username, $first_name, $last_name, $phone, $email])) {
                        // Get store information from admin table
                        $stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
                        $stmt->execute();
                        $store_info = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // After successfully creating a new user
                        if ($store_info['auto_registration_coupon']) {
                            $code = generateCouponCode();
                            $expiry = date('Y-m-d H:i:s', strtotime('+' . $store_info['registration_coupon_expiry_days'] . ' days'));
                            
                            $stmt = $conn->prepare("INSERT INTO discount_codes 
                                                  (code_name, code, discount_percentage, expiry_date, usage_limit, type, assigned_to, user_id) 
                                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                'Welcome Coupon',
                                $code,
                                $store_info['registration_coupon_percentage'],
                                $expiry,
                                1, // Single use
                                'registration',
                                'specific_user',
                                $user_id
                            ]);
                            
                            $showcode = '<p>we are glad to welcome you with  ' . $store_info['registration_coupon_percentage'] . '% for your first order using this coupon code: "'.$code.' valid untill: '.$expiry.'"</p>';
                        } else {
                            $showcode='';
                        }


if($store_info['loyalty_program_enabled']){
$loyal='<p>Did you know we have a loyalty program? Earn points with every order and get special discount coupons!</p>
                    <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/loyaltyprogram.php" class="button">Learn About Loyalty Program</a></p>';
}
else {$loyal='';}

                        // Send welcome email
                        if ($store_info) {
                            $emailSent = sendWelcomeEmail($email, $first_name, $username, $password, $store_info, $showcode, $loyal);
                            
                            if (!$emailSent) {
                                // Log the error but don't prevent registration
                                error_log("Failed to send welcome email to: $email");
                                $_SESSION['email_error'] = "Your account was created, but we couldn't send the welcome email. Please contact support.";
                            } else {
                                $_SESSION['user_id'] = $user_id;
                                $_SESSION['user_name'] = $username;
        
                                echo '<script>
                                alert("Thank you for your registration! Welcome information has been sent to your email.");
                                window.location.href = "profile.php";
                                </script>';
                                exit();
                            }
                        }
                    } else {
                        $error = "Failed to save profile data. Please try again.";
                    }
                } else {
                    $error = "Failed to save delivery address. Please try again.";
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }

    $_SESSION['register_attempts']++;
}
ob_end_flush();
?>

<div class="container">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
<div class="form-container">
    <h2>Register</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
  
    <form method="POST" action="" class="form colored-second" id="registerForm">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <div class="row ">
    <div class="col-sm-12 col-md-6">
        
        <div class="form-group">
            <label for="username">Username*</label>
            <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
            <div id="username-feedback" class="feedback"></div>
        </div>
        <div class="form-group">
            <label for="first_name">First Name*</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name*</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            <div id="email-feedback" class="feedback"></div>
        </div>
        
        <div class="form-group">
            <label for="birthday">Birthday</label>
            <div class="row">
                <div class="col-4">
                    <select id="birth_year" name="birth_year" class="form-control">
                        <option value="">Year</option>
                        <?php 
                        $current_year = date('Y');
                        for ($year = $current_year; $year >= $current_year - 100; $year--) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-4">
                    <select id="birth_month" name="birth_month" class="form-control">
                        <option value="">Month</option>
                        <?php 
                        for ($month = 1; $month <= 12; $month++) {
                            $month_name = date('F', mktime(0, 0, 0, $month, 1));
                            echo "<option value='$month'>$month_name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-4">
                    <select id="birth_day" name="birth_day" class="form-control">
                        <option value="">Day</option>
                        <?php 
                        for ($day = 1; $day <= 31; $day++) {
                            echo "<option value='$day'>$day</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        
        </div>
     <div class="col-sm-12 col-md-6">

        <div class="form-group">
            <label for="phone">Phone*</label>
            <input type="tel" id="phone" name="phone" placeholder="Use international format starting with +" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
            <div id="phone-feedback" class="feedback"></div>
        </div>

        <div class="form-group">
            <label for="country">Country*</label>
            <select id="country" name="country" required>
                <option value="">Select Country</option>
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
            <label for="city">City*</label>
            <input type="text" id="city" name="city" value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password*</label>
            <input type="password" id="password" name="password" required placeholder="Password must be at least 8 characters long.">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password*</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        </div>
        <button type="submit" class="btn btn-primary" id="registerButton">Register</button>
    
        </div>
        
    </form>
 
    <p class="form-footer">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>
</div>

<script>
// Function to check field availability
function checkFieldAvailability(field, value, feedbackId) {
    if (value.length === 0) return;
    
    fetch('check_availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `field=${field}&value=${encodeURIComponent(value)}`
    })
    .then(response => response.json())
    .then(data => {
        const feedbackElement = document.getElementById(feedbackId);
        if (data.available) {
            feedbackElement.textContent = '';
            feedbackElement.style.color = 'green';
        } else {
            feedbackElement.textContent = `${field} is already taken`;
            feedbackElement.style.color = 'red';
            document.getElementById('registerButton').disabled = true;
        }
    })
    .catch(error => console.error('Error:', error));
}

// Add event listeners for real-time validation
document.getElementById('username').addEventListener('blur', function() {
    checkFieldAvailability('username', this.value, 'username-feedback');
});

document.getElementById('email').addEventListener('blur', function() {
    checkFieldAvailability('email', this.value, 'email-feedback');
});

document.getElementById('phone').addEventListener('blur', function() {
    checkFieldAvailability('phone', this.value, 'phone-feedback');
});

// Enable submit button when all fields are valid
document.getElementById('registerForm').addEventListener('input', function() {
    const usernameFeedback = document.getElementById('username-feedback').textContent;
    const emailFeedback = document.getElementById('email-feedback').textContent;
    const phoneFeedback = document.getElementById('phone-feedback').textContent;
    
    document.getElementById('registerButton').disabled = 
        usernameFeedback !== '' || emailFeedback !== '' || phoneFeedback !== '';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>