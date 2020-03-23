<?php

/**
 * The Dialog class, defines api to open a dialog asking user
 * 	for Name of Task, Category of Task and Time/ Limit for task
 * Each Platform/ OS/ ... needs an own class implementing the abstract methods.
 * 	Register Platform in Class Recorder::__construct()
 */
abstract class Dialog {

	/**
	 * Array of available categories
	 */
	protected array $categories = array();

	/**
	 * The values chosen by user
	 */
	protected ?int $chCategory = null;
	protected ?string $chName = null;
	protected ?string $chTime = null;
	protected bool $shortBreak = false;

	/**
	 * Set an array of available categories [ID => CatName, ...]
	 * 	No short break now, will be set in open().
	 * @param $categories array of categories
	 */
	public function setCategories(array $categories){
		$this->shortBreak = false;
		$this->categories = $categories;
	}

	/**
	 * Open the dialog for user and block until user gave input
	 * 	=> different per Platform 
	 */
	public abstract function open() : void;

	/**
	 * Get chosen value for category.
	 * @return the id (key of array)
	 */
	public function getChosenCategory() : int {
		return $this->chCategory;
	}

	/**
	 * Get the name input value from the user
	 * @return the name value (just validated)
	 */
	public function getChosenName() : string {
		return $this->chName;
	}

	/**
	 * Get the time input value from the user
	 * @return the time value (no parsing done, but validated)
	 */
	public function getChosenTime() : string {
		return $this->chTime;
	}

	/**
	 * A Short break means, that the user closed the dialog without filling something in
	 * 
	 */
	public function doesShortBreak() : bool {
		return $this->shortBreak;
	}


	/**
	 * Check if all necessary packages are installed on sytem
	 *	=> echo message and die() if not
	 *	=> different per Platform 
	 */
	public abstract static function checkOSPackages() : void;
}

?>