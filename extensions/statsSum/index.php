<?php

namespace TTTStatsSum;

class StatsSum {
	/**
	 * Called after the stats output to cli is finished, just before ttt stops execution.
	 * Adds the sum of worked hours to the stats view.
	 * 
	 * @param $array the data array from StatsData::getAllDatasets()
	 * @param $output the CLI Output object
	 */
	public static function statsViewed(array $data, \CLIOutput $output) : void {
		$sums = array();
		foreach( $data as $k => $d ){
			if( empty($sums[$d['category']]) ) {
				$sums[$d['category']] = 0;
			}

			$sums[$d['category']] += $d['duration'];
		}

		$tab = array();
		foreach( $sums as $k => $s ){
			$tab[] = array(
				'Category' => $k,
				'Sum' => \Time::secToTime($s)
			);
		}

		$output->table($tab);
		$output->print(array(
			'Total sum: ' . trim(\Time::secToTime(array_sum($sums)))
		), \CLIOutput::RED);
	}
}
?>