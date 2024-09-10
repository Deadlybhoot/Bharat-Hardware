<?php
require_once "../../admin/inc/config.php";

$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// Read the post from Cashfree system and add 'cf-ipn-checksum' header
$req = 'cf-ipn-checksum=' . urlencode($_SERVER['HTTP_CF_IPN_SIGNATURE']);
foreach ($myPost as $key => $value) {
    $req .= "&$key=" . urlencode($value);
}

/*
 * Post IPN data back to Cashfree to validate the IPN data is genuine
 * Without this step, anyone can fake IPN data
 */
$cfURL = "https://www.cashfree.com/merchant/pg/partialpayment/v1/validate";
$ch = curl_init($cfURL);
if ($ch == FALSE) {
    return FALSE;
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

// Set TCP timeout to 30 seconds
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name'));
$res = curl_exec($ch);

/*
 * Inspect IPN validation result and act accordingly
 * Check the response status
 */
if ($res === 'VERIFIED') {

    $statement = $pdo->prepare("UPDATE tbl_payment SET 
                        txnid=?, 
                        payment_status=?
                        WHERE payment_id=?");
    $sql = $statement->execute(array(
        $_POST['txnid'],
        $_POST['payment_status'],
        $_POST['item_number']
    ));

} else {
    $statement = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
    $sql = $statement->execute(array($_POST['item_number']));
}
?>
