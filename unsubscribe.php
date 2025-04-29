<?php
require_once __DIR__ . '/db.php';

// Get the email from the query string
$email = $_GET['email'] ?? '';

// Set content type to HTML since we'll output a full page
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .confirmation-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .emoji {
            font-size: 50px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #0d6efd;
            padding: 10px 25px;
            border-radius: 30px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="emoji">👋</div>
        <h2>We're sorry to see you go</h2>
        <div id="message-container">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Processing your request...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.getElementById('message-container');
            
            <?php if (empty($email)): ?>
                // No email provided
                messageContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <h4>Oops!</h4>
                        <p>We couldn't process your unsubscribe request because no email address was provided.</p>
                        <p>Please try clicking the unsubscribe link in your email again.</p>
                        <a href="index.php" class="btn btn-primary">Return to Homepage</a>
                    </div>
                `;
            <?php else: ?>
                // Process unsubscribe request via AJAX
                fetch('unsubscribe_handler.php?email=<?= urlencode($email) ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            messageContainer.innerHTML = `
                                <div class="alert alert-success">
                                    <h4>You're unsubscribed</h4>
                                    <p>We've successfully removed <strong><?= htmlspecialchars($email) ?></strong> from our newsletter list.</p>
                                    <p>We're sad to see you go, but you're always welcome back!</p>
                                    <p>You won't receive any further promotional emails from us.</p>
                                    <a href="index.php" class="btn btn-primary">Return to Homepage</a>
                                </div>
                            `;
                        } else {
                            messageContainer.innerHTML = `
                                <div class="alert alert-warning">
                                    <h4>Something went wrong</h4>
                                    <p>We couldn't process your unsubscribe request: ${data.message || 'Unknown error'}</p>
                                    <p>Please try again later or contact our support team.</p>
                                    <a href="index.php" class="btn btn-primary">Return to Homepage</a>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        messageContainer.innerHTML = `
                            <div class="alert alert-danger">
                                <h4>Connection Error</h4>
                                <p>We encountered a problem processing your request.</p>
                                <p>Please check your internet connection and try again.</p>
                                <a href="index.php" class="btn btn-primary">Return to Homepage</a>
                            </div>
                        `;
                        console.error('Error:', error);
                    });
            <?php endif; ?>
            
            // Redirect to homepage after 10 seconds regardless
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 10000);
        });
    </script>
</body>
</html>