<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Privacy Policy';
$current_page = 'Privacy Policy';
require_once __DIR__ . '/includes/header.php';
?>

<main>

  <!-- Home | Parallex background -->
  <header id="title">
    <div class="bgimg-1">    
        <h1 class="outline">PRIVACY POLICY</h1>   
        
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
        <h2 class="fw-bold">PRIVACY POLICY</h2>
        <p style="text-align: justify;">
Welcome to <?php echo $admin ['store_name']; ?>. We are committed to protecting your privacy and ensuring compliance with the applicable data protection regulations, including the UAE Personal Data Protection Law (PDPL), the DIFC Data Protection Law No. 5 of 2020, and the General Data Protection Regulation (GDPR) where applicable.
</p>
       
<h3 class="pt-3">Online Store Terms</h3>
<p style="text-align: justify;">This privacy policy (“Privacy Policy”) shall form an integral part of the Terms of Use of the Website (<a href="terms.php" target="blank">“Terms”</a>), and shall be read along with the Terms. Any capitalized term not specifically defined herein shall draw its meaning from the meaning ascribed to such term in the Terms.</p>
<p style="text-align: justify;">By agreeing to these Terms and Conditions, you represent that you are at least the age of majority in your state or province of residence, or that you are the age of majority in your state or province of residence and you have given us your consent to allow any of your minor dependents to use this site. You may not use our products for any illegal or unauthorized purpose nor may you, in the use of the Service, violate any laws in your jurisdiction (including but not limited to copyright laws). You must not transmit any worms or viruses or any code of a destructive nature. A breach or violation of any of the Terms will result in an immediate termination of your Services.
</p>


<h3 class="pt-3">Information We Collect</h3>
<p style="text-align: justify;">We collect personal information such as your name, email address, phone number, billing and shipping addresses, and payment information. We may also collect technical and usage data, such as IP addresses, device information, and website interaction data.
</p>
<h3 class="pt-3">Use of Your Information</h3>
<p style="text-align: justify;">We use your information to:
<ul style="margin-left:40px;">
<li>Process and deliver your orders.</li>

<li>Provide customer support and respond to inquiries.</li>

<li>Process payments securely through trusted payment gateway operators, including Novarris Fashion Trading Private Limited, a company registered in India.
</li>
<li>Send communications related to your transactions, our products, or services.
</li>
</ul>
</p>
<h3 class="pt-3">Sharing Your Information</h3>
<p style="text-align: justify;">We may share your information with our affiliates, employees, agents, service providers, sellers, suppliers, banks, payment gateway operators, and judicial or law enforcement agencies if required by law.
</p>
<p style="text-align: justify;">We may pass your name and delivery address to third parties such as couriers or suppliers to complete your order.
</p>
<p style="text-align: justify;">All parties handling your information are contractually bound to safeguard and use your data only for authorized purposes.
</p>
<h3 class="pt-3">Your Rights</h3>
<ul style="margin-left:40px;">In line with applicable laws, you have rights to:

  <li>Access, correct, or delete your personal information.
  </li>
  <li>Object to or restrict certain processing activities.
  </li>
  <li>Request data portability.
  </li>
  <li>Withdraw your consent at any time where processing is based on consent.
  </li>
  <li>You can exercise these rights by contacting us at [Your Contact Email].
  </li>
</ul>
<h3 class="pt-3">Security of Your Information</h3>
<p style="text-align: justify;">We implement appropriate technical and organizational measures to secure your personal data against unauthorized access, misuse, or disclosure. However, no method of transmission over the internet is entirely secure.
</p>
<h3 class="pt-3">Cookies</h3>
<p style="text-align: justify;">Our Website uses cookies to enhance your user experience and collect analytics data. You can manage your cookie settings through your browser at any time.
</p>
<h3 class="pt-3">User Representations and Warranties</h3>
<p style="text-align: justify;">By using our Website, you hereby represent and warrant that:
  </p>
  <p style="text-align: justify;">All information you provide to us is true, correct, current, and updated.
  </p>
  <p style="text-align: justify;">The information you provide does not belong to any third party; if it does, you confirm that you have the necessary authorization from such third party to use, access, and disseminate such information.
  </p>
<h3 class="pt-3">Changes to This Policy</h3>
<p style="text-align: justify;">We reserve the right to modify this Privacy Policy at any time to reflect changes in our practices, services, or legal requirements. Updates will be posted on this page with an updated "Effective Date."
</p>
<h3 class="pt-3">Contact Us</h3>
For any inquiries or requests relating to your personal data or this Privacy Policy, please contact us at:
[Your Company Name]
Email: [Your Contact Email]
Phone: [Your Contact Number]
Effective Date: [Insert Date]










      </div>
    </section>
    </div> <!-- End of container -->
  </section>
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