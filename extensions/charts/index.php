<?php

namespace TTTCharts;

class Charts {

	/**
	 * Called after the stats output to cli is finished, just before ttt stops execution.
	 * Adds some charts to the stats view.
	 * 
	 * @param $array the data array from StatsData::getAllDatasets()
	 * @param $output the CLI Output object
	 */
	public static function statsViewed(array $data, \CLIOutput $output) : void {
		if(empty($data)){
			return;
		}
		// plot categories
		$sums = array();
		foreach( $data as $k => $d ){
			if( empty($sums[$d['category']]) ) {
				$sums[$d['category']] = 0;
			}
			$sums[$d['category']] += $d['duration'];
		}
		// if only one category, plot tasks
		if( count($sums) <= 1){
			$sums = array();
			foreach( $data as $k => $d ){
				if( empty($sums[$d['name']]) ) {
					$sums[$d['name']] = 0;
				}
				$sums[$d['name']] += $d['duration'];
			}
		}

		// plot time in ttt vs all time
		$minBegin = \min(\array_column($data, 'begin'));
		$maxEnd = \max(\array_column($data, 'end'));
		$tttTime = \array_sum(\array_column($data, 'duration'));
		$fullTime = $maxEnd - $minBegin;

		$output->print(array(
			self::createBar($sums),
			self::createBar(array(
				'TTT' => $tttTime,
				'' => $fullTime - $tttTime
			))
		));
	}

	private static function createBar(array $data) : string {
		if(empty($data)){
			return "";
		}

		// determine terminal width
		$lineLength = \Utilities::getTerminalColumns()-1;

		// calculate length for each category
		$all = \array_sum($data);
		$lengths = array();
		foreach($data as $name => $d){
			$ln = \intval(\floor(($d / $all) * $lineLength));
			$key = \strlen($name)+1 <= $ln ? $name : "";
			if( !isset($lengths[$key]) ){
				$lengths[$key] = 0;
			}
			$lengths[$key] += $ln;
		}
		\arsort($lengths, SORT_NUMERIC);

		// deal with zero length fields
		$zeros = \array_reduce($lengths, function ($c, $i){
			return $c + ( $i === 0 ? 1 : 0 );
		}, 0);
		while($zeros > 0){
			foreach($lengths as $name => &$d){
				if($d > \strlen($name) + 1){
					$d--; $zeros--;
				}
				if($zeros === 0){
					break;
				}
			}
		}

		// draw chart
		$c = \str_repeat("-", $lineLength+1) . PHP_EOL;
		$fullLength = 0;
		foreach($lengths as $name => $d){
			$c .= '|' . \str_pad($name, $d - 1, " ", STR_PAD_BOTH);
			$fullLength += $d < 1 ? 1 : $d;
		}
		$c .= \str_repeat(" ", $lineLength > $fullLength ? $lineLength - $fullLength : 0) . ($lineLength >= $fullLength ? '|' : '' ) . PHP_EOL;
		$c .= \str_repeat("-", $lineLength+1);
		return $c;
	}
}
?>