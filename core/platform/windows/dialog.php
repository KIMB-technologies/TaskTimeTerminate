<?php

// Autocomplete
require_once(__DIR__ . '/socket.php');
//	getCompletion($prefix) => array('A', 'AA', 'AAB')

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
 * 	.../php.exe ..../dialog.php -cats Test,TTT,UUU -lastcat TTT -lasttask Test
 */
$lastCatIndex = 0;
$lastTask = "";
function getInput(){
	global $argc, $argv, $lastCatIndex, $lastTask;

	$catList = array();
	$lastCat = null;

	if( $argc >= 3 ){
		foreach( $argv as $key => $value ) {
			switch ( $value ) {
				case "-cats":
					$catList = explode(",", $argv[$key+1]);
					break;
				case "-lastcat":
					$lastCat = $argv[$key+1];
					break;
				case "-lasttask":
					$lastTask = $argv[$key+1];
					break;
				default:
					break;
			}
		}	
	}
	if( $lastCat !== null ){
		$lastCatIndex = array_search($lastCat, $catList);
	}

	return $catList;
}

/**
 * GTK Window
 */
$window = new GtkWindow();
$window->set_title('TaskTimeTerminate');      
$window->connect_simple('destroy', function (){
	sendInput( "", "", "", true );
}); 

// image (load only if loading possible on system)
try {
	$window->set_icon(
		GdkPixbuf::new_from_file(
			realpath( __DIR__ . '/../../icon/icon.png' )
		)
	);
} catch (Exception $e) {
	// proceed without icon
}

$fixed = new GtkFixed();

$label = new GtkLabel("It is time for a new task!");
$fixed->put($label, 10, 10);

$fixed->put(new GtkLabel("Category"), 10, 40);
$dropdown = GtkComboBox::new_text();
foreach( getInput() as $c ){
	$dropdown->append_text($c);
}
$fixed->put($dropdown, 90, 40);
$dropdown->set_active($lastCatIndex);

$fixed->put(new GtkLabel("Task"), 10, 70);
$task = new GtkEntry($lastTask);
$fixed->put($task, 90, 70);
$task->connect('key-press-event', function ($w, $e) use (&$task, &$lastTask){
	// http://gtk.php.net/manual/en/appendix.keysyms.php
	if( in_array($e->keyval, array(Gdk::KEY_BackSpace, Gdk::KEY_Clear)) && $task->get_text() === $lastTask ){
		$task->set_text('');
	}
});

$completion = new GtkEntryCompletion();
$completion->set_text_column(0);
$model = new GtkListStore(GObject::TYPE_STRING);
$completion->set_model($model); 
$task->set_completion($completion);
$task->connect_simple('key-release-event', function () use (&$task, &$model) {
	$model->clear();
	foreach(getCompletion($task->get_text()) as $c){
		$model->append(array($c)); 
	}
});

$fixed->put(new GtkLabel("Time"), 10, 100);
$time = new GtkEntry();
$fixed->put($time, 90, 100);

$timelI = new GtkLabel("Time can be a duration like 2h, 2h10m, 25m or time like 12:00.");
$fixed->put($timelI, 10, 130);

$timelII = new GtkLabel("The time is seen as a limit, if reached this dialog will show up again.");
$fixed->put($timelII, 10, 150);

$start = new GtkButton('Start');
$fixed->put($start, 100, 180);
$start->connect_simple('clicked', function () use (&$dropdown, &$task, &$time ){
	sendInput( $dropdown->get_active_text(), $task->get_text(), $time->get_text() );
});

$pause = new GtkButton('Pause');
$fixed->put($pause, 10, 180);
$pause->connect_simple('clicked', function (){
	sendInput( "", "", "", true );
});

$window->add($fixed); 
$window->set_default_size(400, 220); 
$window->set_position(GTK::WIN_POS_CENTER);
$window->show_all(); 
Gtk::main();
?>