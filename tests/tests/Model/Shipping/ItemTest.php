<?php

use OpenBuildings\Monetary\Monetary;
use OpenBuildings\Monetary\Source_Static;

/**
 * Functest_TestsTest 
 *
 * @group model.shipping_item
 * 
 * @package Functest
 * @author Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 */
class Model_Shipping_ItemTest extends Testcase_Shipping {

	/**
	 * @covers Model_Shipping_Item::build_from
	 */
	public function test_build_from()
	{
		$store_purchase = Jam::find('store_purchase', 1);
		$france = Jam::find('location', 'France');
		$post = Jam::find('shipping_method', 1);

		$items = array($store_purchase->items[0], $store_purchase->items[2]);

		$shipping_items = Model_Shipping_Item::build_from($items, $france, $post);

		$this->assertCount(2, $shipping_items);

		foreach ($shipping_items as $item)
		{
			$this->assertINstanceOf('Model_Shipping_Item', $item);
			$this->assertEquals($france, $item->shipping_group->location);
			$this->assertEquals($post, $item->shipping_group->method);
		}

		$this->assertSame($items[0], $shipping_items[0]->purchase_item);
		$this->assertSame($items[1], $shipping_items[1]->purchase_item);
	}

	public function data_filter_discounted_items()
	{
		return array(
			array(array(100 => TRUE, 101 => TRUE), array()),
			array(array(100 => FALSE, 101 => TRUE), array(100)),
			array(array(100 => FALSE, 101 => FALSE), array(100, 101)),
		);
	}

	/**
	 * @covers Model_Shipping_Item::filter_discounted_items
	 * @dataProvider data_filter_discounted_items
	 */
	public function test_filter_discounted_items($items, $expected_ids)
	{
		$total = new Jam_Price(10, 'GBP');

		$shipping_items = array();
		foreach ($items as $id => $return_is_discounted) 
		{
			$shipping_group = $this->getMock('Model_Shipping_Group', array('is_discounted'), array('shipping_group'));

			$shipping_group
				->expects($this->once())
					->method('is_discounted')
					->with($this->identicalTo($total))
					->will($this->returnValue($return_is_discounted));

			$shipping_item = Jam::build('shipping_item', array(
				'id' => $id,
				'shipping_group' => $shipping_group,
			));

			$shipping_items []= $shipping_item;
		}

		$filtered = Model_Shipping_Item::filter_discounted_items($shipping_items, $total);

		$this->assertEquals($expected_ids, $this->ids($filtered));
	}

	public function data_sort_by_price()
	{
		$monetary = new Monetary('GBP', new Source_Static());

		return array(
			array(
				array(
					10 => array('price' => new Jam_Price(20, 'USD', $monetary)), 
					11 => array('price' => new Jam_Price(18, 'GBP', $monetary)),
					12 => array('price' => new Jam_Price(5, 'USD', $monetary)),
				),
				array(11, 10, 12),
			),
			array(
				array(
					10 => array('price' => new Jam_Price(20, 'USD', $monetary)), 
					11 => array('price' => new Jam_Price(20, 'GBP', $monetary)),
					12 => array('price' => new Jam_Price(5, 'USD', $monetary)),
					13 => array('price' => new Jam_Price(45, 'USD', $monetary)),
				),
				array(13, 11, 10, 12),
			),
		);
	}

	/**
	 * @dataProvider data_sort_by_price
	 */
	public function test_sort_by_price($params, $expected_ids)
	{
		$items = $this->getMockModelArray('shipping_item', $params);
		
		$sorted = Model_Shipping_Item::sort_by_price($items);

		$this->assertEquals($expected_ids, $this->ids($sorted));
	}

	public function data_relative_prices()
	{
		$monetary = new Monetary('GBP', new Source_Static());
		return array(
			array(
				array(
					10 => array(
						'price' => new Jam_Price(20, 'EUR', $monetary), 
						'total_additional_item_price' => new Jam_Price(40, 'EUR', $monetary)
					),
					11 => array(
						'price' => new Jam_Price(30, 'EUR', $monetary), 
						'total_price' => new Jam_Price(70, 'EUR', $monetary)
					),
					12 => array(
						'price' => new Jam_Price(10, 'EUR', $monetary), 
						'total_additional_item_price' => new Jam_Price(30, 'EUR', $monetary)
					),
				),
				array(
					new Jam_Price(70, 'EUR', $monetary),
					new Jam_Price(40, 'EUR', $monetary),
					new Jam_Price(30, 'EUR', $monetary),
				)
			),
		);
	}

