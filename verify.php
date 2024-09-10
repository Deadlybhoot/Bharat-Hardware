<?php
require_once('config.php'); // Include your configuration file
require_once('header.php');

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Verify the email and token
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email = ? AND cust_token = ?");
    $statement->execute([$email, $token]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Email and token are valid, update customer status to active
        $updateStatement = $pdo->prepare("UPDATE tbl_customer SET cust_status = 1 WHERE cust_email = ?");
        $updateStatement->execute([$email]);

        // Redirect to success page or perform any other desired action
        header("Location: success.php");
        exit;
    } else {
        // Invalid email or token, display error message
        $error_message = 'Invalid email or token.';
    }
} else {
    // Email or token is missing, display error message
    $error_message = 'Invalid URL parameters.';
}

?>

<!-- Verification Result -->
<div class="verification-result">
    <h2>Email Verification</h2>
    <?php if (isset($error_message)) : ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>
</div>

<?php require_once('footer.php'); ?>
