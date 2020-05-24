<?php

namespace App\Http\Controllers\Admin;

use App\Models\ShippingFee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Common\CustomController;

class ShippingFeeController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $shippingFeeList = $this->paginate($this->search($request), 10)
                                ->withPath(route('admin.shippingFee.index'))
                                ->appends($request->capture()->except('page'));

        return view('admin.shipping_fee.index', [
            'shippingFeeList' => $shippingFeeList
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    protected function search(Request $request)
    {
        $orderByList = ['id', 'value', 'required_value', 'type', 'status'];

        $validatedData = $request->validate([
            'name'              => ['nullable', 'string'],
            'value'             => ['nullable', 'integer', 'min:0'],
            'required_value'    => ['nullable', 'integer', 'min:0'],
            'type'              => ['nullable', 'integer', Rule::in(array_keys(ShippingFee::getTypeList()))],
            'status'            => ['nullable', 'integer', Rule::in(array_keys(ShippingFee::getStatusLabels()))],
            'order_by'          => ['nullable', 'string', Rule::in($orderByList)]
        ]);

        $query = ShippingFee::query();

        foreach ($validatedData as $attribute => $value) {

            if (!$value) {

                continue;

            }

            switch ($attribute) {

                case 'name':

                    $query->where([
                        [$attribute, 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'value':
                case 'required_value':
                case 'type':
                case 'status':

                    $query->where([
                        [$attribute, '=', $value]
                    ]);

                    break;

                case 'order_by':

                    $query->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;

            }

        }

        return $query->get();

    }

}
