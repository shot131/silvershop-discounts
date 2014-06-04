<?php

class SpecificPriceTest extends SapphireTest{
	
	protected static $fixture_file = array(
		'shop_discount/tests/fixtures/SpecificPrices.yml'
	);
	
	function setUp(){
		parent::setUp();
		Object::add_extension("Product", "SpecificPricingExtension");
		Object::add_extension("ProductVariation", "SpecificPricingExtension");
	}

	function testProductPrice() {
		$product = $this->objFromFixture("Product", "raspberrypi");
		$this->assertEquals(45, $product->sellingPrice());
		$this->assertTrue($product->IsReduced());
		$this->assertEquals(5, $product->getTotalReduction());
	}

	function testProductVariationPrice() {
		$variation = $this->objFromFixture("ProductVariation", "robot_30gb");
		$this->assertEquals(90, $variation->sellingPrice());
		$this->assertTrue($variation->IsReduced());
		$this->assertEquals(10, $variation->getTotalReduction());
	}

	function testPriceHistory() {
		$product = new Product();
		$product->update(array(
			'Title' => 'Test Product',
			'BasePrice' => 200
		));
		$product->write();

		//update other fields

		$prices = array(200, 320, 320, 150, 50.50);
		foreach($prices as $price){
			$product->BasePrice = $price;
			$product->write();
		}

		$this->assertDOSEquals(array(
			array("BasePrice" => 200),
			array("BasePrice" => 320),
			array("BasePrice" => 150),
			array("BasePrice" => 50.50),
		), $product->getPriceHistory());
	}

}