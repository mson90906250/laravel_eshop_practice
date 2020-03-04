<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Common\CustomController;

class PaymentMethodController extends CustomController
{
    /**
     * Display a listing of the resopaurce.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paymentMethodList = $this->paginate($this->search($request), 10)
                                    ->withPath(route('admin.paymentMethod.index'))
                                    ->appends($request->capture()->except('page'));

        return view('admin.payment_method.index', [
            'paymentMethodList' => $paymentMethodList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:payment_methods,id'],
            'status'    => ['required', 'integer', Rule::in(array_keys(PaymentMethod::getStatusLabelList()))]
        ]);

        PaymentMethod::whereIn('id', $validatedData['id'])
                        ->update(['status' => $validatedData['status']]);

        return redirect()->back()->with('status', '修改狀態成功');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        //
    }

    protected function search(Request $request)
    {
        $orderByList = ['name', 'status'];

        $validatedData = $request->validate([
            'name'      => ['nullable', 'string'],
            'status'    => ['nullable', 'integer', Rule::in(array_keys(PaymentMethod::getStatusLabelList()))],
            'order_by'  => ['nullable', 'string', Rule::in($orderByList)]
        ]);

        $query = PaymentMethod::query();

        foreach ($validatedData as $attribute => $value) {

            if ($value == NULL) {

                continue;

            }

            switch ($attribute) {

                case 'name':

                    $query->where($attribute, 'like', sprintf('%%%s%%', $value));

                    break;

                case 'status':

                    $query->where($attribute, '=', $value);

                    break;

                case 'order_by':

                    $query->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;

            }

        }

        return $query->get();
    }
}
