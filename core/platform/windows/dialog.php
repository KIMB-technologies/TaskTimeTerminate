<?php
function olamundo()
{
print "Hello word\n";
Gtk::main_quit();
}
$window = new GtkWindow();
$button = new GtkButton('Hello word!');
$button->connect('clicked', 'olamundo');

$window->add($button);

$window->show_all();
Gtk::main();

?>