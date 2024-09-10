<?php
ob_start();
session_start();
require_once('../../admin/inc/config.php');

$error_message = '';

$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $cashfree_app_id = $row['cashfree_app_id'];
    $cashfree_secret_key = $row['cashfree_secret_key'];
    $cashfree_return_url = $row['cashfree_return_url'];
}

$return_url = 'payment_success.php';
$cancel_url = 'payment.php';
$notify_url = 'payment/cashfree/verify_process.php';

$item_name = 'Product Item(s)';
$item_amount = $_POST['final_total'];
$item_number = time();

$payment_date = date('Y-m-d H:i:s');

// Check if Cashfree request or response
if (!isset($_POST["orderId"]) && !isset($_POST["orderStatus"])) {
    $request_params = array(
        "appId" => $cashfree_app_id,
        "orderId" => $item_number,
        "orderAmount" => $item_amount,
        "orderCurrency" => "INR",
        "orderNote" => $item_name,
        "customerName" => $_SESSION['customer']['cust_name'],
        "customerPhone" => $_SESSION['customer']['cust_phone'],
        "customerEmail" => $_SESSION['customer']['cust_email'],
        "returnUrl" => $cashfree_return_url,
    );

    // Generate signature using Cashfree secret key and request parameters
    $signature = generateSignature($request_params, $cashfree_secret_key);
    $request_params["signature"] = $signature;

    // Prepare the HTML form to redirect the user to the Cashfree payment page
    echo '<html><body><center><h1>Please wait! Redirecting to Cashfree...</h1></center><form method="post" action="https://test.cashfree.com/billpay/checkout/post/submit" name="cashfree_form">';
    foreach ($request_params as $key => $value) {
        echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';
    }
    echo '</form><script type="text/javascript">document.cashfree_form.submit();</script></body></html>';
    exit();
} else {
    // Response from Cashfree

    // Verify the response signature using Cashfree secret key and response parameters
    $response_signature = $_POST['signature'];
    $is_valid_signature = verifySignature($_POST, $cashfree_secret_key, $response_signature);

    if ($is_valid_signature) {
        // Check if the payment was successful
        if ($_POST['orderStatus'] == "PAID") {
            // Payment successful, process the order

            // Insert payment details into the database
            $statement = $pdo->prepare("INSERT INTO tbl_payment (
                customer_id,
                customer_name,
                customer_email,
                payment_date,
                txnid, 
                paid_amount,
                payment_method,
                payment_status,
                shipping_status,
                payment_id
            ) 
            VALUES (?,?,?,?,?,?,?,?,?,?)");
            $sql = $statement->execute(array(
                $_SESSION['customer']['cust_id'],
                $_SESSION['customer']['cust_name'],
                $_SESSION['customer']['cust_email'],
                $payment_date,
                $_POST['orderId'],
                $item_amount,
                'Cashfree',
                'Pending',
                'Pending',
                $item_number
            ));

            // Insert order details into the database
            if ($sql) {
                $i;
                // Insert order details into the database
                $i = 0;
                foreach ($_SESSION['cart_p_id'] as $key => $value) {
                    $i++;
                    $arr_cart_p_id[$i] = $value;
                }

                $i = 0;
                foreach ($_SESSION['cart_p_name'] as $key => $value) {
                    $i++;
                    $arr_cart_p_name[$i] = $value;
                }

                $i = 0;
                foreach ($_SESSION['cart_p_qty'] as $key => $value) {
                    $i++;
                    $arr_cart_p_qty[$i] = $value;
                }

                $i = 0;
                foreach ($_SESSION['cart_p_current_price'] as $key => $value) {
                    $i++;
                    $arr_cart_p_current_price[$i] = $value;
                }

                for ($i = 1; $i <= count($arr_cart_p_name); $i++) {
                    $statement = $pdo->prepare("INSERT INTO tbl_order (
                        product_id,
                        product_name,
                        quantity,
                        unit_price,
                        payment_id
                    )
                    VALUES (?,?,?,?,?)");
                    $sql = $statement->execute(array(
                        $arr_cart_p_id[$i],
                        $arr_cart_p_name[$i],
                        $arr_cart_p_qty[$i],
                        $arr_cart_p_current_price[$i],
                        $item_number
                    ));

                    // Update the stock
                    $statement = $pdo->prepare("UPDATE tbl_product SET p_qty = p_qty - ? WHERE p_id = ?");
                    $statement->execute(array($arr_cart_p_qty[$i], $arr_cart_p_id[$i]));
                }

                unset($_SESSION['cart_p_id']);
                unset($_SESSION['cart_p_qty']);
                unset($_SESSION['cart_p_current_price']);
                unset($_SESSION['cart_p_name']);
                unset($_SESSION['cart_p_featured_photo']);

                // Redirect to success page
                header('Location: payment_success.php');
                exit();
            } else {
                // Payment failed, redirect to failure page
                header('Location: payment_failure.php');
                exit();
            }
        }
    }
}

// Function to generate signature
function generateSignature($params, $secretKey)
{
    ksort($params);
    $signatureData = "";
    foreach ($params as $key => $value) {
        $signatureData .= $key . $value;
    }
    return hash_hmac('sha256', $signatureData, $secretKey);
}

// Function to verify signature
function verifySignature($params, $secretKey, $signature)
{
    $signatureData = "";
    ksort($params);
    foreach ($params as $key => $value) {
        if ($key !== 'signature') {
            $signatureData .= $key . $value;
        }
    }
    $computedSignature = hash_hmac('sha256', $signatureData, $secretKey);
    return ($computedSignature === $signature);
}
