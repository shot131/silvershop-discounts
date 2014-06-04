<?php

class ProductDiscountExtension extends DataExtension{
	
	private static $casting = array(
		'TotalReduction' => 'Currency'
	);

	/**
	 * Get the difference between the original price
	 * and the new price.
	 */
	function getTotalReduction($original = "BasePrice") {
		$reduction = $this->owner->{$original} - $this->owner->sellingPrice();
		//keep it above 0;
		$reduction = $reduction < 0 ? 0 : $reduction;
		return $reduction;
	}

	/**
	 * Check if this product has a reduced price.
	 */
	function IsReduced(){
		return (bool)$this->getTotalReduction();
	}

	/**
	 * Get the historical changes in price.
	 */
	function getPriceHistory($pricefield = 'BasePrice') {
		$versions = Versioned::get_all_versions(
			get_class($this->owner), 
			$this->owner->ID
		);
		$output = new ArrayList();
		$price = 0;
		foreach($versions as $version){
			//if($version->{$pricefield} != $price){
				$output->push($version);
				$price = $version->{$pricefield};
			//}
		}

		return $output;
	}

}