<?php
class Stats {

	private CLIParser $parser;
	private CLIOutput $output;

	private bool $todayview = false;

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
				break;
			case "week":
				$this->backUntil(time() - 604800, array_slice($commands, 1));
				break;
			case "month":
				$this->backUntil(time() - 2628000, array_slice($commands, 1));
				break;
			case "all":
				$this->backUntil(0, array_slice($commands, 1));
				break;
			case "today":
				$commands = array_slice($commands, 1);
			default:
				$this->todayview = true;
				$this->backUntil(strtotime("today"), $commands);
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
				$isNames = false;
			}
			if( $isCats ){
				$cats = explode(',', $arg);
				$cats = array_map('trim', $cats);
				$cats = array_filter( $cats, 'InputParser::checkCategoryInput');
				$allCats = array_merge($allCats, $cats);
				$isCats = false;
			}
		}
		$s->filterData($allNames, $allCats);

		$this->printDataset($s->getAllDatasets());
	}

	private function printDataset(array $data) : void {
		$combi = array();
		foreach( $data as $d ){
			$key = $d['category'] . '++' . $d['name'];
			if( !isset($combi[$key])){
				$combi[$key] = array(
					'category' => $d['category'],
					'name' => $d['name'],
					'duration' => 0,
					'times' => 0
				);
			}
			$combi[$key]['times']++;
			$combi[$key]['duration'] += $d['duration'];
		}
		$combi = array_values($combi);

		$table = array();
		foreach( $combi as $d ){
			$table[] = array(
				'Category' => $d['category'],
				'Name' => $d['name'],
				'Time' => $d['duration'],
				'Work Items' => str_pad($d['times'], 4, " ", STR_PAD_LEFT)
			);
		}

		array_multisort(
			array_column( $table, 'Category' ), SORT_ASC,
			array_column( $table, 'Time' ), SORT_DESC,
			array_column( $table, 'Name' ), SORT_ASC,
			$table
		);

		foreach( $table as &$d ){
			$d['Time'] = self::secToTime($d['Time']);
		}

		$this->output->table($table);

		if( $this->todayview ){
			$this->printTodayView($data);
		}
	}

	public static function secToTime(int $t) : string {
		return str_pad(
				($t >= 3600 ? intval($t/3600) . 'h ' : '' ) .
				str_pad(
					intval(($t % 3600) / 60) . 'm',
					3,
					" ",
					STR_PAD_LEFT
				),
				8,
				" ",
				STR_PAD_LEFT
			);
	}

	private function printTodayView(array $data) : void {
		array_multisort(
			array_column( $data, 'begin' ), SORT_ASC,
			$data
		);

		$table = array();
		$i = 0;
		$lastval = null;
		foreach( $data as $d ){
			if( $lastval === $d['category'] . '++' . $d['name'] ){
				$table[$i-1]['Time'] += $d['duration'];
			}
			else{
				$table[$i++] = array(
					'Begin' => date('H:i', $d['begin']),
					'Category' => $d['category'],
					'Name' => $d['name'],
					'Time' => $d['duration']
				);
			}
			$lastval = $d['category'] . '++' . $d['name'];
		}

		foreach( $table as &$d ){
			$d['Time'] = self::secToTime($d['Time']);
		}

		$this->output->table($table);
	}

}
?>
