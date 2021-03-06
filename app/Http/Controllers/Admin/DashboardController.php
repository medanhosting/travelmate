<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 30/08/2017
 * Time: 10:53
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransferConfirmation;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user_admins');
    }

    public function index(){
        $trxTotal = Transaction::where('status_id', 8)->get()->count();
        $customerTotal = User::where('status_id',1)->get()->count();

        $newOrderTotal = Transaction::where('status_id', 5)->get()->count();
        $onGoingPaymentTotal = Transaction::where('status_id', 3)
            ->orWhere('status_id',4)
            ->get()->count();
        $manualPaymentTotal = TransferConfirmation::where('status_id', 3)->get()->count();
        $challengedCcTotal = Transaction::where('status_id', 11)->get()->count();
        $deliveryReqTotal = Transaction::where('status_id', 6)->get()->count();

        $data =[
            'trxTotal'              => $trxTotal,
            'customerTotal'         => $customerTotal,
            'newOrderTotal'         => $newOrderTotal,
            'onGoingPaymentTotal'   => $onGoingPaymentTotal,
            'manualPaymentTotal'    => $manualPaymentTotal,
            'challengedCcTotal'     => $challengedCcTotal,
            'deliveryReqTotal'      => $deliveryReqTotal
        ];

        return View('admin.dashboard')->with($data);
    }
}