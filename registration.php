<?php
// Include the necessary files and configurations
require_once('header.php');
require_once('vendor/autoload.php');

// Define the BASE_URL constant
// define('BASE_URL', 'http://localhost/eCommerceSite-PHP/index.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize the form input data
    $cust_name = $_POST['cust_name'];
    $cust_email = $_POST['cust_email'];
    $cust_phone = $_POST['cust_phone'];

    // Generate a verification token
    $verification_token = bin2hex(random_bytes(32));

    // Insert customer data into the database
    $statement = $pdo->prepare("INSERT INTO tbl_customer (cust_name, cust_email, cust_phone, verification_token, cust_status) VALUES (?, ?, ?, ?, 0)");
    $statement->execute([$cust_name, $cust_email, $cust_phone, $verification_token]);

    // Compose the verification email
    $to = $cust_email;
    $subject = 'Account Verification';
    $verify_link = BASE_URL . 'verify.php/token=' . $verification_token;
    $message = 'Dear ' . $cust_name . ',<br><br>';
    $message .= 'Please click on the following link to verify your account:<br>';
    $message .= '<a href="' . $verify_link . '">' . $verify_link . '</a><br><br>';
    $message .= 'Thank you for registering with us.<br>';
    $message .= 'Best regards,<br>';
    $message .= 'Bharat Hardware';

    // Send the verification email
    $mailer = new PHPMailer\PHPMailer\PHPMailer();
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com';  // Set your SMTP server
    $mailer->SMTPAuth = true;
    $mailer->Username = 'amols9517@gmail.com';  // Set your SMTP username
    $mailer->Password = 'qgazgjgavbouhaee';  // Set your SMTP password
    $mailer->SMTPSecure = 'tls';
    $mailer->Port = 587;
    $mailer->setFrom('amols9517@gmail.com', 'Bharat Hardware');
    $mailer->addAddress($to, $cust_name);
    $mailer->isHTML(true);
    $mailer->Subject = $subject;
    $mailer->Body = $message;

    if ($mailer->send()) {
        // Email sent successfully
        $success_message = 'Verification email has been sent to ' . $cust_email . '. Please check your inbox and follow the instructions to verify your account.';
    } else {
        // Failed to send email
        $error_message = 'Failed to send verification email. Please try again later.';
    }
}
?>

<!-- Registration Form -->
<div class="registration-form">
    <h2>New Customer Registration</h2>
    <?php if (isset($success_message)) : ?>
        <p class="success"><?php echo $success_message; ?></p>
    <?php endif; ?>
    <?php if (isset($error_message)) : ?>
        <p class="error"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="cust_name">Name</label>
            <input type="text" class="form-control" id="cust_name" name="cust_name" required>
        </div>
        <div class="form-group">
            <label for="cust_email">Email Address</label>
            <input type="email" class="form-control" id="cust_email" name="cust_email" required>
        </div>
        <div class="form-group">
            <label for="cust_phone">Phone Number</label>
            <input type="text" class="form-control" id="cust_phone" name="cust_phone" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php require_once('footer.php'); ?>
