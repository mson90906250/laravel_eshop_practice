<?php

namespace App\Http\Controllers\Front;

use App\Models\Coupon;
use App\Models\CartStorage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Common\CustomController;
use Darryldecode\Cart\Facades\CartFacade as Cart;

class CartController extends CustomController
{
    const CART_COOKIE_NAME = 'cart_cookie';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cart = self::getCart();

        $couponList = Coupon::where([
                            ['status', '=', Coupon::STATUS_ON],
                            ['required_value', '<=', $cart ? $cart->getSubTotal() : 0],
                            ['remain', '>', 0],
                            ['start_time', '<=', date('Y-m-d H:i:s')],
                            ['end_time', '>=', date('Y-m-d H:i:s')]
                        ])
                        ->get();

        $this->storeToCartStorage();

        $total = $cart->getTotal();

        $subTotal = $cart->getSubTotal();

        $hasCoupon = $cart->getConditions()['coupon'] ?? FALSE;

        $couponId = 0;

        if ($hasCoupon) {

            $couponId = $hasCoupon->getAttributes()['couponId'];

            $couponDiscountValue = $hasCoupon->parsedRawValue;

        }

        return view('front.shop.cart', [
            'cart' => $cart,
            'couponList' => $couponList,
            'total' => $total,
            'subTotal' => $subTotal,
            'hasCoupon' => $hasCoupon,
            'ownedCoupon' => Coupon::find($couponId),
            'couponDiscountValue' => $couponDiscountValue ?? NULL
        ]);
    }

    /**
     * 在cart裏增加一項新商品 或 更新已存在的商品
     *
     */
    public function store()
    {
        $validatedData = Request::validate([
            'id'                                => ['required', 'integer', 'exists:stocks'], //*** id使用stocks表的
            'attributes.stock.description'      => ['nullable', 'string'],
            'attributes.stock.image'            => ['nullable', 'string'],
            'attributes.stock.maxQuantity'      => ['required', 'integer', 'min:0'],
            'attributes.product.id'             => ['required', 'integer', 'exists:products,id'],
            'name'                              => ['required', 'string'],
            'price'                             => ['required', 'min:0'],
            'quantity'                          => ['required', 'min:1'],
        ]);

        //確認商品數目是否超過庫存量 有則將數量改成最大值
        $cart = self::getCart();

        $item = $cart->getContent()->get($validatedData['id']);

        $maxQuantity = $validatedData['attributes']['stock']['maxQuantity'];

        if ($item && ($item->quantity + $validatedData['quantity']) > $maxQuantity) {

            Cart::update($validatedData['id'], [
                'quantity' => [
                    'relative'  => FALSE,
                    'value'     => $maxQuantity
                ]
            ]);

        } else {

            Cart::add($validatedData);

        }

        if (Auth::guard('web')->check()) {

            $this->storeToCartStorage();

        }

        return redirect(route('shop.show', ['product' => $validatedData['attributes']['product']['id']]))
                ->with('status', '成功加入購物車!!');
    }

    /**
     * 更新cart裏的某一項商品
     *
     */
    public function update()
    {
        $cart = self::getCart();

        if (!$cart) {

            return redirect()->back();

        }

        foreach (Request::get('item') as $rowId => $quantity) {

            if ($quantity < 1) {

                $cart->remove($rowId);

            } else {

                $cart->update($rowId, [
                    'quantity' => [
                        'relative'  => FALSE,
                        'value'     => $quantity
                    ]
                ]);
            }
        }

        $this->checkCoupon($cart);

        if (Auth::guard('web')->check()) {

            $this->storeToCartStorage();

        }

        return  redirect(route('cart.index'));
    }

    /**
     * 刪除cart裏的某個商品
     *
     */
    public function destroy()
    {
        $cart = self::getCart();

        if ($cart) {

            $cart->remove(Request::get('rowId'));

        }

        $this->checkCoupon($cart);

        if (Auth::guard('web')->check()) {

            $this->storeToCartStorage();

        }

        return redirect(route('cart.index'))->with('status', '成功刪除商品');
    }

    /**
     * 使用優惠券
     *
     */
    public function addCoupon()
    {
        try {

            $validatedData = Request::validate([
                'code' => 'required|regex:/[0-9A-Za-z]+/'
            ], [
                'required' => ':attribute必須輸入',
                'regex' => ':attribute格式不符'
            ], [
                'code' => '優惠代碼'
            ]);

            $coupon = Coupon::where([
                                ['code', '=', $validatedData['code']],
                                ['status', '=', Coupon::STATUS_ON],
                                ['remain', '>', 0],
                                ['start_time', '<=', date('Y-m-d H:i:s')],
                                ['end_time', '>=', date('Y-m-d H:i:s')]
                            ])
                            ->first();

            if (!$coupon) {

                throw new \ErrorException('此優惠代碼無效');

            }

            $cart = self::getCart();

            $cart->removeCartCondition('coupon');

            if ($cart->getSubTotal() < $coupon->required_value) {

                throw new \ErrorException(sprintf('此優惠券必須滿足的金額: %d', $coupon->required_value));
            }

            $condition = new \Darryldecode\Cart\CartCondition(array(
                'name' => 'coupon',
                'type' => 'coupon',
                'target' => 'total',
                'value' => Coupon::getDiscountValue($coupon),
                'attributes' => [
                    'title' => $coupon->title,
                    'couponId' => $coupon->id
                ]
            ));

            $cart->condition($condition);

            return redirect()->back()->with('status', '優惠券使用成功');

        } catch (\Exception $e) {

            $errors = new MessageBag([$e->getMessage()]);

            return redirect()->back()->withErrors($errors);

        }



    }

    /**
     * 將user的cart的内容存到cart_storage表
     *
     */
    protected function storeToCartStorage()
    {
        if (!Auth::guard('web')->check()) {

            return FALSE;

        }

        $user = Auth::guard('web')->user();

        $cartStorage = CartStorage::where('user_id', $user->id)->first() ?? new CartStorage(['user_id' => $user->id]);

        $cartStorage->cart_data = serialize(self::getCart()->getContent()->toArray());

        $cartStorage->save();
    }

    /**
     * 用來確認每次更新後的購物車是否還符合優惠券的條件
     */
    protected function checkCoupon(\Darryldecode\Cart\Cart $cart)
    {
        $couponCondition = $cart->getConditions()['coupon'] ?? NULL;

        if ($couponCondition) {

            $coupon = Coupon::find($couponCondition->getAttributes()['couponId']);

            if ($coupon && $coupon->required_value > $cart->getSubTotal()) {

                $cart->removeCartCondition('coupon');

            }

        }

        return TRUE;
    }

    /**
     * setCart
     */
    public static function setCart()
    {
        // TODO: 將cart相關的方法移到自製的cart類別裏
        $cookie = Cookie::get(self::CART_COOKIE_NAME);

        if ($cookie) {

            $sessionKey = $cookie;

        } else if (Auth::guard('web')->check()) {

            $user = Auth::guard('web')->user();

            $sessionKey = sprintf('cart_%s',Hash::make(sprintf('%d_%s', $user->id, $user->name)));

            if (CartStorage::where('user_id', $user->id)->first()) {

                $cartItems = unserialize(CartStorage::where('user_id', $user->id)->first()->cart_data);

                $cart = Cart::session($sessionKey);

                if (!empty($cartItems)) {

                    $cart->add($cartItems);

                }
            }

        } else {

            $sessionKey = sprintf('cart_%s',Hash::make(sprintf('%d_%s', time(), Request::ip())));

        }

        Cart::session($sessionKey);

        Cookie::queue(self::CART_COOKIE_NAME, $sessionKey, 20);
    }

    public static function getCart()
    {
        $sessionKey = Cookie::get(self::CART_COOKIE_NAME);

        $cart = $sessionKey ? Cart::session($sessionKey) : NULL;

        return $cart;
    }


}
