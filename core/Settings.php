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
			case "edit":
			case "e":
				$this->edit(array_slice($commands, 1));
				break;
			default:
				$this->commandList();
		}
	}

	private function categories(array $commands) : void {
		$this->output->print(array(
			'Settings -> Categories'
		));
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
					$clist = array();
					foreach( $cats as $id => $cat ){
						$clist[] = array(
							'ID' => strval($id), 
							'Category' => $cat
						);
					}
					$this->output->table($clist);
					
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
					$clist = array();
					foreach( $cats as $id => $cat ){
						$clist[] = array(
							'ID' => strval($id), 
							'Category' => $cat
						);
					}
					$this->output->table($clist);
				}
				return;
			}
		}
		$this->output->print(array(
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
		$this->output->print(array(
			'Settings -> Merge'
		));
			
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

	private function edit(array $commands) : void {
		$this->output->print(array(
			'Settings -> Edit'
		));
		if( isset($commands[0]) && preg_match( StatsData::DATE_PREG, $commands[0]) === 1 ) {
			$finame = $commands[0];
			if( is_file( Config::getStorageDir() . '/' . $finame . '.json' ) ){
				$s = Config::getStorageReader($finame);

				$id = null;
				do {
					if( $id !== null ){
						$this->output->print(array("Edit Task " . CLIOutput::colorString( strval($id), CLIOutput::YELLOW) . ':',
							array(
								'Name:     ' . CLIOutput::colorString( $s->getValue([$id, 'name']), CLIOutput::YELLOW),
								'Category: ' . CLIOutput::colorString( $s->getValue([$id, 'category']), CLIOutput::YELLOW),
								'Duration: ' . CLIOutput::colorString( Stats::secToTime( $s->getValue([$id, 'end']) - $s->getValue([$id, 'begin']) ) , CLIOutput::YELLOW),
							)
						), null, 1);
						$this->editSingle($id, $s);
					}
					
					$list = array();
					foreach($s->getArray() as $id => $d){
						$list[] = array(
							'ID' => strval($id),
							'Name' => $d['name'],
							'Category' => $d['category'],
							'Duration' => Stats::secToTime( $d['end'] - $d['begin'] )
						);
					}
					$this->output->table($list);
					$id = $this->output->readline("Give ID of task to edit, else exit:", null, 1 );
				}
				while( $s->isValue([$id]) );
				$this->output->print(array("Exit!"), CLIOutput::YELLOW, 1);
			}
			else{
				$this->output->print(array(array(
					'No tasks found for this day.'
				)), CLIOutput::RED );
			}
		}
		else {
			$this->output->print(array(array(
				'Please give the day containing the tasks to edit.',
				array( 'e.g. ' . CLIOutput::colorString( "ttt conf edit 2020-01-20", CLIOutput::GREEN) )
			)), CLIOutput::RED );
		}
	}

	private function editSingle(int $id, JSONReader $s) : void {
		if( $this->output->readline("Delete task (y/n)?", CLIOutput::RED, 1) === 'y' ){
			$s->setValue([$id], null);
			$this->output->print(array('Deleted ' . $id . '!' ), CLIOutput::RED, 2);			
		}
		else{
			$this->output->print(array("For all upcoming, leave empty to remain unchanged."), CLIOutput::BLUE, 1);
			
			$name = trim($this->output->readline("Give a new name:", null, 1));
			if( !empty( $name ) ){
				$s->setValue([$id, 'name'], $name);
			}

			$category = trim($this->output->readline("Give a new category (type a name, not an ID):", null, 1));
			if( !empty( $category ) ){
				$s->setValue([$id, 'category'], $category);
			}

			$time = trim($this->output->readline("Give a new duration (will change the end time; format e.g. 1h10m, 10m, 1h):", null, 1));
			if( !empty( $time ) && preg_match('/^(\d+h)?(\d+m)?$/', $time, $matches) === 1 ){
				$hs = 0;
				$mins = 0;
				if(isset($matches[1])){ // Gruppe 3, d.h. Stundenangabe
					$hs = intval(substr($matches[1], 0, -1));
				}
				if(isset($matches[2])){ // Gruppe 3, d.h. Minutenangabe
					$mins = intval(substr($matches[2], 0, -1));
				}
				$s->setValue([$id, 'end'], $s->getValue([$id, 'begin']) + 3600 * $hs + 60 * $mins);
			}
		}
	}

	private function commandList() : void {
		$this->output->print(array(
			'Settings',
			array(
				'List of all commands for settings',
				array(
					CLIOutput::colorString( "cats, categories, c ", CLIOutput::GREEN ) . CLIOutput::colorString( "del| list| add", CLIOutput::YELLOW ),
					array( CLIOutput::colorString( "Edit list of available categories", CLIOutput::BLUE) ),
					CLIOutput::colorString( "merge", CLIOutput::GREEN ),
					array( CLIOutput::colorString( "Merge two tasks into one (rename one)", CLIOutput::BLUE) ),
					CLIOutput::colorString( "edit, e ", CLIOutput::GREEN ) . CLIOutput::colorString( "2020-01-20", CLIOutput::YELLOW ),
					array( CLIOutput::colorString( "Edit the logged tasks for a given day", CLIOutput::BLUE) )
				)
			)
		));
	}
}
?>