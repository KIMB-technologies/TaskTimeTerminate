<?php
class Stats {

	const DAYS_DISPLAY = 'd.m';
	const DAYS_MAXCOUNT = 5;
	const DEVICE_MAXCOUNT = 3;

	private CLIParser $parser;
	private CLIOutput $output;

	private bool $todayview = false;

	public function __construct(CLIParser $parser, CLIOutput $output) {
		$this->parser = $parser;
		$this->output = $output;

		$this->parseCommands($this->parser->getCommands());
	}

	private function parseCommands(array $commands) : void {
		$this->showHelp(in_array( '-h', $commands) || in_array( '--help', $commands));
		if(in_array('--help', $commands)){
			return;
		}
		
		switch( $commands[0] ) {
			case "week":
			case "cWeek":
				$this->backUntil(strtotime("last Monday"), array_slice($commands, 1));
				break;
			case "month":
			case "cMonth":
				$this->backUntil(strtotime(date("Y-m")."-01"), array_slice($commands, 1));
				break;
			case "year":
			case "cYear":
				$this->backUntil(strtotime(date("Y")."-01-01"), array_slice($commands, 1));
				break;
			
			case "lWeek":
				$this->backUntil(time() - 7*24*60*60, array_slice($commands, 1));
				break;
			case "lMonth":
				$this->backUntil(time() - 30*24*60*60, array_slice($commands, 1));
				break;
			case "lYear":
				$this->backUntil(time() - 365*24*60*60, array_slice($commands, 1));
				break;

			case "all":
				$this->backUntil(0, array_slice($commands, 1));
				break;
			case "range":
				$this->rangeStats(array_slice($commands, 1));
				break;
			
			case "day":
			case "lDay":
				$this->backUntil(time() - 24*60*60, array_slice($commands, 1));
				break;
			case "cDay":
			case "today": // => also default
				$commands = array_slice($commands, 1);
			default:
				$this->todayview = true;
				$this->backUntil(strtotime("today"), $commands);
		}
	}

	private function showHelp(bool $full = false) : void {
		$info = array('Statistics', 
			array(
				'Use commands ' .
					CLIOutput::colorString( 'week', CLIOutput::BLUE ) . ', ' .
					CLIOutput::colorString( 'month', CLIOutput::BLUE ) . ', ' .
					CLIOutput::colorString( 'range', CLIOutput::BLUE ) . ' or ' .
					CLIOutput::colorString( 'today', CLIOutput::BLUE ) . ' (=default).',
				'Optionally add, e.g., '.CLIOutput::colorString('-cats Hobby,Home', CLIOutput::BLUE) . ', to filter for categories.'
			),
		);

		if($full) {
			$info[] = array(
				'Select time range:',
					array(
						'Current day, week, month or year:',
							array(
								CLIOutput::colorString('cDay', CLIOutput::BLUE) . ' or ' .
									CLIOutput::colorString('today', CLIOutput::BLUE) . ' => since last midnight',
								CLIOutput::colorString('cWeek', CLIOutput::BLUE) . ' or ' .
									CLIOutput::colorString('week', CLIOutput::BLUE) . ' => since last Monday',
								CLIOutput::colorString('cMonth', CLIOutput::BLUE) . ' or ' .
									CLIOutput::colorString('month', CLIOutput::BLUE) . ' => since beginning of current month',
								CLIOutput::colorString('cYear', CLIOutput::BLUE) . ' or ' .
									CLIOutput::colorString('year', CLIOutput::BLUE) . ' => since last first of January'	
							),
						'Last day, week, month or year:',
							array(
								CLIOutput::colorString('lDay', CLIOutput::BLUE) . ' or ' .
									CLIOutput::colorString('day', CLIOutput::BLUE) . ' => last 24 hours',
								CLIOutput::colorString('lWeek', CLIOutput::BLUE) . ' => last 7 days',
								CLIOutput::colorString('lMonth', CLIOutput::BLUE) . ' => last 30 days',
								CLIOutput::colorString('lYear', CLIOutput::BLUE) . ' => last 365 days'
							),
						'Special ranges:',
							array(
								CLIOutput::colorString('all', CLIOutput::BLUE) . ' => all time (may load some minutes)',
								CLIOutput::colorString('range', CLIOutput::BLUE) . ' => range between dates ' . 
									'(add one or two dates, e.g., ' . CLIOutput::colorString('2015-01-01 2015-01-31', CLIOutput::BLUE) . ')'
							)
					),
				'Optional arguments:',
					array(
						'Filter for categories, names and/or devices (if synced across devices) by adding',
						array(
							CLIOutput::colorString('-cats Hobby,Home', CLIOutput::BLUE), 
							CLIOutput::colorString('-names TTT,Website', CLIOutput::BLUE),
							CLIOutput::colorString('-devices Laptop,Desktop', CLIOutput::BLUE)
						)
					),
					array(
						'Also '.CLIOutput::colorString('-localOnly', CLIOutput::BLUE) . 
						' can be added to ignore external devices added via sync.',
					),
				'Examples:',
					array(
						CLIOutput::colorString( 'ttt s week -localOnly', CLIOutput::BLUE ),
						CLIOutput::colorString( 'ttt s cMonth -cats Hobby', CLIOutput::BLUE ),
						CLIOutput::colorString( 'ttt s range 2021-02-03 2021-03-02', CLIOutput::BLUE ),
						CLIOutput::colorString( 'ttt s all', CLIOutput::BLUE )
					)
			);
		}
		else {
			$info[] = array(
				'Add ' .
					CLIOutput::colorString( '-h', CLIOutput::BLUE ) .' or ' .
					CLIOutput::colorString( '--help', CLIOutput::BLUE ) . 
				' to get more information.'
			);
		}	
		$this->output->print($info);
	}

