<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Common\CustomController;

class OrderController extends CustomController
{
    protected $compareTypeList = ['>=', '=', '<='];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orderList = $this->paginate($this->search($request), 10)
                            ->withPath(route('admin.order.index'))
                            ->appends($request->capture()->except('page'));

        $paymentMethodList = [];

        foreach (PaymentMethod::all() as $paymentMethod) {

            $paymentMethodList[$paymentMethod->id] = $paymentMethod->name;

        }

        return view('admin.order.index', [
            'orderList' => $orderList,
            'paymentMethodList' => $paymentMethodList,
            'compareTypeList' => $this->compareTypeList
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
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return view('admin.order.show', [
            'order' => $order
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        return view('admin.order.edit', [
            'order' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $validatedData = $request->validate([
            'order_status'      => ['required', 'integer', Rule::in(array_keys(Order::getOrderStatusList()))],
            'payment_status'    => ['required', 'integer', Rule::in(array_keys(Order::getPaymentStatusList()))],
        ]);

        $order->update($validatedData);

        if ($order->save()) {

            return redirect(route('admin.order.show', ['order' => $order->id]))->with('status', '修改狀態成功');

        }

        return redirect()->back()->withErrors(new MessageBag(['修改狀態失敗']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function cancel(Order $order)
    {
        $paymentStatusList = [
            Order::PAYMENT_STATUS_HAS_PAID_AND_AUTHORIZED,
            Order::PAYMENT_STATUS_HAS_PAID_NOT_AUTHORIZED,
            Order::PAYMENT_STATUS_HAS_PAID_AND_CONFIRMED
        ];

        if ($order->payment_status === Order::PAYMENT_STATUS_NOT_PAID) {

            return $this->thirdPartyCancel($order);

        } elseif (in_array($order->payment_status, $paymentStatusList)) {

            return $this->thirdPartyRefund($order);

        }
    }

    /**
     * rollback已被取消的訂單
     *
     */
    protected function rollback(Order $order)
    {
        try {

            DB::transaction(function () use ($order) {

                //返還訂單内的商品
                foreach ($order->stocks as $stock) {

                    $updateStockQuery = DB::update('UPDATE stocks SET quantity = quantity + ? WHERE id = ?', [$stock->pivot->quantity, $stock->id]);

                    if ($updateStockQuery !== 1) {

                        throw new \ErrorException('返還stocks失敗');

                    }

                }

                $order->order_status = Order::ORDER_STATUS_CANCEL;

                if (!$order->save()) {

                    throw new \ErrorException('訂單狀態更新失敗');

                }

            });

            return TRUE;

        } catch (\Exception $e) {

            return FALSE;

        }

    }

    protected function search(Request $request)
    {
        $orderByList = ['order_number', 'order_status', 'payment_status', 'payment_method', 'total', 'created_at'];

        $validatedData = $request->validate([
            'order_number'      => ['nullable', 'string'],
            'nickname'          => ['nullable', 'string'],
            'order_status'      => ['nullable', 'integer', Rule::in(array_keys(Order::getOrderStatusList()))],
            'payment_status'    => ['nullable', 'integer', Rule::in(array_keys(Order::getPaymentStatusList()))],
            'payment_method'    => ['nullable', 'integer', 'exists:payment_methods,id'],
            'total'             => ['nullable', 'integer', 'min:0'],
            'date_start'        => ['nullable', 'date'],
            'date_end'          => ['nullable', 'date'],
            'order_by'          => ['nullable', 'string', Rule::in($orderByList)]
        ]);

        $orderQuery = Order::query();

        foreach ($validatedData as $attribute => $value) {

            if ($value == NULL) {

                continue;

            }

            switch ($attribute) {

                case 'order_number':

                    $orderQuery->where($attribute, 'like', sprintf('%%%s%%', $value));

                    break;

                case 'nickname':

                    $orderQuery->whereHas('user', function (Builder $query) use ($attribute, $value) {

                        $query->where($attribute, 'like', sprintf('%%%s%%', $value));

                    });

                    break;

                case 'order_status':
                case 'payment_status':
                case 'payment_method':

                    $orderQuery->where($attribute, $value);

                    break;

                case 'total':

                    if (!in_array($request->get('compare_type'), $this->compareTypeList)) {

                        break;

                    }

                    $orderQuery->where($attribute, $request->get('compare_type'), $value);

                    break;

                case 'date_start':

                    $orderQuery->where('created_at', '>=', $value);

                    break;

                case 'date_end':

                    $orderQuery->where('created_at', '<=', sprintf('%s 23:59:59', $value));

                    break;

                case 'order_by':

                    $orderQuery->orderBy($value, $request->get('is_asc') ? 'asc' : 'desc');

                    break;

            }

        }

        return $orderQuery->with('user')->get();

    }

    /**
     * 發起第三方確認付款請求
     *
     */
    public function thirdPartyConfirm(Order $order)
    {
        if (!$order) {

            $errors = new MessageBag(['此訂單不存在']);

            return redirect(route('order.index'))->withErrors($errors);

        }

        //儲存資料
        if (!empty(Request::all())) {

            $order->data = json_encode(array_merge(json_decode($order->data, TRUE) ?? [], Request::all()));

            $order->save();

        }

        $paymentMethod = resolve($order->paymentMethod->name);

        $result = $paymentMethod->confirmRequest($order);

        return redirect(route('admin.order.show', ['order' => $result['orderId']]));

    }

    /**
     * 發起第三方取消訂單的請求
     *
     */
    public function thirdPartyCancel(Order $order)
    {
        try {

            // TODO: 取消訂單不應該跟第三方支付綁在一起處理

            //確認訂單狀態
            if ($order->payment_status !== ORDER::PAYMENT_STATUS_NOT_PAID) {

                throw new \ErrorException('只有未付款的訂單才可以被取消, 已付款的訂單請以退款的方法處理');

            }

            $paymentMethod = resolve($order->paymentMethod->name);

            $result = $paymentMethod->cancelRequest($order);

            if (!$result['isSuccess'] || !$this->rollback($order)) {

                throw new \ErrorException('取消訂單失敗');

            }

            return redirect(route('admin.order.show', ['order' => $order->id]))->with('status', '取消訂單成功');

        } catch (\Exception $e) {

            $errors = new MessageBag([$e->getMessage()]);

            return redirect(route('admin.order.show', ['order' => $order->id]))->withErrors($errors);

        }

    }

    public function thirdPartyRefund(Order $order)
    {
        $paymentStatusList = [
            Order::PAYMENT_STATUS_HAS_PAID_AND_AUTHORIZED,
            Order::PAYMENT_STATUS_HAS_PAID_NOT_AUTHORIZED,
            Order::PAYMENT_STATUS_HAS_PAID_AND_CONFIRMED
        ];

        try {

            //確認訂單狀態
            if (!in_array($order->payment_status, $paymentStatusList)) {

                throw new \ErrorException('只有狀態為已付款的訂單才可以申請退款');

            }

            $paymentMethod = resolve($order->paymentMethod->name);

            $result = $paymentMethod->refundRequest($order);

            if (!$result['isSuccess'] || !$this->rollback($order)) {

                throw new \ErrorException('退款失敗, 請稍後再做嘗試');

            }

            return redirect(route('admin.order.show', ['order' => $order->id]))->with('status', '退款成功');

        } catch (\Exception $e) {

            $errors = new MessageBag([$e->getMessage()]);

            return redirect(route('admin.order.show', ['order' => $order->id]))->withErrors($errors);

        }
    }
}
