<?php

namespace App\Http\Controllers\Admin;

use App\Models\ShippingFee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Common\CustomController;
use Illuminate\Support\MessageBag;

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
        return view('admin.shipping_fee.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'              => ['nullable', 'string'],
            'value'             => ['nullable', 'integer', 'min:0'],
            'required_value'    => ['nullable', 'integer', 'min:0'],
            'type'              => ['nullable', 'integer', Rule::in(array_keys(ShippingFee::getTypeList()))],
            'status'            => ['nullable', 'integer', Rule::in(array_keys(ShippingFee::getStatusLabels()))],
        ]);

        $createQuery = ShippingFee::create($validatedData);

        if (!$createQuery) {

            return redirect()->back()->withErrors(new MessageBag(['創建失敗']));

        }

        return redirect(route('admin.shippingFee.index'))->with('status', '創建成功');


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ShippingFee $shippingFee
     * @return \Illuminate\Http\Response
     */
    public function edit(ShippingFee $shippingFee)
    {
        return view('admin.shipping_fee.edit', [
            'shippingFee' => $shippingFee
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  ShippingFee $shippingFee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShippingFee $shippingFee)
    {
        $validatedData = $request->validate([
            'name'              => ['required', 'string'],
            'value'             => ['required', 'integer', 'min:0'],
            'required_value'    => ['required', 'integer', 'min:0'],
            'type'              => ['required', 'integer', Rule::in(array_keys(ShippingFee::getTypeList()))],
            'status'            => ['required', 'integer', Rule::in(array_keys(ShippingFee::getStatusLabels()))],
        ]);

        if (!$shippingFee->update($validatedData)) {

            return redirect()->back()->withErrors(new MessageBag(['修改失敗']));

        }

        return redirect(route('admin.shippingFee.index'))->with('status', '修改成功');

    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'status'    => ['required', 'integer', Rule::in(array_keys(ShippingFee::getStatusLabels()))],
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:shipping_fee,id']
        ]);

        $updateQuery = ShippingFee::whereIn('id', $validatedData['id'])
                                    ->update(['status' => $validatedData['status']]);

        if (!$updateQuery) {

            return redirect()->back()->withErrors(new MessageBag(['狀態更新失敗']));

        }

        return redirect()->back()->with('status', '狀態更新成功');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:shipping_fee,id']
        ]);

        $deleteQuery = ShippingFee::whereIn('id', $validatedData['id'])
                                    ->delete();

        if (!$deleteQuery) {

            return redirect()->back()->withErrors(new MessageBag(['刪除失敗']));

        }

        return redirect()->back()->with('status', '刪除成功');

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