	private function rangeStats(array $commands) {
		$cmd = array_values(array_filter(array_slice($commands, 0, 2), function ($d) {
			return preg_match( StatsData::DATE_PREG, $d) === 1;
		})); // create array of range days

		if( count($cmd) === 1){
			$until = strtotime($cmd[0]);
			$forwardTo = $until + 86399;

			$this->todayview = true;
		}
		else if( count($cmd) === 2 ){
			sort( $cmd ); // change s.t. ('start of range', 'end of range')
			$until = strtotime($cmd[0]);
			$forwardTo = strtotime($cmd[1]) + 86399;
		}
		else{
			$this->output->print(
				array('Statistics -> Range',
					array(
						'Please give single day or range of two days',
						array(
							CLIOutput::colorString( 'range 2020-01-11', CLIOutput::BLUE ),
							CLIOutput::colorString( 'range 2020-01-11 2020-01-13', CLIOutput::BLUE ),
						)
					)
				));
			return;
		}

		$this->backUntil($until,
			array_slice($commands, count($cmd)), // shift number of range days
			$forwardTo); 
	}

	private function backUntil(int $timestamp, array $f, int $forwardTo = StatsData::FORWARD_TO_NOW) : void {
		// Name, Device and Cat filtering
		$isCats = false;
		$isNames = false;
		$isDevices = false;
		$allNames = array();
		$allCats = array();
		$allDevices = array();

		// No syn
		$onlyLocal = false;
		foreach( $f as $arg ){
			if( $arg == '-cats' ){
				$isCats = true;
				$isNames = false;
				$isDevices = false;
				continue;
			}
			if( $arg == '-names' ){
				$isCats = false;
				$isNames = true;
				$isDevices = false;
				continue;
			}
			if( $arg == '-devices' ){
				$isCats = false;
				$isNames = false;
				$isDevices = true;
				continue;
			}
			if( $arg == '-localOnly' ){
				$onlyLocal = true;
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
			if( $isDevices ){
				$devs = explode(',', $arg);
				$devs = array_map('trim', $devs);
				$devs = array_filter( $devs, 'InputParser::checkDeviceName');
				$allDevices = array_merge($allDevices, $devs);
				$isDevices = false;
			}
		}

		$s = new StatsData($timestamp, $forwardTo, $onlyLocal);
		$s->filterData($allNames, $allCats, $allDevices);

		$this->printDataset($s->getAllDatasets());
	}

	private function printDataset(array $data) : void {
		array_multisort( // sort multiple days (s.t. latest days are first)
			array_column( $data, 'begin' ), SORT_DESC,
			$data
		);

		$noExternalDevice = true;
		$combi = array();
		foreach( $data as $d ){
			$key = $d['category'] . '++' . $d['name'];
			$day = date(self::DAYS_DISPLAY, $d['begin']);
			if( !isset($combi[$key])){
				$combi[$key] = array(
					'category' => $d['category'],
					'name' => $d['name'],
					'duration' => 0,
					'times' => 0,
					'days' => array( $day ),
					'devices' => array()
				);
			}
			$combi[$key]['times']++;
			$combi[$key]['duration'] += $d['duration'];

			if( !in_array( $day, $combi[$key]['days'] ) ){
				$combi[$key]['days'][] = $day;
			}
			if( !empty($d['device']) && !in_array( $d['device'], $combi[$key]['devices'] ) ){
				$combi[$key]['devices'][] = $d['device'];
				$noExternalDevice = false;
			}
		}
		$combi = array_values($combi);

		$table = array();
		foreach( $combi as $d ){
			$table[] = array_merge(
				array(
					'Category' => $d['category'],
					'Name' => $d['name'],
					'Time' => $d['duration'],
					'Work Items' => str_pad($d['times'], 4, " ", STR_PAD_LEFT),
					'Days' => implode(', ', array_slice($d['days'], 0, self::DAYS_MAXCOUNT))
						. (count($d['days']) > self::DAYS_MAXCOUNT ? ', ...' : '' )
				),
				$noExternalDevice ? array() : array(
					'Other devices' => implode(', ', array_slice($d['devices'], 0, self::DEVICE_MAXCOUNT))
					. (count($d['devices']) > self::DEVICE_MAXCOUNT ? ', ...' : '' )
				)
			);
		}

		array_multisort(
			array_column( $table, 'Category' ), SORT_ASC,
			array_column( $table, 'Time' ), SORT_DESC,
			array_column( $table, 'Name' ), SORT_ASC,
			$table
		);

		foreach( $table as &$d ){
			$d['Time'] = Time::secToTime($d['Time']);
		}

		$this->output->table($table);

		if( $this->todayview ){
			$this->printTodayView($data);
		}

		ExtensionEventHandler::statsViewed($data, $this->output);
	}

	

	private function printTodayView(array $data) : void {
		array_multisort(
			array_column( $data, 'begin' ), SORT_ASC,
			$data
		);

		$table = array();
		$i = 0;
		$lastval = null;
		$lastend = 0;
		foreach( $data as $d ){
			if( $lastval === $d['category'] . '++' . $d['name']
				&& $lastend + Config::getSleepTime() > $d['begin'] ){
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
			$lastend = $d['end'];
			$lastval = $d['category'] . '++' . $d['name'];
		}

		foreach( $table as &$d ){
			$d['Time'] = Time::secToTime($d['Time']);
		}

		$this->output->table($table);
	}

}
?>
