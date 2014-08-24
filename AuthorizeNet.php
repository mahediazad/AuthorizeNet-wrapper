<?php
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'vendor/authorizenet/authorizenet/AuthorizeNet.php';

define("AUTHORIZENET_API_LOGIN_ID", "YOUR APPLICATION ID");
define("AUTHORIZENET_TRANSACTION_KEY", "YOUR TRANSACTION KEY");
define("AUTHORIZENET_SANDBOX", true);

class AuthorizeNet
{
    public function sampleTransaction()
    {

        $sale = new AuthorizeNetAIM();
        $sale->amount = "5.99";
        $sale->card_num = '6011000000000012';
        $sale->exp_date = '04/15';
        $response = $sale->authorizeAndCapture();
        if ($response->approved) {
            return $transaction_id = $response->transaction_id;
        }
    }

    public function receivePayment($order = array(), $shippingInfo = array(), $billingInfo = array(), $options = array(), $fullResponse = false)
    {
        //var_dump(class_exists('AuthorizeNetAIM')); die();
        $authorize = new AuthorizeNetAIM();

        //Add order details
        $authorize->setField('po_num', $order['id']);   //order number

        //Add Billing address
        $authorize->invoice_num = $options['invoice_prefix'].$order['id'];   //package name and id

        $authorize->amount = $order['amount'];

        //Credit card information
        $authorize->card_num = $billingInfo['card_number'];
        $authorize->exp_date = $billingInfo['card_expired'];

        //Customer Billing information
        $authorize->setField('cust_id', $billingInfo['customer_id']);
        $authorize->setField('first_name', $billingInfo['first_name']);
        $authorize->setField('last_name', $billingInfo['last_name']);
        $authorize->setField('address', $billingInfo['address']);
        $authorize->setField('city', $billingInfo['city']);
        $authorize->setField('state', $billingInfo['state']);
        $authorize->setField('zip', $billingInfo['zip']);

        //Shipping info
        $authorize->setField('ship_to_first_name', $billingInfo['first_name']);
        $authorize->setField('ship_to_last_name', $billingInfo['last_name']);
        $authorize->setField('ship_to_address', $shippingInfo['address']);
        $authorize->setField('ship_to_city', $shippingInfo['city']);
        $authorize->setField('ship_to_state', $shippingInfo['state']);
        $authorize->setField('ship_to_zip', $shippingInfo['zip']);
        $authorize->setField('ship_to_country', $shippingInfo['country']);

        // Authorize Only:
        $response['authorize']  = $authorize->authorizeOnly();

        if ($response['authorize']->approved) {
            $response['auth_code'] = $response['authorize']->transaction_id;

            // Now capture:
            $capture = new AuthorizeNetAIM;
            $response['capture_response'] = $capture->priorAuthCapture($response['auth_code']);

            // Now void:
            $void = new AuthorizeNetAIM;
            $response['void_response'] = $void->void($response['capture_response']->transaction_id);
            $response['data'] = array(
                'payment_response_code' => $response['void_response']->response_code,
                'payment_response_reason_code' => $response['void_response']->response_reason_code,
                'payment_response_reason_text' => $response['void_response']->response_reason_text,
                'payment_avs_response' => $response['void_response']->avs_response,
                'payment_authorization_code' => $response['void_response']->authorization_code,
                'payment_invoice_no' => $response['void_response']->invoice_number,
                'payment_transaction_id' => $response['void_response']->transaction_id,
                'payment_transaction_type' => $response['void_response']->transaction_type,
                'payment_account_number' => $response['void_response']->account_number,
                'payment_card_type' => $response['void_response']->card_type,
                'payment_response_text_full' => $response['void_response']->response,
                'payment_final_status' => 1,    //Payment received
                'status' => 1,  //Order activated
            );
        }else if($response['authorize']->declined){
            $response['custom'] = 'declined';
            $response['data'] = array(
                'payment_response_code' => $response['authorize']->response_code,
                'payment_response_reason_code' => $response['authorize']->response_reason_code,
                'payment_response_reason_text' => $response['authorize']->response_reason_text,
                'payment_avs_response' => $response['authorize']->avs_response,
                'payment_authorization_code' => $response['authorize']->authorization_code,
                'payment_invoice_no' => $response['authorize']->invoice_number,
                'payment_transaction_id' => $response['authorize']->transaction_id,
                'payment_transaction_type' => $response['authorize']->transaction_type,
                'payment_account_number' => $response['authorize']->account_number,
                'payment_card_type' => $response['authorize']->card_type,
                'payment_response_text_full' => $response['authorize']->response,
                'payment_final_status' => 2,    //Payment declined
                'status' => 2,      //Order status Payment declined
            );
        }else if($response['authorize']->error){
            $response['custom'] = 'error';
            $response['data'] = array(
                'payment_response_code' => $response['authorize']->response_code,
                'payment_response_reason_code' => $response['authorize']->response_reason_code,
                'payment_response_reason_text' => $response['authorize']->response_reason_text,
                'payment_avs_response' => $response['authorize']->avs_response,
                'payment_authorization_code' => $response['authorize']->authorization_code,
                'payment_invoice_no' => $response['authorize']->invoice_number,
                'payment_transaction_id' => $response['authorize']->transaction_id,
                'payment_transaction_type' => $response['authorize']->transaction_type,
                'payment_account_number' => $response['authorize']->account_number,
                'payment_card_type' => $response['authorize']->card_type,
                'payment_response_text_full' => $response['authorize']->response,
                'payment_final_status' => 3,    //Payment error
                'status' => 3,      //Order status Payment Error
            );
        }else if($response['authorize']->held){
            $response['custom'] = 'held';
            $response['data'] = array(
                'payment_response_code' => $response['authorize']->response_code,
                'payment_response_reason_code' => $response['authorize']->response_reason_code,
                'payment_response_reason_text' => $response['authorize']->response_reason_text,
                'payment_avs_response' => $response['authorize']->avs_response,
                'payment_authorization_code' => $response['authorize']->authorization_code,
                'payment_invoice_no' => $response['authorize']->invoice_number,
                'payment_transaction_id' => $response['authorize']->transaction_id,
                'payment_transaction_type' => $response['authorize']->transaction_type,
                'payment_account_number' => $response['authorize']->account_number,
                'payment_card_type' => $response['authorize']->card_type,
                'payment_response_text_full' => $response['authorize']->response,
                'payment_final_status' => 4,    //Payment held
                'status' => 4,      //Order status Payment held
            );
        }

        if($fullResponse === true)
            return $response;

        return $response['data'];
    }
}