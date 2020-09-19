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
	 * Set the last tasks name and category
	 * 	User can enter +5m to get five minutes more
	 * 	for last task.
	 */
	public function setLastTask(?string $name, ?int $category){
		$this->chName = $name;
		$this->chCategory = $category;
	}

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
	 * JSON Response handler
	 * 	may be called by open()
	 * @param $stdout {string} STDout JSON from dialog process
	 */
	protected function handleStdoutJson(string $stdout) : void {
		if( !empty($stdout) ){
			$stdout = json_decode($stdout, true);

			if( $stdout['pause'] ){
				$this->shortBreak = true;
			}
			else{
				if( strpos($stdout['time'], '+' ) === false ){ // not additional time for last task/ else values are set
					$this->chCategory = in_array($stdout['cat'], $this->categories) ? array_search($stdout['cat'], $this->categories) : null; // category id
					$this->chName = InputParser::checkNameInput($stdout['name']) ? $stdout['name'] : null;
				}
				$this->chTime = InputParser::checkTimeInput($stdout['time']) ? $stdout['time'] : null;

				if( is_null($this->chCategory) || is_null( $this->chTime ) || is_null($this->chName)){
					$this->open();
					return;
				}
			}
		}
		else { // error
			$this->open();
			return;
		}
	}

	/**
	 * Create commandline args (cats, last used cat and task)
	 * @param $dialogPath {string} the command to call/ init dialog process
	 */
	protected function createCommandLineArgs(string $dialogPath) : string {
		$cmd = array(
			$dialogPath,
			'-cats',
			'"'. implode(',', $this->categories) .'"',
			'-lastcat',
			'"'.$this->categories[$this->chCategory ?? array_key_first($this->categories)] .'"'
		);
		if( !empty($this->chName)){
			array_push($cmd,
				'-lasttask',
				'"'.$this->chName.'"'
			);
		}

		return implode(' ', $cmd);
	}

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