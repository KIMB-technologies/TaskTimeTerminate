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
	if( $argc === 3 && $argv[1] === '-cats'){
		if( !empty($argv[2]) ){
			return explode(",", $argv[2]);
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

$fixed->put(new GtkLabel("Task"), 10, 70);
$task = new GtkEntry();
$fixed->put($task, 90, 70);

$fixed->put(new GtkLabel("Time"), 10, 100);
$time = new GtkEntry();
$fixed->put($time, 90, 100);

$timelI = new GtkLabel("Time can be a duration like 2h, 2h10m, 25m or time like 12:00.");
$fixed->put($timelI, 10, 130);

$timelII = new GtkLabel("The time is seen as a limit, if reached this dialog will show up again.");
$fixed->put($timelII, 10, 150);

$start = new GtkButton('Start');
$fixed->put($start, 10, 180);
$start->connect_simple('clicked', function () use (&$dropdown, &$task, &$time ){
	sendInput( $dropdown->get_active_text(), $task->get_text(), $time->get_text() );
});

$pause = new GtkButton('Pause');
$fixed->put($pause, 100, 180);
$pause->connect_simple('clicked', function (){
	sendInput( "", "", "", true );
});

$window->add($fixed); 
$window->set_default_size(400, 220); 
$window->set_position(GTK::WIN_POS_CENTER);
$window->show_all(); 
Gtk::main();
?>