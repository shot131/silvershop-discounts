<?php

class DiscountCode extends DataObject{
	
	private static $db = array(
		"Code" => "Varchar(25)"
	);

	function getTitle(){
		return $this->Code;
	}

	/**
	* Generates a unique code.
	* @todo depending on the length, it may be possible that all the possible
	*       codes have been generated.
	* @return string the new code
	*/
	public static function generate($length = null, $prefix = "") {
		$length = ($length) ? $length : self::$generated_code_length;
		$code = null;
		$generator = Injector::inst()->create('RandomGenerator');
		do{
			$code = $prefix.strtoupper(substr($generator->randomToken(), 0, $length));
		}while(
			self::get()->filter("Code:nocase", $code)->exists()
		);

		return $code;
	}

}