	/**
	 * @dataProvider data_relative_prices
	 * @covers Model_Shipping_Item::relative_prices
	 */
	public function test_relative_prices($params, $expected)
	{
		$items = $this->getMockModelArray('shipping_item', $params);

		$prices = Model_Shipping_Item::relative_prices($items);

		$this->assertEquals($expected, $prices);
	}

	public static function data_compute_price()
	{
		$monetary = new Monetary('GBP', new Source_Static());
		return array(
			array(
				array(
					10 => array(
						'price' => new Jam_Price(20, 'EUR', $monetary),
						'additional_item_price' => new Jam_Price(70, 'EUR', $monetary),
						'is_discounted' => TRUE,
						'quantity' => 2,
						'group_key' => 'group1',
					),
					11 => array(
						'price' => new Jam_Price(18, 'EUR', $monetary),
						'additional_item_price' => new Jam_Price(10, 'EUR', $monetary),
						'is_discounted' => FALSE,
						'quantity' => 2,
						'group_key' => 'group2',
					),
					12 => array(
						'price' => new Jam_Price(25, 'EUR', $monetary),
						'additional_item_price' => new Jam_Price(12, 'EUR', $monetary),
						'is_discounted' => FALSE,
						'quantity' => 5,
						'group_key' => 'group2',
					),
					13 => array(
						'price' => new Jam_Price(30, 'EUR', $monetary),
						'additional_item_price' => new Jam_Price(22, 'EUR', $monetary),
						'is_discounted' => FALSE,
						'quantity' => 3,
						'group_key' => 'group3',
					),
				),
				new Jam_Price(300, 'EUR', $monetary),
				new Jam_Price(
					0 
					+ 10*2 
					+ 25+12*4
					+ 30+22*2
					, 'EUR', $monetary
				)
			),			
		);
	}

	/**
	 * @dataProvider data_compute_price
	 * @covers Model_Shipping_Item::compute_price
	 */
	public function test_compute_price($params, $total, $expected)
	{
		$items = $this->getMockModelArray('shipping_item', $params);

		$computed_price = Model_Shipping_Item::compute_price($items, $total);

		$this->assertEquals($expected, $computed_price);
	}

	/**
	 * @covers Model_Shipping_Item::currency
	 */
	public function test_currency()
	{
		$store_purchase_shipping = $this->getMock('Model_Store_Purchase_Shipping', array('currency'), array('store_purchase_shipping'));

		$store_purchase_shipping
			->expects($this->exactly(2))
				->method('currency')
				->will($this->onConsecutiveCalls('GBP', 'EUR'));

		$item = Jam::build('shipping_item', array('store_purchase_shipping' => $store_purchase_shipping));

		$this->assertEquals('GBP', $item->currency());
		$this->assertEquals('EUR', $item->currency());
	}

	/**
	 * @covers Model_Shipping_Item::monetary
	 */
	public function test_monetary()
	{
		$store_purchase_shipping = $this->getMock('Model_Store_Purchase_Shipping', array('monetary'), array('store_purchase_shipping'));
		$monetary = new Monetary();

		$store_purchase_shipping
			->expects($this->once())
				->method('monetary')
				->will($this->returnValue($monetary));

		$item = Jam::build('shipping_item', array('store_purchase_shipping' => $store_purchase_shipping));

		$this->assertSame($monetary, $item->monetary());
	}

	/**
	 * @covers Model_Shipping_Item::price
	 */
	public function test_price()
	{
		$monetary = new Monetary('GBP', new Source_Static());
		$item = $this->getMock('Model_Shipping_Item', array('currency', 'monetary'), array('shipping_item'));

		$item
			->expects($this->once())
				->method('monetary')
				->will($this->returnValue($monetary));

		$item
			->expects($this->once())
				->method('currency')
				->will($this->returnValue('EUR'));

		$item->set(array(
			'shipping_group' => array(
				'price' => new Jam_Price(10, 'USD'),
			)
		));

		$price = $item->price();

		$this->assertEquals(new Jam_Price(7.5091987684914, 'EUR', $monetary), $price);
	}

