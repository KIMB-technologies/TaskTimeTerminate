# Extensions
## General
TaskTimeTerminate has an extension interface. Each extension needs its own folder
in this directory. The name of the folder will be the name of the extension.

Per default each folder in the folder `/extensions/` will be seen as an enabled
extension. Only if a valid `index.json` is provided, TTT will load the extension.

To disable an extension just add the name of the extension to the `/extensions/disabled.txt` file.
Each name has to be in alone in one row.

### Install an Extension
Just add the folder containing the extension to `/extensions/`. If the extension already
exists, check the `disabled.txt` and remove the extension's name there.

Some extensions are distributed through the default TTT-installation, only these will be 
updated together with TTT. Any custom installs have to be updated separately!

### Usage
Most extension provide a cli interface, it can be reached using the `ttt ext <extension name>` 
command. All extensions may use a short-name `ttt <short name>`  for the cli interface, it will
only be reachable if there are no conflicts with TTT's core commands.

Also each extension can attach to events, e.g. it is possible to call some PHP-function each time 
when a dialog is opened.

## Develop Extensions
Developing an extension is as easy as copying two files them in a new folder and adapting them.
Just use the two files from `/extensions/example/`.

### `index.php`
The system will 'require_once' this file to load the extension.
All configuration has to be done here and all callback functions have
to be defined. Of course an autoloader may be used.
It is recommended to use a custom namespace. All core classes
of TTT are in the global namespace.

See the commented example [index.php](example/index.php).
 
### `index.json`
```json
{
	"name" : "Example Extension", // The extensions name
	"shortname" : "ee", // The short name (as described [above](#Usage))
	"events" : { // a list of all events provided by TTT, defining a callback to handle the event
			// one may only list the used events, non existing events or empty callbacks will be ignored
		"newRecordSaved" : "TTTExampleExtension\\ExampleExtension::newRecordSaved",
		"newDialogOpened" : "TTTExampleExtension\\ExampleExtension::newDialogOpened",
		"daemonAfterSleep" : "TTTExampleExtension\\ExampleExtension::daemonAfterSleep",
		"daemonBeforeSleep" : "TTTExampleExtension\\ExampleExtension::daemonBeforeSleep",
		"dayFileSync" : "TTTExampleExtension\\ExampleExtension::dayFileSync",
		"statsViewed" : "TTTExampleExtension\\ExampleExtension::statsViewed",
		"settingsCatsChanged" : "TTTExampleExtension\\ExampleExtension::settingsCatsChanged"
	},
	"cli" : "TTTExampleExtension\\ExampleExtension::cli", // the cli callback, leave empty to disable cli
	"version" : "v0.1.0" // the addons version, shown in 'ttt v'
}
```
More information about the events, the callbacks and parameter can be found in [index.php](extensions/example/index.php).
