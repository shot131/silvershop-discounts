<?php

/**
 * @package silvershop-discounts
 */
class OrderDiscountModifier extends OrderModifier {

    private static $defaults = array(
        "Type" => "Deductable"
    );

    private static $many_many = array(
        "Discounts" => "Discount"
    );

    private static $many_many_extraFields = array(
        'Discounts' => array(
            'DiscountAmount' => 'Currency'
        )
    );

    private static $singular_name = "Discount";

    private static $plural_name = "Discounts";

    public function value($incoming)
    {
        $this->getDiscount();
        return $this->Amount;
    }

    public function getDiscount()
    {
        $calculator = $this->getCalculator();
        $amount = $calculator->calculate();
        $this->Amount = $amount;
        return $amount;
    }

    public function getCalculator()
    {
        $context = array();

        if($code = $this->getCode()) {
            $context['CouponCode'] = $code;
        }

        $order = $this->Order();
        $order->extend("updateDiscountContext", $context);

        return new Shop\Discount\Calculator($order, $context);
    }

    public function getCode() {
        $order = $this->Order();
        $code = $order->IsCart() ? Session::get("cart.couponcode") : null;

        if(!$code) {
            $code = [];
            foreach ($this->Order()->Discounts() as $discount) {
                if (!empty($discount->Code)) {
                    $code[] = $discount->Code;
                }
            }
        }

        return $code;
    }

    public function getSubTitle() {
        return $this->getUsedCodes();
    }

    public function getUsedCodes() {
        return implode(",",
            $this->Order()->Discounts()
                ->filter("Code:not", "")
                ->map('ID','Title')
        );
    }

    public function Amount() {
        return $this->getDiscount();
    }

    public function ShowInTable() {
        return $this->Amount() > 0;
    }
}
