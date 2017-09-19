<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 18-Sep-17
 * Time: 10:30 AM
 */

namespace App\libs;

use App\Models\Cart;
use GuzzleHttp\Client;

class Midtrans
{
    public static function setRequestData($userId, $adminFee,$enabledPayments){
        //get all item from DB
        $carts = Cart::where('user_id', 'like', $userId)->get();
        $totalPrice = 0;

        //transaction_details 1
        $uniqId = uniqid();
        $transactionDetailsArr = [];
        $transactionDetailsArr = array_add($transactionDetailsArr, 'order_id', $uniqId);

        //item_details
        $itemArr = [];
        foreach($carts as $cart){
            $PriceDB = (int)$cart->getOriginal('total_price') / $cart->quantity;
            $totalPriceDB = (int)$cart->getOriginal('total_price');
            $totalPrice += $totalPriceDB;

            //set item detail
            $arrItem = [];
            $arrItem = array_add($arrItem, 'id', $cart->id);
            $arrItem = array_add($arrItem, 'price', $PriceDB);
            $arrItem = array_add($arrItem, 'quantity', $cart->quantity);
            $arrItem = array_add($arrItem, 'name', $cart->Product->name);
            array_push($itemArr, $arrItem);

            $selectedCourier = $cart->Courier->description;
            $selectedDeliveryType = $cart->DeliveryType->description;
            $ShippingPrice = (int)$cart->getOriginal('delivery_fee');

            //set order id and admin fee to cart DB
            $cart->order_id = $uniqId;
            $cart->admin_fee = $adminFee;
            $cart->payment_method = $enabledPayments == 'credit_card'?2:1;

            $cart->save();
        }
        $arrShipping = [];
        $arrShipping = array_add($arrShipping, 'id', uniqid());
        $arrShipping = array_add($arrShipping, 'price', $ShippingPrice);
        $arrShipping = array_add($arrShipping, 'quantity', 1);
        $arrShipping = array_add($arrShipping, 'name', 'Ongkos Kirim '.$selectedCourier.'-'.$selectedDeliveryType);

        array_push($itemArr, $arrShipping);

        $arrAdminFee = [];
        $arrAdminFee = array_add($arrAdminFee, 'id', uniqid());
        $arrAdminFee = array_add($arrAdminFee, 'price', $adminFee);
        $arrAdminFee = array_add($arrAdminFee, 'quantity', 1);
        $arrAdminFee = array_add($arrAdminFee, 'name', 'Biaya admin');

        array_push($itemArr, $arrAdminFee);

        $totalPrice += $ShippingPrice;
        $totalPrice += $adminFee;

        //transaction_details 2
        $transactionDetailsArr = array_add($transactionDetailsArr, 'gross_amount', $totalPrice);

        //vtweb
        $vtWebArr = [];
        $vtWebArr = array_add($vtWebArr, 'credit_card_3d_secure', true);
        // credit card = credit_card
        // bank transfer = bank_transfer
        // e-wallet =
        // direct debit = mandiri_clickpay, cimb_clicks, bri_epay, bca_klikpay

//      $vtWebArr = array_add($vtWebArr, 'enabled_payments', ['credit_card', 'mandiri_clickpay', 'cimb_clicks', 'bca_klikpay', 'bri_epay', 'echannel','permata_va','bca_va','other_va']);
        $hostUrl = env('SERVER_HOST_URL');

        $vtWebArr = array_add($vtWebArr, 'enabled_payments', [$enabledPayments]);
        $vtWebArr = array_add($vtWebArr, 'finish_redirect_url', $hostUrl. '/checkout-success/'.$userId);
        $vtWebArr = array_add($vtWebArr, 'unfinish_redirect_url', $hostUrl. '/checkout-failed');
        $vtWebArr = array_add($vtWebArr, 'error_redirect_url', $hostUrl. '/checkout-failed');


        $transactionDataArr = [];
        $transactionDataArr = array_add($transactionDataArr, 'payment_type', 'vtweb');
        $transactionDataArr = array_add($transactionDataArr, 'transaction_details', $transactionDetailsArr);
        $transactionDataArr = array_add($transactionDataArr, 'item_details', $itemArr);
        $transactionDataArr = array_add($transactionDataArr, 'vtweb', $vtWebArr);

        return $transactionDataArr;
    }

    public static function sendRequest($transactionDataArr){
        $isDevelopment = env('MIDTRANS_IS_DEVELOPMENT');

        if($isDevelopment == "true"){
            $serverKey = env('MIDTRANS_API_KEY_SANDBOX');
            $serverURL = env('MIDTRANS_API_URL_SANDBOX');
        }
        else{
            $serverKey = env('MIDTRANS_API_KEY_PRODUCTION');
            $serverURL = env('MIDTRANS_API_URL_PRODUCTION');
        }
        json_encode($transactionDataArr);
        $base64ServerKey = base64_encode($serverKey);

        $client = new Client([
            'base_uri' => $serverURL,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.$base64ServerKey
            ],
        ]);
        $request = $client->request('POST', $serverURL, [
            'json' => $transactionDataArr
        ]);

        if($request->getStatusCode() == 200){
            $collect = json_decode($request->getBody());
            $redirectUrl = $collect->redirect_url;

            return $redirectUrl;
        }

    }
}