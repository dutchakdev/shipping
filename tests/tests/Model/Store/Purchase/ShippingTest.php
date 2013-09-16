<?php

use OpenBuildings\Monetary\Monetary;
/**
 * Functest_TestsTest 
 *
 * @group model.purchase
 * 
 * @package Functest
 * @author Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 */
class Model_Store_Purchase_ShippingTest extends Testcase_Shipping {

	/**
	 * @dataProvider Model_Shipping_ItemTest::data_compute_price
	 * @covers Model_Store_Purchase_Shipping::price
	 */
	public function test_compute_price($params, $total, $expected)
	{
		$items = $this->getMockModelArray('shipping_item', $params);

		$store_purchase_shipping = $this->getMock('Model_Store_Purchase_Shipping', array('total_purchase_price'), array('store_purchase_shipping'));

		$store_purchase_shipping
			->expects($this->once())
				->method('total_purchase_price')
				->will($this->returnValue($total));

		$store_purchase_shipping->items = $items;

		$price = $store_purchase_shipping->price(Jam::build('purchase_item'));

		$this->assertEquals($expected, $price);
	}


	/**
	 * @covers Model_Store_Purchase_Shipping::currency
	 */
	public function test_currency()
	{
		$store_purchase = $this->getMock('Model_Store_Purchase', array('currency'), array('store_purchase'));

		$store_purchase
			->expects($this->exactly(2))
				->method('currency')
				->will($this->onConsecutiveCalls('GBP', 'EUR'));

		$item = Jam::build('store_purchase_shipping', array('store_purchase' => $store_purchase));

		$this->assertEquals('GBP', $item->currency());
		$this->assertEquals('EUR', $item->currency());
	}

	/**
	 * @covers Model_Store_Purchase_Shipping::location
	 */
	public function test_location()
	{
		$france = Jam::find('location', 'France');
		
		$store_purchase = Jam::build('store_purchase', array(
			'location' => $france,
		));

		$this->assertSame($france, $store_purchase->location);
	}

	/**
	 * @covers Model_Store_Purchase_Shipping::monetary
	 */
	public function test_monetary()
	{
		$store_purchase = $this->getMock('Model_Store_Purchase', array('monetary'), array('store_purchase'));
		$monetary = new Monetary();

		$store_purchase
			->expects($this->once())
				->method('monetary')
				->will($this->returnValue($monetary));

		$item = Jam::build('store_purchase_shipping', array('store_purchase' => $store_purchase));

		$this->assertSame($monetary, $item->monetary());
	}

	/**
	 * @covers Model_Store_Purchase_Shipping::total_purchase_price
	 */
	public function test_total_purchase_price()
	{
		$store_purchase = $this->getMock('Model_Store_Purchase', array('total_price'), array('store_purchase'));
		$price = new Jam_Price(10, 'GBP');

		$store_purchase
			->expects($this->once())
				->method('total_price')
				->with($this->equalTo(array('is_payable' => TRUE)))
				->will($this->returnValue($price));

		$item = Jam::build('store_purchase_shipping', array('store_purchase' => $store_purchase));

		$this->assertSame($price, $item->total_purchase_price());
	}

	/**
	 * @covers Model_Store_Purchase_Shipping::build_items_from
	 */
	public function test_build_items_from()
	{
		$france = Jam::find('location', 'France');
		$post = Jam::find('shipping_method', 1);
		$group = Jam::build('shipping_group');

		$store_purchase_shipping = Jam::build('store_purchase_shipping', array(
			'location' => $france
		));

		$shipping = $this->getMock('Model_Shipping', array('group_for'), array('shipping'));

		$shipping
			->expects($this->exactly(2))
				->method('group_for')
				->with($this->identicalTo($france), $this->identicalTo($post))
				->will($this->returnValue($group));

		$purchase_items = array(
			Jam::build('purchase_item', array(
				'reference' => Jam::build('product', array(
					'shipping' => $shipping
				))
			)),
			Jam::build('purchase_item', array(
				'reference' => Jam::build('product', array(
					'shipping' => $shipping
				))
			)),
		);

		$store_purchase_shipping->build_items_from($purchase_items, $post);

		$items = $store_purchase_shipping->items;
		
		$this->assertCount(2, $items);

		foreach ($items as $i => $item) 
		{
			$this->assertInstanceOf('Model_Shipping_Item', $item);
			$this->assertSame($purchase_items[$i], $item->purchase_item);
			$this->assertSame($group, $item->shipping_group);
			$this->assertSame($store_purchase_shipping, $item->store_purchase_shipping);
		}
	}

	public function data_build_items_from_errors()
	{
		return array(
			array(array('asd'), 'The array must be of Model_Purchase_Item object, item [1] was "array"'),
			array(Jam::build('product'), 'The array must be of Model_Purchase_Item object, item [1] was "Model_Product"'),
		);
	}

	/**
	 * @covers Model_Store_Purchase_Shipping::build_items_from
	 * @dataProvider data_build_items_from_errors
	 */
	public function test_build_items_from_errors($wrong_object, $expected_exception_message)
	{
		$post = Jam::find('shipping_method', 1);

		$purchase_items = array(
			Jam::build('purchase_item'),
			$wrong_object,
		);

		$store_purchase_shipping = Jam::build('store_purchase_shipping');

		$this->setExpectedException('Kohana_Exception', $expected_exception_message);

		$store_purchase_shipping->build_items_from($purchase_items, $post);
	}
}