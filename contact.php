<?php
session_start();
include 'db.php';
// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$name = $email = $phone = $subject = $message = $message_type = '';
$errors = [];
$success = false;

// Get store email from admin table
$stmt = $conn->prepare("SELECT store_email FROM admin LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$store_email = $admin['store_email'] ?? 'store@example.com';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token. Please try again.";
    } else {
        // Sanitize inputs
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
        $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
        $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));
        $message_type = trim(filter_input(INPUT_POST, 'message_type', FILTER_SANITIZE_STRING));

        // Validate inputs
        if (empty($name)) {
            $errors[] = "Name is required.";
        } elseif (strlen($name) > 100) {
            $errors[] = "Name must be less than 100 characters.";
        }

        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if (!empty($phone) && !preg_match('/^[\d\s\-+()]{10,20}$/', $phone)) {
            $errors[] = "Invalid phone number format.";
        }

        if (empty($subject)) {
            $errors[] = "Subject is required.";
        } elseif (strlen($subject) > 200) {
            $errors[] = "Subject must be less than 200 characters.";
        }

        if (empty($message)) {
            $errors[] = "Message is required.";
        } elseif (strlen($message) > 2000) {
            $errors[] = "Message must be less than 2000 characters.";
        }

        if (empty($message_type)) {
            $errors[] = "Message type is required.";
        }

        // If no errors, send email
        if (empty($errors)) {
            // Prepare email headers
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            // Prepare email content
            $email_content = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #f8f9fa; padding: 15px; text-align: center; }
                        .content { padding: 20px; background-color: #fff; border: 1px solid #ddd; }
                        .footer { margin-top: 20px; padding: 10px; text-align: center; font-size: 12px; color: #777; }
                        .label { font-weight: bold; color: #555; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>New Contact Form Submission</h2>
                            <p>Message Type: $message_type</p>
                        </div>
                        <div class='content'>
                            <p><span class='label'>Name:</span> $name</p>
                            <p><span class='label'>Email:</span> $email</p>
                            <p><span class='label'>Phone:</span> " . ($phone ? $phone : 'Not provided') . "</p>
                            <p><span class='label'>Subject:</span> $subject</p>
                            <p><span class='label'>Message:</span></p>
                            <p>" . nl2br(htmlspecialchars($message)) . "</p>
                        </div>
                        <div class='footer'>
                            <p>This message was sent from the contact form on your website.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Send email
            $mail_sent = mail($store_email, "[$message_type] $subject", $email_content, $headers);

            if ($mail_sent) {
                $success = true;
                // Clear form fields
                $name = $email = $phone = $subject = $message = $message_type = '';
            } else {
                $errors[] = "Failed to send message. Please try again later.";
            }
        }
    }
}
$page_title = 'Contact us';
$current_page = 'contact';
require_once 'includes/header.php';

?>

<div class="container py-5">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
    


<h2 class="fw-bold">Drop a Message</h2>

<div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0">
               
                <div class="card-body p-4 p-md-5">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="text-center">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                <h3 class="alert-heading">Thank you for your message!</h3>
                                <p>We shall contact you soon!</p>
                                <button type="button" class="btn btn-success mt-3" onclick="window.location.href='contact.php'">
                                    Send Another Message
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h5 class="alert-heading">Please fix the following errors:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="contactForm">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" 
                                               value="<?= htmlspecialchars($name) ?>" required>
                                        <label for="name">Your Name *</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Your Email" value="<?= htmlspecialchars($email) ?>" required>
                                        <label for="email">Your Email *</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="Your Phone Number" value="<?= htmlspecialchars($phone) ?>">
                                    <label for="phone">Your Phone Number (optional)</label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-floating">
                                    <select class="form-select" id="message_type" name="message_type" required>
                                        <option value="" disabled selected>Select message type</option>
                                        <option value="General inquiry" <?= $message_type === 'General inquiry' ? 'selected' : '' ?>>General inquiry</option>
                                        <option value="Special Order" <?= $message_type === 'Special Order' ? 'selected' : '' ?>>Special Order</option>
                                        <option value="Delivery Issues" <?= $message_type === 'Delivery Issues' ? 'selected' : '' ?>>Delivery Issues</option>
                                        <option value="Payment Issues" <?= $message_type === 'Payment Issues' ? 'selected' : '' ?>>Payment Issues</option>
                                        <option value="Feedback and Suggestions" <?= $message_type === 'Feedback and Suggestions' ? 'selected' : '' ?>>Feedback and Suggestions</option>
                                        <option value="Complaint Submissions" <?= $message_type === 'Complaint Submissions' ? 'selected' : '' ?>>Complaint Submissions</option>
                                        <option value="Bulk Purchase or B2B Requests" <?= $message_type === 'Bulk Purchase or B2B Requests' ? 'selected' : '' ?>>Bulk Purchase or B2B Requests</option>
                                        <option value="Technical Problem Reports" <?= $message_type === 'Technical Problem Reports' ? 'selected' : '' ?>>Technical Problem Reports</option>
                                    </select>
                                    <label for="message_type">Message Type *</label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           placeholder="Subject" value="<?= htmlspecialchars($subject) ?>" required>
                                    <label for="subject">Subject *</label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-floating">
                                    <textarea class="form-control" id="message" name="message" 
                                              placeholder="Your Message" style="height: 150px" required><?= htmlspecialchars($message) ?></textarea>
                                    <label for="message">Your Message *</label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg py-3">
                                    <i class="fas fa-paper-plane me-2"></i> Send Message
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>


<style>
    .form-floating label {
        padding: 1rem 1.25rem;
    }
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label,
    .form-floating > .form-select ~ label {
        transform: scale(0.85) translateY(-0.9rem) translateX(0.15rem);
        opacity: 0.8;
    }
    .card {
        border-radius: 4px;
        overflow: hidden;
    }
    .card-header {
        padding: 1.5rem;
    }
</style>






</div>


<?php require_once 'includes/footer.php'; ?>

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