<?php
class Settings {

	private CLIParser $parser;
	private CLIOutput $output;

	public function __construct(CLIParser $parser, CLIOutput $output) {
		$this->parser = $parser;
		$this->output = $output;

		$this->parseCommands($this->parser->getCommands());
	}

	private function parseCommands(array $commands) : void {
		switch( $commands[0] ) {
			case "cats":
			case "categories":
			case "c":
				$this->categories(array_slice($commands, 1));
				break;
			case "merge":
				$this->merge();
				break;
			default:
				$this->commandList();
		}
	}

	private function categories(array $commands) : void {
		if( isset($commands[0])){
			$cmd = $commands[0];
			if( in_array($cmd, ['add', 'del', 'list'])){
				$cats = StatsData::getAllCategories();
				$r = Config::getStorageReader('config');
				if($cmd == 'add'){
					$color = null;
					do {
						if( $color == CLIOutput::RED){
							$this->output->print(array("Please check input, can't create categories twice, check allowed chars!"), $color ,1);
						}
						$newcat = $this->output->readline("Name of category to add [Only A-Z, a-z and -]:", $color, 1);
						$color = CLIOutput::RED;
					} while (!InputParser::checkCategoryInput($newcat) || in_array($newcat, $cats));
					$cats[] = $newcat;
					if($r->setValue(['categories'], $cats)){
						$this->output->print(array("Added '". $newcat ."'"), CLIOutput::GREEN, 1);
					}
					else{
						$this->output->print(array("Unable to add '". $newcat ."'"), CLIOutput::RED, 1);
					}
				}
				else if ($cmd == 'del'){
					$clist = array("ID\t:\tCategory");
					foreach( $cats as $id => $cat ){
						$clist[] = $id . "\t:\t" . $cat;
					}
					$this->output->print($clist, null, 2);
					
					$delid = $this->output->readline("Type ID to delete category, else abort:", CLIOutput::RED, 1);
					if( isset($cats[$delid]) ){
						$delcat = $cats[$delid];
						unset($cats[$delid]);
						if($r->setValue(['categories'], $cats)){
							$this->output->print(array("Deleted '". $delcat ."'"), CLIOutput::GREEN, 1);
						}
						else{
							$this->output->print(array("Unable to delete '". $delcat ."'"), CLIOutput::RED, 1);
						}
					}
					else{
						$this->output->print(array("Abort!"), CLIOutput::YELLOW, 1);
					}
				}
				else {
					$clist = array("ID\t:\tCategory");
					foreach( $cats as $id => $cat ){
						$clist[] = $id . "\t:\t" . $cat;
					}
					$this->output->print($clist, null, 1);
				}
				return;
			}
		}
		$this->output->print(array(
			'Settings -> Categories',
			array(
				'There are more commands for categories',
				array(
					CLIOutput::colorString( "add, del, list", CLIOutput::GREEN ),
					array( CLIOutput::colorString( "Add category, delete category or get a list.", CLIOutput::BLUE) )
				)
			)
		));
	}

	private function merge() : void {
		$s = new StatsData();
		$allnames = $s->getAllNames();
		$color = null;
		do {
			if( $color == CLIOutput::RED){
				$this->output->print(array("Please check input, only used names are allowed!"), $color ,1);
			}
			$merge = $this->output->readline("Name of data to merge into another (the one to rename):", $color, 1);
			$color = CLIOutput::RED;
		} while ( empty($merge) || !in_array($merge, $allnames));

		$color = null;
		do {
			if( $color == CLIOutput::RED){
				$this->output->print(array("Please check input, only used names are allowed!"), $color ,1);
			}
			$mergeTo = $this->output->readline("Name of data to merge into (the new name):", $color, 1);
			$color = CLIOutput::RED;
		} while ( empty($mergeTo) || !in_array($mergeTo, $allnames));
		
		if($s->merge($merge, $mergeTo)){
			$this->output->print(array("Merged '". $merge ."' into '".$mergeTo."'"), CLIOutput::GREEN, 1);
		}
		else{
			$this->output->print(array("Error merging '". $merge ."' into '".$mergeTo."'"), CLIOutput::RED, 1);
		}
	}

	private function commandList() : void {
		$this->output->print(array(
			'Settings',
			array(
				'List of all commands for settings',
				array(
					CLIOutput::colorString( "cats, categories, c", CLIOutput::GREEN ),
					array( CLIOutput::colorString( "Edit list of available categories", CLIOutput::BLUE) ),
					CLIOutput::colorString( "merge", CLIOutput::GREEN ),
					array( CLIOutput::colorString( "Merge two tasks into one (rename one)", CLIOutput::BLUE) )
				)
			)
		));
	}



}
?>