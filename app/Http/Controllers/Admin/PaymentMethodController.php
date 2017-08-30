<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 30/08/2017
 * Time: 12:01
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::all();

        return View('admin.payment-method', compact('paymentMethods'));
    }

    public function create()
    {
        return View('admin.create-payment-method');
    }

    public function store()
    {
        $this->validate(request(), [
            'description' => 'required',
            'fee' => 'required'
        ]);

        PaymentMethod::create(request(['description', 'fee']));

        return redirect('/admin/paymentmethods');
    }

    public function edit($id)
    {
        $paymentMethod = PaymentMethod::find($id);
        return View('admin.edit-payment-method', compact('paymentMethod'));
    }

    public function update(Request $request, $id)
    {
        $this->validate(request(), [
            'description' => 'required',
            'fee' => 'required'
        ]);


        PaymentMethod::where('id', $id)->update([
            'description' => $request->description,
            'fee' => $request->fee
        ]);

        return redirect('admin/paymentmethods');
    }

    public function destroy($id)
    {
        PaymentMethod::destroy($id);

        return redirect('admin/paymentmethods');
    }
}