<?php

class DatetimeDiscountConstraint extends DiscountConstraint{

	private static $db = array(
		"StartDate" => "Datetime",
		"EndDate" => "Datetime"
	);
	
	public function updateCMSFields(FieldList $fields) {
        $fields->findOrMakeTab('Root.Main.Constraints.Main', _t('DiscountModelAdmin.Main', 'Main'));
		$fields->addFieldToTab("Root.Main.Constraints.Main",
			FieldGroup::create(_t('DatetimeDiscountConstraint.Title', 'Valid date range:'),
				CouponDatetimeField::create("StartDate", _t('DatetimeDiscountConstraint.StartDateTime', 'Start Date / Time')),
				CouponDatetimeField::create("EndDate", _t('DatetimeDiscountConstraint.EndDateTime', 'End Date / Time'))
			)->setDescription(
				_t('DatetimeDiscountConstraint.Description', 'You should set the end time to 23:59:59, if you want to include the entire end day.')
			)
		);
	}

	public function filter(DataList $list) {
	    // Check whether we are looking at a historic order or a current one
		$datetime = $this->order->Placed ? $this->order->Created : date('Y-m-d H:i:s');

		//to bad ORM filtering for NULL doesn't work...so we need to use where
		return $list->where(
			"(\"Discount\".\"StartDate\" IS NULL) OR (\"Discount\".\"StartDate\" < '$datetime')"
		)
		->where(
			"(\"Discount\".\"EndDate\" IS NULL) OR (\"Discount\".\"EndDate\" > '$datetime')"
		);
	}

	public function check(Discount $discount) {

		//time period
		$startDate = strtotime($discount->StartDate);
		$endDate = strtotime($discount->EndDate);

		// Adjust the time to the when the order was placed or the current time non completed orders
		$now = $this->order->Placed ? strtotime($this->order->Created) : time();

		if($endDate && $endDate < $now){
			$this->error(_t("OrderCoupon.EXPIRED", "This coupon has already expired."));
			return false;
		}

		if($startDate && $startDate > $now){
			$this->error(_t("OrderCoupon.TOOEARLY", "It is too early to use this coupon."));
			return false;
		}

		return true;
	}
}