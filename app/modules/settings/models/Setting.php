<?php namespace App\Modules\Settings\Models;

class Setting extends \Eloquent {

	protected $table = "settings";
	public $timestamps = false;

	/**
	 * Returns a formatted varname entry,
	 * this ensures that line breaks are returned.
	 *
	 * @return string
	 */
	public function varname() {
		return nl2br($this -> varname);
	}

	/**
	 * Returns a formatted groupname entry,
	 * this ensures that line breaks are returned.
	 *
	 * @return string
	 */
	public function groupname() {
		return nl2br($this -> groupname);
	}

	/**
	 * Returns a formatted value entry,
	 * this ensures that line breaks are returned.
	 *
	 * @return string
	 */
	public function value() {
		return nl2br($this -> value);
	}

	/**
	 * Returns a formatted defaultvalue entry,
	 * this ensures that line breaks are returned.
	 *
	 * @return string
	 */
	public function defaultvalue() {
		return nl2br($this -> defaultvalue);
	}

}
