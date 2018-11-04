<?php

class CodeDiscountConstraint extends DiscountConstraint{
	
	private static $db = array(
		"Code" => "Varchar(25)"
	);

	//cms field is added in OrderCoupon class

	public function filter(DataList $list) {
        $code = $this->findCouponCode();
		if(!empty($code)){
		    if (is_array($code)) {
                $codeArr = Convert::raw2sql($code, true);
                $list = $list
                    ->where("(\"Code\" IS NULL) OR (\"Code\" IN (" . implode(',', $codeArr) . "))");
            } else {
                $list = $list
                    ->where("(\"Code\" IS NULL) OR (\"Code\" = ".  Convert::raw2sql($code, true) . ")");
            }
		}else{
			$list = $list->where("\"Code\" IS NULL");
		}

		return $list;
	}

	public function check(Discount $discount) {
		$code = $this->findCouponCode();
		if (is_array($code)) {
		    foreach ($code as $k => $codeVal) {
                $code[$k] = strtolower($codeVal);
            }
        }
		if($discount->Code && (!in_array(strtolower($discount->Code), $code))){
			$this->error(_t('CodeDiscountConstraint.ErrorCodeDontMatch',
                "Coupon code doesn't match {code}",
                array('code' => $code)
            ));
			return false;
		}

		return true;
	}

	protected function findCouponCode() {
		return isset($this->context['CouponCode']) ? $this->context['CouponCode'] : null;
	}

}