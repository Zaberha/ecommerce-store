<?php
// Enable error reporting for debugging
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Composer autoloader
require __DIR__ . 
"/vendor/autoload.php";

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Set headers right after session start
header("Content-Type: application/json");
$host = "localhost";
$dbname = "u547298449_ecommerce";
$username = "root";
$password = "";

// --- Database Connection (Keep your existing connection logic) ---
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}
// --- End Database Connection ---

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $raw_data = file_get_contents("php://input");
        if (!$raw_data) {
            throw new Exception("No input data received.");
        }

        $data = json_decode($raw_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON data.");
        }

        // Extract data
        $cartData = $data["cart"] ?? [];
        if (empty($cartData)) {
            throw new Exception("Your cart is empty.");
        }

        $totalAmount = $data["total_amount"] ?? 0;
        $paymentMethod = $data["payment_method"] ?? "";
        $deliveryCharges = $data["delivery_charges"] ?? 0;
        $taxAmount = $data["tax_amount"] ?? 0;
        $discountByCode = $data["coupon_discount"] ?? 0;
        $discount = $data["product_discount"] ?? 0;
        $grandTotal = $data["grand_total"] ?? 0;
        $couponCode = $data["coupon_code"] ?? null;
        $couponId = $data["coupon_id"] ?? null;
        $userId = $_SESSION["user_id"]; // Get user ID from session

        if ($paymentMethod == "cash on delivery") {
            $paymentStatus = "on_delivery";
        } else {
            // For other methods, initial status might be pending until confirmed
            $paymentStatus = "pending";
        }

        // Begin transaction
        $conn->beginTransaction();

        // Insert the order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, total_amount, discount, payment_method, order_status, 
                delivery_charges, tax_amount, discount_by_code, grand_total,
                discount_code, discount_code_id, payment_status
            ) VALUES (
                :user_id, :total_amount, :discount, :payment_method, :order_status, 
                :delivery_charges, :tax_amount, :discount_by_code, :grand_total,
                :discount_code, :discount_code_id, :payment_status
            )
        ");
        $stmt->execute([
            ":user_id" => $userId,
            ":total_amount" => $totalAmount,
            ":discount" => $discount,
            ":payment_method" => $paymentMethod,
            ":order_status" => "pending", // Initial status is pending
            ":delivery_charges" => $deliveryCharges,
            ":tax_amount" => $taxAmount,
            ":discount_by_code" => $discountByCode,
            ":grand_total" => $grandTotal,
            ":discount_code" => $couponCode,
            ":discount_code_id" => $couponId,
            ":payment_status" => $paymentStatus,
        ]);
        $orderId = $conn->lastInsertId();

        // Insert order items and update stock (Keep your existing logic)
        foreach ($cartData as $item) {
            $productId = $item["id"];
            $quantity = $item["quantity"];
            $price = $item["price"];

            // Check stock
            $stmt = $conn->prepare("SELECT stock_limit FROM products WHERE id = :id FOR UPDATE");
            $stmt->execute([":id" => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product with ID $productId not found.");
            }

            // Use stock_limit if available, otherwise handle appropriately
            $currentStock = $product["stock_limit"] ?? 0; // Default to 0 if null

            if ($currentStock < $quantity) {
                throw new Exception("Insufficient stock for product ID $productId.");
            }

            // Insert order item
            $stmt = $conn->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantity, price
                ) VALUES (
                    :order_id, :product_id, :quantity, :price
                )
            ");
            $stmt->execute([
                ":order_id" => $orderId,
                ":product_id" => $productId,
                ":quantity" => $quantity,
                ":price" => $price,
            ]);

            // Update stock
            $newStock = $currentStock - $quantity;
            $stmt = $conn->prepare("
                UPDATE products SET stock_limit = :new_stock WHERE id = :id
            ");
            $stmt->execute([
                ":new_stock" => $newStock,
                ":id" => $productId,
            ]);
        }

        // Insert delivery address (Keep your existing logic)
        $stmt = $conn->prepare("
            SELECT * FROM delivery_addresses WHERE user_id = :user_id
        ");
        $stmt->execute([":user_id" => $userId]);
        $deliveryAddress = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($deliveryAddress) {
            $stmt = $conn->prepare("
                INSERT INTO actual_addresses (
                    user_id, country, city, street, building_name, 
                    building_number, floor_number, flat_number, alternative_phone, order_id
                ) VALUES (
                    :user_id, :country, :city, :street, :building_name, 
                    :building_number, :floor_number, :flat_number, :alternative_phone, :order_id
                )
            ");
            $stmt->execute([
                ":user_id" => $userId,
                ":country" => $deliveryAddress["country"],
                ":city" => $deliveryAddress["city"],
                ":street" => $deliveryAddress["street"],
                ":building_name" => $deliveryAddress["building_name"],
                ":building_number" => $deliveryAddress["building_number"],
                ":floor_number" => $deliveryAddress["floor_number"],
                ":flat_number" => $deliveryAddress["flat_number"],
                ":alternative_phone" => $deliveryAddress["alternative_phone"],
                ":order_id" => $orderId,
            ]);
        }

        // Update Coupon status (Keep your existing logic)
        if ($couponCode) {
            $stmt = $conn->prepare("UPDATE discount_codes SET active_flag = 0 WHERE code = :code");
            $stmt->bindParam(":code", $couponCode, PDO::PARAM_STR);
            $stmt->execute();
        }

        // Update points (Keep your existing logic)
        $amount_spent = $totalAmount - $discount;
        $stmt = $conn->prepare("SELECT points FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            $new_points = $user["points"] + $amount_spent;
            $update = $conn->prepare("UPDATE users SET points = ? WHERE id = ?");
            $update->execute([$new_points, $userId]);
        }

        // --- PDF Invoice Generation (Keep your existing TCPDF logic) ---
        $pdfGenerated = false;
        $pdfFilePath = "";
        if (file_exists(__DIR__ . "/tcpdf/tcpdf.php")) {
            require_once __DIR__ . "/tcpdf/tcpdf.php";

            // Get customer details
            $stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$customer) {
                throw new Exception("Customer profile not found.");
            }

            // Get delivery address for PDF (use actual_addresses for this order)
            $stmt = $conn->prepare("SELECT * FROM actual_addresses WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $address = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get store information
            $stmt = $conn->prepare("SELECT store_name, store_email, store_phone, store_city, store_country, business_logo FROM admin LIMIT 1");
            $stmt->execute();
            $store = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$store) {
                throw new Exception("Store information not found.");
            }

            // Get product names for invoice
            $productIds = array_column($cartData, "id");
            if (!empty($productIds)) {
                $placeholders = implode(",", array_fill(0, count($productIds), "?"));
                $stmt = $conn->prepare("SELECT id, name FROM products WHERE id IN ($placeholders)");
                $stmt->execute($productIds);
                $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            } else {
                $products = [];
            }

            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);

            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($store["store_name"]);
            $pdf->SetTitle("Invoice #" . $orderId);
            $pdf->SetSubject("Invoice");
            $pdf->SetKeywords("Invoice, Order");

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Add a page
            $pdf->AddPage();

            // Add business logo if exists
            $logoPath = __DIR__ . "/" . ($store["business_logo"] ?? ""); // Assuming logo path is relative to script dir
            if (!empty($store["business_logo"]) && file_exists($logoPath)) {
                $pdf->Image($logoPath, 15, 15, 40, 0, "", "", "T", false, 300, "", false, false, 0, false, false, false);
            } else {
                // Optional: Placeholder or just move store info up if no logo
                $pdf->SetY(15);
            }

            // Store information
            $pdf->SetY(15); // Adjust Y position as needed
            $pdf->SetFont("helvetica", "B", 12);
            $pdf->Cell(0, 10, $store["store_name"], 0, 1, "R");
            $pdf->SetFont("helvetica", "", 10);
            $pdf->Cell(0, 5, $store["store_email"], 0, 1, "R");
            $pdf->Cell(0, 5, $store["store_phone"], 0, 1, "R");
            $pdf->Cell(0, 5, $store["store_city"] . ", " . $store["store_country"], 0, 1, "R");

            // Invoice title
            $pdf->SetY(50); // Adjust Y position
            $pdf->SetFont("helvetica", "B", 16);
            $pdf->Cell(0, 10, "INVOICE", 0, 1, "C");

            // Invoice details
            $pdf->SetFont("helvetica", "", 10);
            $pdf->Cell(0, 5, "Invoice #: " . $orderId, 0, 1, "L");
            $pdf->Cell(0, 5, "Date: " . date("F j, Y"), 0, 1, "L");
            $pdf->Ln(5);

            // Customer information
            $pdf->SetFont("helvetica", "B", 10);
            $pdf->Cell(0, 7, "Bill To:", 0, 1);
            $pdf->SetFont("helvetica", "", 10);
            $pdf->Cell(0, 5, $customer["first_name"] . " " . $customer["last_name"], 0, 1);
            $pdf->Cell(0, 5, $customer["email"], 0, 1);
            $pdf->Cell(0, 5, $customer["phone"], 0, 1);

            if ($address) {
                $pdf->Cell(0, 5, $address["building_name"] . ", " . $address["street"], 0, 1);
                $pdf->Cell(0, 5, $address["city"] . ", " . $address["country"], 0, 1);
            }
            $pdf->Ln(10);

            // Order items table header
            $pdf->SetFillColor(224, 224, 224);
            $pdf->SetFont("helvetica", "B", 10);
            $pdf->Cell(95, 7, "Product", 1, 0, "L", 1);
            $pdf->Cell(30, 7, "Price", 1, 0, "R", 1);
            $pdf->Cell(25, 7, "Quantity", 1, 0, "R", 1);
            $pdf->Cell(30, 7, "Subtotal", 1, 1, "R", 1);

            // Table rows
            $pdf->SetFont("helvetica", "", 9);
            $fill = 0;
            foreach ($cartData as $item) {
                $productName = $products[$item["id"]] ?? "Product ID: " . $item["id"];
                $pdf->Cell(95, 6, $productName, "LR", 0, "L", $fill);
                $pdf->Cell(30, 6, number_format($item["price"], 2), "LR", 0, "R", $fill);
                $pdf->Cell(25, 6, $item["quantity"], "LR", 0, "R", $fill);
                $pdf->Cell(30, 6, number_format($item["price"] * $item["quantity"], 2), "LR", 1, "R", $fill);
                $fill = !$fill;
            }
            $pdf->Cell(180, 0, "", "T"); // Closing line for table
            $pdf->Ln(1);

            // Order summary
            $summaryX = 120; // X position for summary labels
            $valueX = 150; // X position for summary values
            $pdf->SetFont("helvetica", "", 10);
            $pdf->SetX($summaryX);
            $pdf->Cell(30, 6, "Subtotal:", 0, 0, "R");
            $pdf->Cell(30, 6, number_format($totalAmount, 2), 0, 1, "R");

            if ($discount > 0) {
                $pdf->SetX($summaryX);
                $pdf->Cell(30, 6, "Product Discount:", 0, 0, "R");
                $pdf->Cell(30, 6, "-" . number_format($discount, 2), 0, 1, "R");
            }
            if (!empty($couponCode)) {
                $pdf->SetX($summaryX);
                $pdf->Cell(30, 6, "Coupon Discount:", 0, 0, "R");
                $pdf->Cell(30, 6, "-" . number_format($discountByCode, 2), 0, 1, "R");
            }

            $pdf->SetX($summaryX);
            $pdf->Cell(30, 6, "Tax:", 0, 0, "R");
            $pdf->Cell(30, 6, number_format($taxAmount, 2), 0, 1, "R");

            $pdf->SetX($summaryX);
            $pdf->Cell(30, 6, "Delivery:", 0, 0, "R");
            $pdf->Cell(30, 6, number_format($deliveryCharges, 2), 0, 1, "R");

            $pdf->SetX($summaryX - 10); // Line above total
            $pdf->Cell(70, 0, "", "T");
            $pdf->Ln(1);

            $pdf->SetFont("helvetica", "B", 11);
            $pdf->SetX($summaryX);
            $pdf->Cell(30, 7, "Total:", 0, 0, "R");
            $pdf->Cell(30, 7, number_format($grandTotal, 2), 0, 1, "R");

            // Thank you message
            $pdf->SetY($pdf->GetY() + 10);
            $pdf->SetFont("helvetica", "I", 10);
            $pdf->Cell(0, 5, "Thank you for your order!", 0, 1, "C");

            // Save PDF file
            $invoiceDir = __DIR__ . "/invoices";
            if (!file_exists($invoiceDir)) {
                mkdir($invoiceDir, 0755, true);
            }
            $pdfFilePath = $invoiceDir . "/" . $orderId . ".pdf";
            $pdf->Output($pdfFilePath, "F"); // F saves to file
            $pdfGenerated = true;
        }
        // --- End PDF Invoice Generation ---

        // Commit transaction BEFORE sending email
        $conn->commit();

        // --- Send Confirmation Email with PHPMailer ---
        $emailSent = false;
        $emailError = "";
        // Send email only if PDF was generated and customer email exists
        if ($pdfGenerated && !empty($customer["email"])) {
            $mail = new PHPMailer(true); // Enable exceptions
            try {
                // --- SMTP Configuration --- 
                // IMPORTANT: Replace with your actual SMTP settings
                // Consider using a config file or environment variables for security
                $mail->isSMTP();
                $mail->Host = "smtp.example.com"; // Your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = "your_smtp_username@example.com"; // SMTP username
                $mail->Password = "your_smtp_password"; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                $mail->Port = 587; // TCP port to connect to; use 465 for `PHPMailer::ENCRYPTION_SMTPS` (SSL)
                // --- End SMTP Configuration ---

                //Recipients
                $mail->setFrom($store["store_email"], $store["store_name"]); // Sender Email and Name
                $mail->addAddress($customer["email"], $customer["first_name"] . " " . $customer["last_name"]); // Recipient Email and Name
                // $mail->addReplyTo("info@example.com", "Information");
                // $mail->addCC("cc@example.com");
                // $mail->addBCC("bcc@example.com");

                //Attachments
                $mail->addAttachment($pdfFilePath); // Add the generated invoice

                //Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = "Your Order Confirmation - Order #" . $orderId;
                
                // Simple HTML Body (You can create a more elaborate HTML template)
                $mail->Body = "<p>Dear " . htmlspecialchars($customer["first_name"]) . ",</p>"
                            . "<p>Thank you for your order with " . htmlspecialchars($store["store_name"]) . "!</p>"
                            . "<p>Your Order ID is: <strong>" . $orderId . "</strong></p>"
                            . "<p>Your order details are attached as a PDF invoice.</p>"
                            . ($paymentMethod == "cash on delivery" ? "<p>Your order will be delivered soon. Please prepare the payment amount of " . number_format($grandTotal, 2) . ".</p>" : "<p>We will process your order shortly.</p>")
                            . "<p>You can view your order details online here: [Link to order details page]</p>" // TODO: Add actual link
                            . "<p>Thank you for shopping with us!</p>"
                            . "<p>Best regards,<br>" . htmlspecialchars($store["store_name"]) . "</p>";
                
                // Simple Text Body for non-HTML mail clients
                $mail->AltBody = "Dear " . $customer["first_name"] . ",\n\n"
                             . "Thank you for your order with " . $store["store_name"] . "!\n"
                             . "Your Order ID is: " . $orderId . "\n"
                             . "Your order details are attached as a PDF invoice.\n"
                             . ($paymentMethod == "cash on delivery" ? "Your order will be delivered soon. Please prepare the payment amount of " . number_format($grandTotal, 2) . ".\n" : "We will process your order shortly.\n")
                             . "You can view your order details online.\n\n" // TODO: Add actual link
                             . "Thank you for shopping with us!\n\n"
                             . "Best regards,\n" . $store["store_name"];

                $mail->send();
                $emailSent = true;
            } catch (Exception $e) {
                // Log the error, don't expose detailed errors to the user
                error_log("PHPMailer Error: {" . $mail->ErrorInfo . "}");
                $emailError = "Confirmation email could not be sent. Please contact support if you don't receive it shortly."; // Generic error for user
            }
        }
        // --- End Send Confirmation Email ---

        // Clear cart from localStorage by setting session variable
        $_SESSION["clear_cart"] = true;
        $_SESSION["order_placed"] = true; // Used by confirmation pages

        // Return success, including email status
        echo json_encode([
            "success" => true,
            "message" => "Order placed successfully!" . ($emailSent ? "" : " " . $emailError),
            "order_id" => $orderId,
            "pdf_generated" => $pdfGenerated,
            "email_sent" => $emailSent,
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage(),
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method.",
    ]);
}

