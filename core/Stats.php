<?php
class Stats {

	private CLIParser $parser;
	private CLIOutput $output;

	public function __construct(CLIParser $parser, CLIOutput $output) {
		$this->parser = $parser;
		$this->output = $output;

		$this->parseCommands($this->parser->getCommands());
	}

	private function parseCommands(array $commands) : void {
		$this->output->print(
			array('Statistics',
				array(
					'Use commands ' . CLIOutput::colorString( 'day, week, month, all or today (=default)', CLIOutput::BLUE ),
					'Optional add '.CLIOutput::colorString('-cats Hobby,Home', CLIOutput::BLUE) .
						' and/or '.CLIOutput::colorString('-names TTT,Website', CLIOutput::BLUE).' to filter for categories and names.' 
				)
			));
		switch( $commands[0] ) {
			case "day":
				$this->backUntil(time() - 86400, array_slice($commands, 1));
			case "week":
				$this->backUntil(time() - 604800, array_slice($commands, 1));
			case "month":
				$this->backUntil(time() - 2628000, array_slice($commands, 1));
			case "all":
				$this->backUntil(0, array_slice($commands, 1));
				break;
			case "today":
			default:
				$this->backUntil(strtotime("today"), array_slice($commands, 1));
		}
	}

	private function backUntil(int $timestamp, array $f) : void {
		$s = new StatsData($timestamp);

		// Name and Cat filtering
		$isCats = false;
		$isNames = false;
		$allNames = array();
		$allCats = array();
		foreach( $f as $arg ){
			if( $arg == '-cats' ){
				$isCats = true;
				$isNames = false;
				continue;
			}
			if( $arg == '-names' ){
				$isCats = false;
				$isNames = true;
				continue;
			}

			if( $isNames ){
				$names = explode(',', $arg);
				$names = array_map('trim', $names);
				$names = array_filter( $names, 'InputParser::checkNameInput');
				$allNames = array_merge($allNames, $names);
			}
			if( $isCats ){
				$cats = explode(',', $arg);
				$cats = array_map('trim', $cats);
				$cats = array_filter( $cats, 'InputParser::checkCategoryInput');
				$allCats = array_merge($allCats, $cats);
			}
		}
		$s->filterData($allNames, $allCats);

		$this->printDataset($s->getAllDatasets());
	}

	private function printDataset(array $data){
		print_r($data);
		/**
		 * ToDo
		 */
	}

}
?>