<?php
class Recorder {

	const PID_FILE_LINUX = '/run/lock/TaskTimeTerminate';
	const PID_FILE_MAC = '/private/tmp/TaskTimeTerminate';

	private Dialog $dialog;
	private $lockfileHandle;

	public function __construct(bool $newSocket = true, bool $inTerminal = false) {
		if($inTerminal){
			$this->dialog = new InTerminalDialog();
		}
		else{
			switch (Utilities::getOS()){
				case Utilities::OS_MAC:
					MacDialog::checkOSPackages();
					$this->dialog = new MacDialog();
					if($newSocket){
						AutocompleteSocket::createSocketThread();
					}
					break;
				case Utilities::OS_LINUX:
					LinuxDialog::checkOSPackages();
					$this->dialog = new LinuxDialog();
					break;
				case Utilities::OS_WIN:
					WindowsDialog::checkOSPackages();
					$this->dialog = new WindowsDialog();
					if($newSocket){
						AutocompleteSocket::createSocketThread();
					}
					break;
				case Utilities::OS_TELEGRAM:
					TelegramDialog::checkOSPackages();
					$this->dialog = new TelegramDialog();
					break;
				default:
					die( PHP_EOL . 'Plattform not supported!!' . PHP_EOL . PHP_EOL);
			}
		}
	}

	public function record(bool $forcenew = false) : void {
		if($this->waitForOpenedDialogs()){
			$r = Config::getStorageReader('current');
			ReaderManager::addReader($r);
			if( empty($r->getArray())){ // first start etc.
				$r->setArray(array(
					'name' => '',
					'category' => '',
					'end' => -1,
					'begin' => -1,
					'lastopend' => time(),

				));
				$this->recordNew($r);
			}
			else{
				$wasIncative = time() > $r->getValue(['lastopend']) + Config::getSleepTime() * 3; // pc was shut down (no work!!)
				$end = $r->getValue(['end']);
				if( $end === -1 ){ // short break enabled
					$this->recordNew($r);
				}
				else if(time() < $end && !$forcenew && !$wasIncative ){
					// sleep (no limit reached)
				}
				else{
					$this->saveTaskTime($r);
					$this->recordNew($r);
				}
				$r->setValue(['lastopend'], time());
			}
			ReaderManager::clearAll();
		}
		$this->unlockDialogs();
	}

	private function sameTaskExists(JSONReader $data, JSONReader $new ) : bool {
		$id = $data->searchValue([], $new->getValue(['begin']), 'begin');
		if( $id !== false ){
			return $data->isValue([$id, 'name'], $new->getValue(['name']) ) &&
				$data->isValue([$id, 'category'], $new->getValue(['category']) );
		}
		else{
			return false;
		}
	}

	private function saveTaskTime(JSONReader $r) : void {
		$data = Config::getStorageReader(
				date(
					'Y-m-d',
					is_int($r->getValue(['begin'])) ? $r->getValue(['begin']) : time()
				)
			);
		ReaderManager::addReader($data);
		if( !$this->sameTaskExists($data, $r) ){ // make sure to save each task only once for same timestamps (some type of fix for https://github.com/KIMB-technologies/TaskTimeTerminate/issues/21)
			$data->setValue([null], array(
				"begin" => $r->getValue(['begin']),
				"end" => $r->getValue(['lastopend']) + Config::getSleepTime(),
				"name" => $r->getValue(['name']),
				"category" => $r->getValue(['category'])
			));
		}
		else{
			CLIOutput::error(CLIOutput::ERROR_WARN, "Last task is already stored -- therefore TTT ignores it.");
		}
		// also save to sync
		StatsLoader::saveDayTasks( $data->getArray() );
		ExtensionEventHandler::newRecordSaved(
				$r->getValue(['begin']), $r->getValue(['lastopend']) + Config::getSleepTime(), 
				$r->getValue(['name']), $r->getValue(['category'])
			);
		
		$this->dialog->setLastTask(
			$r->getValue(['name']),
			in_array($r->getValue(['category']), StatsData::getAllCategories()) ?
				array_search($r->getValue(['category']), StatsData::getAllCategories()) : null
		);
	}

	private function recordNew(JSONReader $r) : void {
		$this->dialog->setCategories(StatsData::getAllCategories());
		$this->dialog->open();

		ExtensionEventHandler::newDialogOpened($this->dialog);

		if( !$this->dialog->doesShortBreak()){
			$r->setValue(['name'], $this->dialog->getChosenName());
			$r->setValue(['category'], StatsData::getAllCategories()[$this->dialog->getChosenCategory()]);
			$r->setValue(['begin'], time());
			$r->setValue(['end'], InputParser::getEndTimestamp( $this->dialog->getChosenTime() ) );
		}
		else{
			$r->setValue(['name'],'');
			$r->setValue(['category'], '');
			$r->setValue(['begin'], -1);
			$r->setValue(['end'], -1 );
		}
	}

	private function waitForOpenedDialogs() : bool {
		$lockfile = Config::getStorageDir() . '/openedDialog.lock';
		
		$this->lockfileHandle = fopen( $lockfile, 'c' );
		return flock( $this->lockfileHandle, LOCK_EX );
	}

	private function unlockDialogs() {
		flock($this->lockfileHandle, LOCK_UN );
		fclose($this->lockfileHandle);
	}

	public static function runOnlyOnce() : void {
		if( Utilities::getOS() === Utilities::OS_LINUX || Utilities::getOS() === Utilities::OS_MAC ){
			$osPidFile = Utilities::getOS() === Utilities::OS_LINUX ? self::PID_FILE_LINUX : self::PID_FILE_MAC;
			$pid = getmypid();
			if( $pid !== false ){
				// check if other running
				if( is_file( $osPidFile ) ){
					$otherPid = file_get_contents($osPidFile);
					if( is_numeric($otherPid) ){
						if( Utilities::getOS() === Utilities::OS_MAC || !posix_kill( intval($otherPid), SIGQUIT ) ){
							posix_kill( intval($otherPid), SIGKILL );
						}
					}
				}
				// set yourself running
				file_put_contents( $osPidFile, $pid, LOCK_EX );

				// delete run file on exit
				register_shutdown_function( function ($pid, $osPidFile) {
					if( is_file( $osPidFile ) && file_get_contents($osPidFile) == $pid ){
						unlink($osPidFile);
					}
				}, $pid, $osPidFile);
			}
		}
	}
}
?>
