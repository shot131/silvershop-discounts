<?php
/**
 * Applies a discount to current order, if applicable, when entered at checkout.
 * @package shop-discounts
 */
class OrderCoupon extends Discount {

	private static $has_one = array(
		//used to link to gift voucher purchase
		"GiftVoucher" => "GiftVoucher_OrderItem"
	);

	private static $searchable_fields = array(
		"Title",
		"Code"
	);

	private static $summary_fields = array(
		"Title",
		"Code",
		"DiscountNice",
		"StartDate",
		"EndDate"
	);

	private static $singular_name = "Coupon";
	private static $plural_name = "Coupons";

	private static $minimum_code_length = null;
	private static $generated_code_length = 10;

	public static function get_by_code($code) {
		return self::get()
				->filter('Code:nocase', $code)
				->first();
	}

	/**
	* Generates a unique code.
	* @todo depending on the length, it may be possible that all the possible
	*       codes have been generated.
	* @return string the new code
	*/
	public static function generate_code($length = null, $prefix = "") {
		$length = ($length) ? $length : self::config()->generated_code_length;
		$code = null;
		$generator = Injector::inst()->create('RandomGenerator');
		do{
			$code = $prefix.strtoupper(substr($generator->randomToken(), 0, $length));
		}while(
			self::get()->filter("Code:nocase", $code)->exists()
		);

		return $code;
	}

	public function getCMSFields($params = null) {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab(
			"Root.Main", array(
				$codefield = TextField::create("Code", _t('OrderCoupon.Code', 'Code'))->setMaxLength(25),
			), 
			"Active"
		);
		if($this->owner->Code && $codefield){
			$fields->replaceField("Code",
				$codefield->performReadonlyTransformation()
			);
		}

		return $fields;
	}

    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);

        $labels['Code'] = _t('OrderCoupon.Code', 'Code');

        return $labels;
    }

	public function validate() {
		$result = parent::validate();
		$minLength = $this->config()->minimum_code_length;
		$code = $this->getField('Code');
		if($minLength && $code && $this->isChanged('Code') && strlen($code) < $minLength) {
			$result->error(
				_t(
					'OrderCoupon.INVALIDMINLENGTH',
					'Coupon code must be at least {length} characters in length',
					array('length' => $this->config()->minimum_code_length)
				),
				'INVALIDMINLENGTH'
			);
		}

		return $result;
	}

	/**
	 * Autogenerate the code, if needed
	 */
	protected function onBeforeWrite() {
		if (empty($this->Code)){
			$this->Code = self::generate_code();
		}
		parent::onBeforeWrite();
	}

	/**
	* Forces codes to be alpha-numeric, uppercase, and trimmed
	*/
	public function setCode($code) {
		$code = trim(preg_replace('/[^0-9a-zA-Z]+/', '', $code));
		$this->setField("Code", strtoupper($code));
	}

	public function canView($member = null) {
		return true;
	}

	public function canCreate($member = null) {
		return true;
	}

	public function canDelete($member = null) {
		if($this->getUseCount()) {
			return false;
		}
		return true;
	}

	public function canEdit($member = null) {
		if($this->getUseCount() && !$this->Active) {
			return false;
		}
		return true;
	}

}
