<?php

/**
 * Echos the users input as JSON
 */
function sendInput( $cat, $name, $time, $pause = false ){
	echo json_encode(array(
		"pause" => $pause,
		"cat" => $cat,
		"name" => $name,
		"time" => $time
	));
	Gtk::main_quit();
	die();
}

/**
 * Get the array of categories 
 * 	.../php.exe ..../dialog.php -cats Test,TTT,UUU
 */
function getInput(){
	global $argc, $argv;
	if( $argc >= 3){
		$cats = $argv;
		do {
			$cats = array_slice($cats, 1);
		} while( trim($cats[0]) !== '-cats' );
		$catlist = array_slice($cats, 1);
		if( !empty($catlist)){
			return explode(",", $catlist);
		}
	}
}

/**
 * GTK Window
 */
$window = new GtkWindow();
$window->set_title('TaskTimeTerminate');      
$window->connect_simple('destroy', function (){
	sendInput( "", "", "", true );
}); 

$fixed = new GtkFixed();

$label = new GtkLabel("It is time for a new task!");
$fixed->put($label, 10, 10);

$dropdown = GtkComboBox::new_text();
foreach( getInput() as $c ){
	$dropdown->append_text($c);
}
$fixed->put($dropdown, 10, 30);

$task = new GtkEntry("Task");
$fixed->put($task, 10, 50);

$time = new GtkEntry("Time");
$fixed->put($time, 10, 70);

$timelI = new GtkLabel("Time can be a duration like 2h, 2h10m, 25m or time like 12:00.");
$fixed->put($timelI, 10, 90);
$timelII = new GtkLabel("The time is seen as a limit, if reached this dialog will show up again.");
$fixed->put($timelII, 10, 110);

$start = new GtkButton('Start');
$fixed->put($start, 10, 120);
$start->connect_simple('clicked', function (){
	sendInput( $dropdown->get_active_text(), $task->get_text(), $time->get_text() );
});

$pause = new GtkButton('Pause');
$fixed->put($pause, 100, 120);
$pause->connect_simple('clicked', function (){
	sendInput( "", "", "", true );
});

$window->add($fixed); 
$window->set_default_size(200, 200); 
$window->set_position(GTK::WIN_POS_CENTER);
$window->show_all();      
Gtk::main();
?>