	/**
	 * @covers Model_Shipping_Item::additional_item_price
	 */
	public function test_additional_item_price()
	{
		$monetary = new Monetary('GBP', new Source_Static());
		$item = $this->getMock('Model_Shipping_Item', array('currency', 'monetary'), array('shipping_item'));

		$item
			->expects($this->exactly(2))
				->method('monetary')
				->will($this->returnValue($monetary));

		$item
			->expects($this->exactly(2))
				->method('currency')
				->will($this->returnValue('EUR'));

		$item->set(array(
			'shipping_group' => array(
				'price' => new Jam_Price(10, 'USD'),
				'additional_item_price' => new Jam_Price(5, 'USD'),
			)
		));

		$price = $item->additional_item_price();
		$this->assertEquals(new Jam_Price(3.7545993842457, 'EUR', $monetary), $price);

		$item->shipping_group->additional_item_price = NULL;

		$price = $item->additional_item_price();
		$this->assertEquals(new Jam_Price(7.5091987684914, 'EUR', $monetary), $price);
	}

	/**
	 * @covers Model_Shipping_Item::quantity
	 */
	public function test_quantity()
	{
		$item = Jam::build('shipping_item', array(
			'purchase_item' => array(
				'quantity' => 3
			),
		));

		$this->assertEquals(3, $item->quantity());
	}

	/**
	 * @covers Model_Shipping_Item::shipping_insist
	 */
	public function test_shipping_insist()
	{
		$shipping = Jam::build('shipping');
		$item = Jam::build('shipping_item', array(
			'shipping_group' => array(
				'shipping' => $shipping,
			),
		));

		$this->assertSame($shipping, $item->shipping_insist());

		$this->setExpectedException('Kohana_Exception');
		$item->shipping_group->shipping = NULL;

		$this->assertSame($shipping, $item->shipping_insist());
	}

	/**
	 * @covers Model_Shipping_Item::group_key
	 */
	public function test_group_key()
	{
		$item = Jam::build('shipping_item', array(
			'shipping_group' => array(
				'method_id' => 123,
				'shipping' => array(
					'ships_from_id' => 3123
				)
			)
		));

		$this->assertEquals('123-3123', $item->group_key());
	}

	/**
	 * @covers Model_Shipping_Item::is_discounted
	 */
	public function test_is_discounted()
	{
		$shipping_group = $this->getMock('Model_Shipping_Group', array('is_discounted'), array('shipping_group'));
		$price = new Jam_Price(10, 'GBP');

		$shipping_group
			->expects($this->exactly(3))
				->method('is_discounted')
				->with($this->identicalTo($price))
				->will($this->onConsecutiveCalls(TRUE, FALSE, TRUE));

		$item = Jam::build('shipping_item', array(
			'shipping_group' => $shipping_group
		));

		$this->assertTrue($item->is_discounted($price));
		$this->assertFalse($item->is_discounted($price));
		$this->assertTrue($item->is_discounted($price));
	}


	public function data_total_price()
	{
		$monetary = new Monetary('GBP', new Source_Static());

		return array(
			array(
				array(
					'price' => new Jam_Price(10, 'GBP', $monetary),
					'additional_item_price' => new Jam_Price(5, 'GBP', $monetary),
					'quantity' => 3,
				),
				new Jam_Price(10+5+5, 'GBP', $monetary),
			),			
			array(
				array(
					'price' => new Jam_Price(20, 'GBP', $monetary),
					'additional_item_price' => new Jam_Price(8, 'GBP', $monetary),
					'quantity' => 1,
				),
				new Jam_Price(20, 'GBP', $monetary),
			),
		);
	}

	/**
	 * @covers Model_Shipping_Item::total_price
 	 * @dataProvider data_total_price
	 */
	public function test_total_price($params, $expected)
	{
		$item = $this->getMockFromParams('Model_Shipping_Item', $params, array('shipping_item'));

		$total_price = $item->total_price();

		$this->assertEquals($expected, $total_price);
	}

		public function data_total_additional_item_price()
		{
			$monetary = new Monetary('GBP', new Source_Static());

			return array(
				array(
					array(
						'additional_item_price' => new Jam_Price(5, 'GBP', $monetary),
						'quantity' => 3,
					),
					new Jam_Price(5*3, 'GBP', $monetary),
				),			
				array(
					array(
						'additional_item_price' => new Jam_Price(8, 'GBP', $monetary),
						'quantity' => 1,
					),
					new Jam_Price(8*1, 'GBP', $monetary),
				),
			);
		}

		/**
		 * @covers Model_Shipping_Item::total_additional_item_price
	 	 * @dataProvider data_total_additional_item_price
		 */
		public function test_total_additional_item_price($params, $expected)
		{
			$item = $this->getMockFromParams('Model_Shipping_Item', $params, array('shipping_item'));

			$total_additional_item_price = $item->total_additional_item_price();

			$this->assertEquals($expected, $total_additional_item_price);
		}


}