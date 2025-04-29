<!-- Sidebar -->
<div class="sidebar d-flex flex-column" id="sidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $_SESSION['employee_role'] == 'admin' ? 'admin_dashboard.php' : 'manager.php'; ?>">
        <div class="sidebar-brand-icon">
            <i class="fas fa-lock"></i>
        </div>
        <div class="sidebar-brand-text mx-2"><?php echo $_SESSION['employee_role'] == 'admin' ? 'Admin Panel' : 'Manager Panel'; ?></div>
    </a>
    <hr class="sidebar-divider my-0">
    <div class="nav flex-column px-3">
        <?php
        // Get current employee's privileges
        $privileges = [];
        if ($_SESSION['employee_role'] != 'admin') {
            $stmt = $conn->prepare("SELECT * FROM privileges WHERE employee_id = ?");
            $stmt->execute([$_SESSION['employee_id']]);
            $privileges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        require_once __DIR__ . '/functions.php';
        
        ?>
        
        <!-- Dashboard -->
        <?php if (hasAccess('admin_dashboard', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo $_SESSION['employee_role'] == 'admin' ? 'admin_dashboard.php' : 'manager.php'; ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Catalog -->
        <?php if (hasAccess('products', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('categories', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('brands', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('suppliers', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link collapsed submenu-toggle" href="#catalogSubmenu" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-book"></i>
                <span>Catalog</span>
            </a>
            <div class="collapse submenu" id="catalogSubmenu">
                <?php if (hasAccess('products', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link active" href="products.php">
                    <i class="fas fa-boxes"></i> Products
                </a>
                <?php endif; ?>
                <?php if (hasAccess('categories', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="categories.php">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <?php endif; ?>
                <?php if (hasAccess('brands', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="brands.php">
                    <i class="fas fa-copyright"></i> Brands
                </a>
                <?php endif; ?>
                <?php if (hasAccess('suppliers', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="suppliers.php">
                    <i class="fas fa-truck"></i> Suppliers
                </a>
                <?php endif; ?>
            </div>
        </li>
        <?php endif; ?>
        
        <!-- Sales -->
        <?php if (hasAccess('orders', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('invoices', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('shipments', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('transactions', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link collapsed submenu-toggle" href="#salesSubmenu" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-shopping-cart"></i>
                <span>Sales</span>
            </a>
            <div class="collapse submenu" id="salesSubmenu">
                <?php if (hasAccess('orders', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="orders.php">Orders</a>
                <?php endif; ?>
                <?php if (hasAccess('invoices', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="invoices.php">Invoices</a>
                <?php endif; ?>
                <?php if (hasAccess('shipments', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="shipments.php">Shipments</a>
                <?php endif; ?>
                <?php if (hasAccess('transactions', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="transactions.php">Transactions</a>
                <?php endif; ?>
            </div>
        </li>
        <?php endif; ?>
        
        <!-- Customers -->
        <?php if (hasAccess('customers', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('customer_groups', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('reviews', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link collapsed submenu-toggle" href="#customersSubmenu" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-users"></i>
                <span>Customers</span>
            </a>
            <div class="collapse submenu" id="customersSubmenu">
                <?php if (hasAccess('customers', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="customers.php">Manage Customers</a>
                <?php endif; ?>
                <?php if (hasAccess('customer_groups', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="customer_groups.php">Customer Groups</a>
                <?php endif; ?>
                <?php if (hasAccess('reviews', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="reviews.php">Reviews</a>
                <?php endif; ?>
            </div>
        </li>
        <?php endif; ?>
        
        <!-- Inventory -->
        <?php if (hasAccess('inventory_dashboard', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('warehouses', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('movements', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('adjustments', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('transfers', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('inventory_alerts', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('purchase_orders', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link collapsed submenu-toggle" href="#inventorySubmenu" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-warehouse text-inventory"></i>
                <span>Inventory</span>
            </a>
            <div class="collapse submenu" id="inventorySubmenu">
            <?php if (hasAccess('inventory_dashboard', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="inventory_dashboard.php">Inventory Dashboard</a>
                <?php endif; ?>
                <?php if (hasAccess('warehouses', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="warehouses.php">Warehouses</a>
                <?php endif; ?>
                <?php if (hasAccess('movements', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="movements.php">Movements</a>
                <?php endif; ?>
                <?php if (hasAccess('adjustments', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="adjustments.php">Stock Adjustments</a>
                <?php endif; ?>
                <?php if (hasAccess('transfers', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="transfers.php">Stock Transfers</a>
                <?php endif; ?>
                <?php if (hasAccess('inventory_alerts', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="inventory_alerts.php">Inventory Alerts</a>
                <?php endif; ?>
                <?php if (hasAccess('purchase_orders', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="purchase_orders.php">Purchase Orders</a>
                <?php endif; ?>
            </div>
        </li>
        <?php endif; ?>
        
        <!-- Marketing -->
        <?php if (hasAccess('promotion', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('newrelease', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('coupons', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('email_campaigns', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('abandoned', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('loyalty', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('social_media', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link collapsed submenu-toggle" href="#marketingSubmenu" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-bullhorn text-marketing"></i>
                <span>Marketing</span>
            </a>
            <div class="collapse submenu" id="marketingSubmenu">
                <?php if (hasAccess('promotion', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="promotion.php">
                    <i class="fas fa-percentage"></i> Promotions
                </a>
                <?php endif; ?>
                <?php if (hasAccess('newrelease', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="newrelease.php">
                    <i class="fas fa-star"></i> New Release
                </a>
                <?php endif; ?>
                <?php if (hasAccess('coupons', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="coupons.php">
                    <i class="fas fa-ticket-alt"></i> Coupons
                </a>
                <?php endif; ?>
                <?php if (hasAccess('email_campaigns', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="email_campaigns.php">
                    <i class="fas fa-envelope"></i> Email Campaigns
                </a>
                <?php endif; ?>
                <?php if (hasAccess('abandoned', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="abandoned.php">
                    <i class="fas fa-shopping-cart"></i> Abandoned Cart
                </a>
                <?php endif; ?>
                <?php if (hasAccess('loyalty', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="loyalty.php">
                    <i class="fas fa-award"></i> Loyalty program
                </a>
                <?php endif; ?>
                <?php if (hasAccess('social_media', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="social_media.php">
                    <i class="fas fa-share-alt"></i> Social Media
                </a>
                <?php endif; ?>
            </div>
        </li>
        <?php endif; ?>
        
        <!-- Configure -->
        <?php if (hasAccess('settings', $privileges, $_SESSION['employee_role'] == 'admin') || 
                    hasAccess('alerts_settings', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('stores', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('themes', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('links', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('images', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('blogs', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('news', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('payment_methods', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('shipping_methods', $privileges, $_SESSION['employee_role'] == 'admin') || 
                  hasAccess('delivery-options', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link collapsed submenu-toggle" href="#configureSubmenu" data-bs-toggle="collapse">
                <i class="fas fa-fw fa-cogs"></i>
                <span>Configure</span>
            </a>
            <div class="collapse submenu" id="configureSubmenu">
                <?php if (hasAccess('settings', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <?php endif; ?>
                <?php if (hasAccess('alerts_settings', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="alerts_settings.php">
                    <i class="fas fa-store"></i> Alerts Settings
                </a>
                <?php endif; ?>
                <?php if (hasAccess('stores', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="stores.php">
                    <i class="fas fa-store"></i> Store Information
                </a>
                <?php endif; ?>
                <?php if (hasAccess('themes', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="themes.php">
                    <i class="fas fa-palette"></i> Themes
                </a>
                <?php endif; ?>
                <?php if (hasAccess('links', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="links.php">
                    <i class="fas fa-layer-group"></i> Links
                </a>
                <?php endif; ?>
                <?php if (hasAccess('blogs', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="admin_blog.php">
                    <i class="fas fa-layer-group"></i> Blogs
                </a>
                <?php endif; ?>
                <?php if (hasAccess('news', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="admin_news.php">
                    <i class="fas fa-layer-group"></i> News
                </a>
                <?php endif; ?>
                <?php if (hasAccess('images', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="images.php">
                    <i class="fas fa-image"></i> Images
                </a>
                <?php endif; ?>
                <?php if (hasAccess('payment_methods', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="payment_methods.php">
                    <i class="fas fa-credit-card"></i> Payment Methods
                </a>
                <?php endif; ?>
                <?php if (hasAccess('shipping_methods', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="shipping_methods.php">
                    <i class="fas fa-shipping-fast"></i> Shipping Methods
                </a>
                <?php endif; ?>
                <?php if (hasAccess('delivery-options', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
                <a class="nav-link" href="delivery-options.php">
                    <i class="fas fa-receipt"></i> Delivery Options
                </a>
                <?php endif; ?>
            </div>
        </li>
        <?php endif; ?>
        
        <!-- Reports -->
        <?php if (hasAccess('reports', $privileges, $_SESSION['employee_role'] == 'admin')): ?>
        <li class="nav-item">
            <a class="nav-link" href="reports.php">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Privileges (Admin only) -->
        <?php if ($_SESSION['employee_role'] == 'admin' && hasAccess('privileges', $privileges, true)): ?>
        <li class="nav-item">
            <a class="nav-link" href="privileges.php">
                <i class="fas fa-fw fa-user-shield"></i>
                <span>Employee Privileges</span>
            </a>
        </li>
        <?php endif; ?>
        
        <div class="sidebar-card d-none d-lg-flex mt-auto">
            <div class="card bg-transparent border-0 text-center">
                <div class="card-body">
                    <img class="img-fluid rounded-circle mb-3" src="images/logo.png" width="136.5px" height="52.5px">
                    <div class="mb-2 text-white">Welcome, <?php echo htmlspecialchars($_SESSION['employee_full_name']); ?></div>
                    <a class="btn btn-sm btn-outline-light" href="logout.php">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>