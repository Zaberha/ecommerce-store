<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Return & Refund policy';
$current_page = 'Return & Refund';
require_once __DIR__ . '/includes/header.php';
?>

<main>

  <!-- Home | Parallex background -->
  <header id="title">
    <div class="bgimg-1">    
        <h1 class="outline">RETURN & REFUND POLICY</h1>   
        
    </div>
  </header>
  <!-- End of Home | Parallex background -->
  
  
  <!-- About -->
    <div class="container"> <!-- Start of container -->
 
    <!-- New Products Section -->
    <section class="section">
    <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
        <h2 class="fw-bold">RETURN & REFUND POLICY</h2>  
        <h3>Online Return</h3>
        <p style="text-align: justify;">
        Subject to meeting the conditions set out in this Returns and Exchanges section, we offer a “no questions asked" free-returns policy which allows you to return delivered items to us for any reason up to 7 days after the delivery of your Order, free of charge.
        In order to qualify for a refund, all items (including promotional gift items accompanying the Order) must be returned to us within 7 days of Order receipt with the following conditions:
        </p>
        <ul style="margin-left:40px;">
          <li>
          Items must be unaltered, unused and in full sellable condition (or the condition in which they were received from us or our agents).
          </li>
          <li>
          Items must be in their original packaging/box/dustcover and with all brand and product labels/tags/instructions still attached. Authenticity cards, where provided, should also be returned.
          </li>
          <li>
          The return must be accompanied by the original Order confirmation.
          </li>
        </ul>
<h3 class="pt-3">Returns Process</h3>
<p style="text-align: justify;">You should submit a return request and our executives will contact within 48 hours - 2 working dayas. you may submit the return request on <a href="return.php">Here.</a>


</p>
<h3 class="pt-3">Refund Process</h3>
<p style="text-align: justify;">Refunds will only be processed after completing the Return Process and the item/s returned have been approved. After approval, we will issue a refund of the full-face value of undamaged items duly returned your refund will be processed via the following methods:
</p>
<ul style="margin-left:40px;">
          <li>
          Credit Card payments are refunded back to the card used in the purchase within 14-21 working days.          </li>
          <li>
          Cash on Delivery payments are refunded via local wire transfer since cash on delivery is available locally only.          </li>

        </ul>
<h3 class="pt-3">Item Return Policies</h3>
<p style="text-align: justify;">

Damaged Goods and Incorrectly-Fulfilled Orders: If you receive an item that is damaged or not the product you ordered, please arrange for return of the item to us using the Returns Process above. The item must be returned in the same condition you received it in within 7 days of receipt to qualify for a full refund. Replacements may be available depending on stock. If an item has a manufacturing defect, it may also benefit from a manufacturer’s defects warranty. If you believe your item is defective, please call us on our <?PHP ECHO $admin ['store_country']; ?>  number at <?PHP ECHO $admin ['store_phone']; ?> 
</p>
<h3 class="pt-3">Pakaging</h3>
<p style="text-align: justify;">Please take care to preserve the condition of any product packaging as, for example, damaged Perfume boxes may prevent re-sale and may mean that we cannot give you a full refund. Our agents may ask to inspect returned items at the point of collection but that initial inspection does not constitute a guarantee of your eligibility for a full refund.
</p>
<h3 class="pt-3">Exchanges</h3>
<p style="text-align: justify;">
We are not currently able to offer Exchanges. Instead, all items should follow the returns process, and a new Order placed for the replacement items.
We pride ourselves on the highest quality, luxury product at <?PHP ECHO $admin ['store_name']; ?> Store. So, if your product is damaged or has a fault, we want to know about it. Please contact our customer care team from 10 AM – 6 PM on our support line number <?PHP ECHO $admin ['store_phone']; ?> or email: <?PHP ECHO $admin ['store_email']; ?>
. We reserve the right to monitor returns and to refuse Orders from customers with excessive returns levels. However, nothing in this Returns section is intended to affect any consumer rights that you may have under <?PHP ECHO $admin ['store_country']; ?> law.
</p>

      </div>
    </section>
    </div> <!-- End of container -->

  <!-- End of About -->
  
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