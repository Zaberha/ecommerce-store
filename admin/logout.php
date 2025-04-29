<?php
session_start(); // Start the session

// Destroy all session data
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the home page
header("Location: admin_login.php");
exit(); // Ensure no further code is executed
?>