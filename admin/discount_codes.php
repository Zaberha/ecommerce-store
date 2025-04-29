<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
$page_title = 'Manage Discount Codes';
$current_page = 'discount_codes';
require_once __DIR__ . '/includes/header.php';


// Handle form submission for adding/editing discount codes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_discount_code'])) {
        // Add new discount code
        $code_name = $_POST['code_name'];
        $code = $_POST['code'];
        $discount_percentage = $_POST['discount_percentage'];
        $type = $_POST['type'];

        $stmt = $conn->prepare("
            INSERT INTO discount_codes (code_name, code, discount_percentage, type)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$code_name, $code, $discount_percentage, $type]);

        $_SESSION['success'] = 'Discount code added successfully!';
        header("Location: discount_codes.php");
        exit();
    } elseif (isset($_POST['update_discount_code'])) {
        // Update existing discount code
        $id = $_POST['id'];
        $code_name = $_POST['code_name'];
        $code = $_POST['code'];
        $discount_percentage = $_POST['discount_percentage'];
        $type = $_POST['type'];
        $active_flag = isset($_POST['active_flag']) ? 1 : 0;

        $stmt = $conn->prepare("
            UPDATE discount_codes 
            SET code_name = ?, code = ?, discount_percentage = ?, type = ?, active_flag = ?
            WHERE id = ?
        ");
        $stmt->execute([$code_name, $code, $discount_percentage, $type, $active_flag, $id]);

        $_SESSION['success'] = 'Discount code updated successfully!';
        header("Location: discount_codes.php");
        exit();
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM discount_codes WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = 'Discount code deleted successfully!';
    header("Location: discount_codes.php");
    exit();
}

// Fetch all discount codes from the database
$stmt = $conn->prepare("SELECT * FROM discount_codes");
$stmt->execute();
$discount_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_end_flush();
?>

<style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        color: #333;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    h1 {
        color: #333;
    }

    .section {
        margin-bottom: 30px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #fff;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-group button {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .form-group button:hover {
        background-color: #0056b3;
    }

    .error {
        color: red;
        margin-bottom: 15px;
    }

    .success {
        color: green;
        margin-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th, td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f4f4f4;
    }

    .actions {
        display: flex;
        gap: 10px;
    }

    .actions a {
        text-decoration: none;
        color: #007bff;
    }

    .actions a:hover {
        text-decoration: underline;
    }
</style>

<div class="main-content">
    <!-- Header -->
    <div class="header">
        <h1>Manage Discount Codes</h1>
    </div>

    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Section 1: Add/Edit Discount Code Form -->
    <div class="section">
        <h2><?php echo isset($_GET['edit']) ? 'Edit Discount Code' : 'Add Discount Code'; ?></h2>
        <form method="POST" action="">
            <?php if (isset($_GET['edit'])): ?>
                <?php
                $id = $_GET['edit'];
                $stmt = $conn->prepare("SELECT * FROM discount_codes WHERE id = ?");
                $stmt->execute([$id]);
                $discount_code = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <input type="hidden" name="id" value="<?php echo $discount_code['id']; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="code_name">Code Name</label>
                <input type="text" name="code_name" value="<?php echo $discount_code['code_name'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" value="<?php echo $discount_code['code'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="discount_percentage">Discount Percentage</label>
                <input type="number" step="0.01" name="discount_percentage" value="<?php echo $discount_code['discount_percentage'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" required>
                    <option value="loyalty" <?php echo (isset($discount_code['type']) && $discount_code['type'] === 'loyalty') ? 'selected' : ''; ?>>Loyalty</option>
                    <option value="offer" <?php echo (isset($discount_code['type']) && $discount_code['type'] === 'offer') ? 'selected' : ''; ?>>Offer</option>
                </select>
            </div>
            <?php if (isset($_GET['edit'])): ?>
                <div class="form-group">
                    <label for="active_flag">Active</label>
                    <input type="checkbox" name="active_flag" <?php echo (isset($discount_code['active_flag']) && $discount_code['active_flag'] == 1) ? 'checked' : ''; ?>>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <button type="submit" name="<?php echo isset($_GET['edit']) ? 'update_discount_code' : 'add_discount_code'; ?>">
                    <?php echo isset($_GET['edit']) ? 'Update Discount Code' : 'Add Discount Code'; ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: List of Discount Codes -->
    <div class="section">
        <h2>Discount Codes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code Name</th>
                    <th>Code</th>
                    <th>Discount Percentage</th>
                    <th>Type</th>
                    <th>Active</th>
                    <th>Created At</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($discount_codes as $discount_code): ?>
                    <tr>
                        <td><?php echo $discount_code['id']; ?></td>
                        <td><?php echo $discount_code['code_name']; ?></td>
                        <td><?php echo $discount_code['code']; ?></td>
                        <td><?php echo $discount_code['discount_percentage']; ?>%</td>
                        <td><?php echo $discount_code['type']; ?></td>
                        <td><?php echo $discount_code['active_flag'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $discount_code['created_at']; ?></td>
                        <td><?php echo $discount_code['expiry_date']; ?></td>
                        <td class="actions">
                            <a href="discount_codes.php?edit=<?php echo $discount_code['id']; ?>">Edit</a>
                            <a href="discount_codes.php?delete=<?php echo $discount_code['id']; ?>" onclick="return confirm('Are you sure you want to delete this discount code?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>