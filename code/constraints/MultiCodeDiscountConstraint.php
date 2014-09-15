<?php

class MultiCodeDiscountConstraint extends DiscountConstraint{
	
	private static $has_many = array(
		"Codes" => "DiscountCode"
	);

	function updateCMSFields(FieldList $fields){
		//add datalist for codes
		//create a datalist component for generating codes
	}

	public function filter(DataList $list) {
		//TODO: join with Codes

		return $list;
	}

	public function check(Discount $discount) {
		$code = strtolower($this->findCouponCode());
		
		//TODO: look for code in list

		return true;
	}

	protected function findCouponCode() {
		return isset($this->context['CouponCode']) ? $this->context['CouponCode'] : null;
	}

}

class DiscountCode extends DataObject{

	private static $db = array(
		"Code" => "Varchar(25)"
	);
	
}