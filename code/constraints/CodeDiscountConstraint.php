<?php

class CodeDiscountConstraint extends DiscountConstraint{
	
	private static $many_many = array(
		"Codes" => "DiscountCode"
	);

	public function updateCMSFields(FieldList $fields) {
		if($this->owner->isInDB()){
			$fields->fieldByname("Root.Main.Constraints")->push(new Tab("Codes",
				GridField::create("Codes", "Codes", $this->owner->Codes(),
					GridFieldConfig::create()
						// ->removeComponentsByType("GridFieldAddNewButton")
						// ->removeComponentsByType("GridFieldEditButton")
						// ->addComponent(new GridFieldAddNewInlineButton())
						// ->addComponent(new GridFieldDeleteAction())
						->addComponent(new GridFieldButtonRow())
						->addComponent(new GridFieldToolbarHeader())
						->addComponent($cols = new GridFieldEditableColumns())
						->addComponent(new GridFieldAddNewInlineButton())
						->addComponent(new GridFieldDeleteAction())
						->addComponent(new GridFieldPaginator(10))
				)
			));
		}


		$displayfields = array(
			'Code' => array(
				'title' => 'Code',
				'field' => new TextField("Code")
			)				 
		);
		//add drop-down color selection
		// $colors = $this->owner->getColors();
		// if($colors->exists()){
		// 	$displayfields['ColorID'] = function($record, $col, $grid) use ($colors){
		// 		return DropdownField::create($col,"Color",
		// 			$colors->map('ID','Value')->toArray()
		// 		)->setHasEmptyDefault(true);
		// 	};
		// }
		$cols->setDisplayFields($displayfields);
	}

	public function filter(DataList $list) {
		// if($code = $this->findCouponCode()){
		// 	$list = $list
		// 		->where("(\"Code\" IS NULL) OR (\"Code\" = '$code')");
		// }else{
		// 	$list = $list->where("\"Code\" IS NULL");
		// }
		
		//join ?

		return $list;
	}

	public function check(Discount $discount) {
		$code = strtolower($this->findCouponCode());
		if($discount->Code && ($code != strtolower($discount->Code))){
			$this->error("Coupon code doesn't match $code");
			return false;
		}

		return true;
	}

	protected function findCouponCode() {
		return isset($this->context['CouponCode']) ? $this->context['CouponCode'] : null;
	}

}