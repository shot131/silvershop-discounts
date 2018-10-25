<?php

class ValueDiscountConstraint extends DiscountConstraint{
	
	private static $db = array(
		"MinOrderValue" => "Currency"
	);

	private static $field_labels = array(
		"MinOrderValue" => "Minimum subtotal of order"
	);

	public function updateCMSFields(FieldList $fields) {
        $fields->findOrMakeTab('Root.Main.Constraints.Main', _t('DiscountModelAdmin.Main', 'Main'));
		$fields->addFieldToTab("Root.Main.Constraints.Main",
			CurrencyField::create("MinOrderValue", _t('ValueDiscountConstraint.MinOrderValue', 'Minimum Order Value'))
		);
	}
	
	public function filter(DataList $list) {
		return $list->filterAny(array(
			"MinOrderValue" => 0,
			"MinOrderValue:LessThanOrEqual" => $this->order->SubTotal()
		));
	}

	public function check(Discount $discount) {
		if($discount->MinOrderValue > 0 && $this->order->SubTotal() < $discount->MinOrderValue){
			$this->error(
                _t('ValueDiscountConstraint.ErrorMinOrderValue',
                    'Your cart subtotal must be at least {value} to use this discount',
                    array('value' => $discount->dbObject("MinOrderValue")->Nice())
            ));
			return false;
		}

		return true;
	}	
	
}