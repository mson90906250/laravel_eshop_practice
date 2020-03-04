<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Common\CustomController;

class CouponController extends CustomController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $couponList = $this->paginate($this->search($request), 10)
                            ->withPath(route('admin.coupon.index'))
                            ->appends($request->capture()->except('page'));

        return view('admin.coupon.index', [
            'couponList' => $couponList
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.coupon.create');
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
            'title'             => ['required', 'string'],
            'remain'            => ['required', 'integer', 'min:0'],
            'value_type'        => ['required', 'integer', Rule::in(array_keys(Coupon::getTypeLabelsForShow()))],
            'status'            => ['required', 'integer', Rule::in(array_keys(Coupon::getStatusLabels()))],
            'value'             => ['required', 'integer', 'min:0', function ($attribute, $value, $fail) use ($request) {

                if ($request->get('value_type') === Coupon::TYPE_PERCENT && $value > 100) {

                    $fail('當優惠券類型為折抵 %數時, 折抵數不得超過100');

                }

            }],
            'required_value'    => ['required', 'integer', 'min:0'],
            'start_time'        => ['required', 'date'],
            'end_time'          => ['required', 'date'],
        ]);

        $code = sprintf('ES%d%s', time(), uniqid());

        $validatedData['end_time'] = sprintf('%s 23:59:59', $validatedData['end_time']);

        $validatedData = array_merge($validatedData, ['code' => $code]);

        $createCoupon = Coupon::create($validatedData);

        if (!$createCoupon) {

            return redirect()->back()->withErrors(new MessageBag(['創建失敗']));

        }

        return redirect(route('admin.coupon.index'))->with('status', '創建成功');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupon.edit', [
            'coupon' => $coupon
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validatedData = $request->validate([
            'title'             => ['required', 'string'],
            'remain'            => ['required', 'integer', 'min:0'],
            'value_type'        => ['required', 'integer', Rule::in(array_keys(Coupon::getTypeLabelsForShow()))],
            'status'            => ['required', 'integer', Rule::in(array_keys(Coupon::getStatusLabels()))],
            'value'             => ['required', 'integer', 'min:0', function ($attribute, $value, $fail) use ($request) {

                if ($request->get('value_type') === Coupon::TYPE_PERCENT && $value > 100) {

                    $fail('當優惠券類型為折抵 %數時, 折抵數不得超過100');

                }

            }],
            'required_value'    => ['required', 'integer', 'min:0'],
            'start_time'        => ['required', 'date'],
            'end_time'          => ['required', 'date'],
        ]);

        if (!$coupon->update($validatedData)) {

            return redirect()->back()->withErrors(new MessageBag(['修改失敗']));

        }

        return redirect(route('admin.coupon.index'))->with('status', '修改成功');
    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'status'    => ['required', 'integer', Rule::in(array_keys(Coupon::getStatusLabels()))],
            'id'        => ['required', 'array'],
            'id.*'      => ['required', 'integer', 'exists:coupons,id']
        ]);

        $updateCoupons = Coupon::whereIn('id', $validatedData['id'])->update(['status' => $validatedData['status']]);

        if (!$updateCoupons) {

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
            'id.*'      => ['required', 'integer', 'exists:coupons,id']
        ]);

        $deleteCoupons = Coupon::whereIn('id', $validatedData['id'])->delete();

        if (!$deleteCoupons) {

            return redirect()->back()->withErrors(new MessageBag(['刪除失敗']));

        }

        return redirect()->back()->with('status', '刪除成功');

    }

    protected function search(Request $request)
    {
        $orderByList = ['id', 'code', 'remain', 'value_type', 'value', 'required_value', 'status', 'start_time', 'end_time'];

        $validatedData = $request->validate([
            'title'             => ['nullable', 'string'],
            'code'              => ['nullable', 'string'],
            'remain'            => ['nullable', 'integer', 'min:0'],
            'value_type'        => ['nullable', 'integer', Rule::in(array_keys(Coupon::getTypeLabelsForShow()))],
            'status'            => ['nullable', 'integer', Rule::in(array_keys(Coupon::getStatusLabels()))],
            'value'             => ['nullable', 'integer', 'min:0'],
            'required_value'    => ['nullable', 'integer', 'min:0'],
            'start_time'        => ['nullable', 'date'],
            'end_time'          => ['nullable', 'date'],
            'order_by'          => ['nullable', 'string', Rule::in($orderByList)]
        ]);

        $query = Coupon::query();

        foreach ($validatedData as $attribute => $value) {

            if (!$value) {

                continue;

            }

            switch ($attribute) {

                case 'title':
                case 'code':

                    $query->where([
                        [$attribute, 'like', sprintf('%%%s%%', $value)]
                    ]);

                    break;

                case 'remain':
                case 'value_type':
                case 'value':
                case 'status':
                case 'required_value':

                    $query->where([
                        [$attribute, '=', $value]
                    ]);

                    break;

                case 'start_time':

                    $query->where([
                        [$attribute, '>=', $value]
                    ]);

                    break;

                case 'end_time':

                    $query->where([
                        [$attribute, '<=', sprintf('%s 23:59:59', $value)]
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
