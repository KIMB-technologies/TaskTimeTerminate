<?php
class CLI {

	private CLIParser $parser;
	private CLIOutput $output;

	public function __construct(CLIParser $parser) {
		$this->parser = $parser;
		$this->output = new CLIOutput();
	}

	public function checkTask(){
		switch ($this->parser->getTask()) {
			case CLIParser::TASK_VERSION:
				$this->version();
				break;
			case CLIParser::TASK_HELP:
				$this->help();
				break;
			case CLIParser::TASK_STATS:
				new Stats($this->parser, $this->output);
				break;
			case CLIParser::TASK_SETTINGS:
				new Settings($this->parser, $this->output);
				break;
			case CLIParser::TASK_RECORD:
				(new Recorder())->record(true);
				if( Config::getStorageReader('config')->isValue(['status']) && !Config::getStorageReader('config')->getValue(['status']) ){
					$this->togglePause(); // make sure to enable
				}
				break;
			case CLIParser::TASK_PAUSE:
				$this->togglePause();
				break;
			default:
				$this->help();
			break;
		}
	}

	private function help(){
		$this->output->print(array(
			'Help',
			array(
				'./cli.php TASK [COMMAND, ...]',
				'List of all Tasks, See commands per task when starting task',
				$this->parser->getTaskParams()
			)
		));
		
	}

	private function version(){
		$this->output->print(array(
			'Version',
			array(
				Utilities::VERSION,
				'TaskTimeTerminate',
				'(c) 2020 by KIMB-technologies',
				'https://git.5d7.eu/KIMB-technologies/TaskTimeTerminate',
				array(
					'released under the terms of GNU Public License Version 3',
					'https://www.gnu.org/licenses/gpl-3.0.txt'
				)
			)
		));
	}

	private function togglePause(){
		$c = Config::getStorageReader('config');
		$enabled = !$c->isValue(['status']) ? true : $c->getValue(['status']);
		$c->setValue(['status'], !$enabled);

		if( $enabled ){ // not enabled
			$is = CLIOutput::colorString( 'disabled', CLIOutput::RED);
			$was = CLIOutput::colorString( 'enabled', CLIOutput::GREEN);
		}
		else{ // enabled
			$was = CLIOutput::colorString( 'disabled', CLIOutput::RED);
			$is = CLIOutput::colorString( 'enabled', CLIOutput::GREEN);
		}

		$this->output->print(array(
			'Toggle Pause',
			array(
				'Status was: ' . $was,
				'Status is: ' . $is
			)
		));
	}
}
?>