<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Jam Model: Location
 *
 * @package applicaiton
 * @author Ivan Kerin
 * @copyright  (c) 2011-2013 Despark Ltd.
 */
class Kohana_Model_Location extends Jam_Model {

	/**
	 * @codeCoverageIgnore
	 */
	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'closuretable' => Jam::behavior('closuretable'),
			))
			
			->fields(array(
				'id' => Jam::field('primary'),
				'name' => Jam::field('string'),
				'short_name' => Jam::field('string'),
				'type' => Jam::field('string'),
			))

			->validator('name', array('present' => TRUE));
	}

	public function contains(Model_Location $location)
	{
		return (($this->id() == $location->id()) OR $this->is_ansestor_of($location));
	}
}