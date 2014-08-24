<?php
/**
 * Created by Mahedi Azad.
 * User: micro
 * Date: 8/24/14
 * Time: 11:06 PM
 */

require_once "vendor/autoload.php";
//Use
//Create customer porfile
$profile = array(
    'description' => 'Customer profile',
    'email' => 'mahediazad@gmail.com'
);

$authorize = new AuthorizeNet();
$customerProfileID = $authorize->createCustomerProfileCIM($profile);
$authorize->updateCustomerProfileCIM('27478043', $profile);
echo 'Profile: '.$customerProfileID;

//Create customer payment profile
$card = array(
    'cardNumber' => '370000000000002',
    'exp_date' => '2015-10',
    'cardCode' => '123'
);
$customerPaymentID = $authorize->createCustomerPaymentProfileCIM($customerProfileID, $card);
$authorize->updateCustomerPaymentProfileCIM('27478043', '25061851', $card);
echo 'Payment: '.$customerPaymentID;

//Create customer shipping profile
$shippingProfile = array(
    'firstName' =>'Mahedi',
    'lastName' => 'Azad',
    'companyName' => 'Preview ICT',
    'address' => 'Green Road',
    'city' => 'Dhaka',
    'state' => '',
    'zip' => '1207',
    'country' => 'Bangladesh',
    'phoneNumber' => '123456',
    'faxNumber' => ''

);
$shippingId = $authorize->createCustomerShippingAddressCIM($customerProfileID, $shippingProfile);
$authorize->updateCustomerShippingAddressCIM('7478043', '25704977', $shippingProfile);
echo 'Shipping: '.$shippingId;


//Make a transaction with credit card and products information
$product = array(
    array(
        'id'         => '123',
        'name'       => 'Facebook status robot',
        'description'=> 'Post your wall in your given time',
        'quantity'   => '1',
        'unitPrice'  => '120',
        'taxable'    => 'true'

    )
);

$test = $authorize->createAuthCaptureTransactionCIM($product, '27499614','25081962','25725671');
var_dump($test);

//Email sending
//                        $messageSubject = 'This is a test message';
//                        $messageBody = 'This is the message body';
//                        $messageContent = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/email-template?messageSubject='.$messageSubject.'&messageBody='.$message);
//                        $email = new Email();
//                        $email->sendMail($messageSubject, 'mahedi2014@gmail.com', $messageContent);