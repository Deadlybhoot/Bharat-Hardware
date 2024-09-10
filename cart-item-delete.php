<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_cart = $row['banner_cart'];
}
?>

<?php
$error_message = '';
if (isset($_POST['form1'])) {
    // Code to update quantities goes here
    // ...

    $allow_update = 1;
    // Check if the quantities are available and update session data
    // ...
    ?>

    <?php if ($allow_update == 0): ?>
        <script>alert('<?php echo $error_message; ?>');</script>
    <?php else: ?>
        <script>alert('All Items Quantity Update is Successful!');</script>
    <?php endif; ?>
    <?php
}

if (isset($_GET['id']) && isset($_GET['size'])) {
    $deleted = false;
    $id = $_GET['id'];
    $size = $_GET['size'];

    // Loop through the cart items and remove the specified item
    for ($i = 1; $i <= count($_SESSION['cart_p_id']); $i++) {
        if ($_SESSION['cart_p_id'][$i] == $id && $_SESSION['cart_size_id'][$i] == $size) {
            // Remove the item from the cart
            unset($_SESSION['cart_p_id'][$i]);
            unset($_SESSION['cart_size_id'][$i]);
            unset($_SESSION['cart_size_name'][$i]);
            unset($_SESSION['cart_p_qty'][$i]);
            unset($_SESSION['cart_p_current_price'][$i]);
            unset($_SESSION['cart_p_name'][$i]);
            unset($_SESSION['cart_p_featured_photo'][$i]);
            $deleted = true;
            break;
        }
    }

    if ($deleted) {
        // Reorganize the array indexes
        $_SESSION['cart_p_id'] = array_values($_SESSION['cart_p_id']);
        $_SESSION['cart_size_id'] = array_values($_SESSION['cart_size_id']);
        $_SESSION['cart_size_name'] = array_values($_SESSION['cart_size_name']);
        $_SESSION['cart_p_qty'] = array_values($_SESSION['cart_p_qty']);
        $_SESSION['cart_p_current_price'] = array_values($_SESSION['cart_p_current_price']);
        $_SESSION['cart_p_name'] = array_values($_SESSION['cart_p_name']);
        $_SESSION['cart_p_featured_photo'] = array_values($_SESSION['cart_p_featured_photo']);
    }
}

?>

<!-- Rest of the code remains the same -->
