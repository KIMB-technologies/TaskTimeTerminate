<?php
/**
 * The system will 'require_once' this file to load the extensions.
 * All configuration has to be done here and all callback functions have
 * to be defined. Of course an autoloader mya be uses.
 * It is recommended to use a custom namespace. All core classes
 * of TTT are in the global namespace.
 * 
 * This example file is released under public domain.
 */

namespace TTTExampleExtension;

class ExampleExtension {

	/**
	 * Called each time a new record about a completed task was saved.
	 * @param $begin unix timestamp of task's start
	 * @param $end unix timestamp of task's end
	 * @param $name task's name
	 * @param $category task's category (as name -> string)
	 */
	public static function newRecordSaved(int $begin, int $end, string $name, string $category) : void {
		echo PHP_EOL . "ExampleExtension: Completed a new task {". implode(', ', [$begin, $end, $name, $category]) ."}" . PHP_EOL;
	}

	/**
	 * Called each time a new dialog was opened and is now closed again.
	 *  @param $dialog the used dialog object
	 */
	public static function newDialogOpened(\Dialog $dialog) : void {
		echo PHP_EOL . "ExampleExtension: Closed a dialog and user has " . ($dialog->doesShortBreak() ? "a" : "no" ) . "break." . PHP_EOL;
	}

	/**
	 * Called each time after the background job just wakes up (before opening dialogs
	 * for new tasks/ continuing current task)
	 */
	public static function daemonAfterSleep() : void {
		echo PHP_EOL . "ExampleExtension: Just woke up." . PHP_EOL;
	}

	/**
	 * Called each time before the background job sleeps again (after opening dialogs
	 * for new tasks/ continuing current task)
	 */
	public static function daemonBeforeSleep()  : void {
		echo PHP_EOL . "ExampleExtension: Before going to sleep." . PHP_EOL;
	}

	/**
	 * Called each time a new day file is synced across devices (also called if
	 * all sync disabled)
	 * @param $array like as used in StatsAccess::setDayTasks(), like JSON in files per day
	 */
	public static function dayFileSync(array $array, int $day) : void {
		echo PHP_EOL . "ExampleExtension: Changed a day containing  " . count($array) . " Tasks for Day " . date('Y-m-d', $day). PHP_EOL;
		echo "\t e.g. task: " . print_r($array[0], true) . PHP_EOL;
	}

	/**
	 * Called after the stats output to cli is finished, just before ttt stops execution.
	 * Can be use to add more views to StatsView
	 * @param $array the data array from StatsData::getAllDatasets()
	 * @param $output the CLI Output object
	 */
	public static function statsViewed(array $data, \CLIOutput $output) : void {
		$output->print(array(
			"ExampleExtension: got " . count($data) . " datasets to show."
		), \CLIOutput::YELLOW);
	}

	/**
	 * Called after a category was added or deleted
	 * @param $affectedCategory the name of the affected category
	 * @param $added True if category added, false if deleted
	 */
	public static function settingsCatsChanged(string $affectedCategory, bool $added)  : void {
		echo PHP_EOL . "ExampleExtension: '" . $affectedCategory ."' was " . ($added ? "added." : "deleted" ) . PHP_EOL;
	}

	/**
	 * Direct access via CLI-Interface
	 * @param $parser the CLI Parser object
	 * @param $output the CLI Output object
	 */
	public static function cli( \CLIParser $parser, \CLIOutput $output ) : void {
		$output->print(array(
			"ExampleExtension: CommandLine called with",
			array(print_r( $parser->getCommands(), true))
		), \CLIOutput::YELLOW);
	}
}
?>