<?php
class Recorder {

	public Dialog $dialog;

	public function __construct() {
		$os = php_uname('s');
		if( stripos($os, 'darwin') !== false ){
			MacDialog::checkOSPackages();
			$this->dialog = new MacDialog();
		}
		else if( stripos($os, 'linux') !== false ){
			MacDialog::checkOSPackages();
			$this->dialog = new LinuxDialog();
		}
		else{
			die('Plattform not supported!!');
		}
	}

	public function record(bool $forcenew = false) : void {
		$r = Config::getStorageReader('current');
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
	}

	private function saveTaskTime(JSONReader $r) : void {
		$data = Config::getStorageReader(date('Y-m-d'));
		$data->setValue([null], array(
			"begin" => $r->getValue(['begin']),
			"end" => $r->getValue(['lastopend']) + Config::getSleepTime(),
			"name" => $r->getValue(['name']),
			"category" => $r->getValue(['category'])
		));
	}

	private function recordNew(JSONReader $r) : void {
		$this->dialog->setCategories(StatsData::getAllCategories());
		$this->dialog->open();

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
}
?>
