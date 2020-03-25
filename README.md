> Alpha Version  
> Support for **Linux**, **macOS** and **Windows**

# TaskTimeTerminate

> A Tool to record timings for tasks per category and remembering to terminate work sessions.

## About
There are many different task to do with a computer. At the end of the day one sometimes does not know what
one has done today. If working on projects as business one has to log the hours per project, too.

> Where has all the time gone?  
> What did I do all the time?


This tool tries to solve both problems at once:
1. The Tools opens a dialog on computer startup and asks for the current/ upcoming task:
    - Name of the task
    - Category of the task
    - Planned time to work on the task
2. If the planned time is over, the tools asks again for the next task.
    - So the tool logs the used time per task and category
    - Also the tools limits the time per task and reminds to do other things
3. And so on ....

- Of course
    - One can continue the same task after the limit (the next dialog is just a reminder)
    - One can pause the tool
- Planned
    - Sync. stats and categories about multiple computers

## Usage
If a dialog opens, give a name for the current task and choose a category. Also give a time limit,
this can be a absolute value like `12:00` or `22:30` or a relative one like `1h`, `20m` or 
`1h10m`. The system will open the next dialog after the time limit was reached. 
When the dialog opens and you need additional time for the last task, you can write a relative time value 
like `5m` and prepend a `+` without giving a name. So only typing `+5m` will give you five minutes more
for your last task.  
You can also shutdown the PC (the task will end with the shutdown).

If you don't know what your current task ist, try the overview at `ttt o`.

If you need a short break, you can use the button `Pause` in the dialog (will be around 1 minute).
If you need a longer break, you can disable the tool by executing `ttt t` and enable afterwards with
another `ttt t`. 

If you have to stop your current task before the limit is reached, you can run `ttt new`
to start a new task (will open dialog).

The statistics can be printed using `ttt s`, which will show daily stats by default.
Other stat-commands for different periods and filters:
- `ttt s -names Website`
- `ttt s day -names Website,SocialMedia`
- `ttt s week -cats Work,Hobby`
- `ttt s month -cats Hobby -names Website`
- `ttt s all`

## Setup
The tool supports Linux (like Ubuntu, Linux Mint), macOS and Windows.
We have tested it under Linux Mint 18, macOS Catalina and Windows 10.

### General Installation
We have an install and update script for macOS and Linux &ndash; [`install.sh`](https://raw.githubusercontent.com/KIMB-technologies/TaskTimeTerminate/master/install.sh).

The script will follow these steps (for detailed information per operating system see below).
1. Check the installation of PHP 7.4
2. Ask the user where to install
3. Use git to download the programs repository
4. Add the `ttt` command to the shell
5. Create an background job if on Linux

### Manual Installation
1. Install PHP 7.4 (only the CLI component is needed)
    - On Linux install `yad` for dialogs
    - On Windows PHP-GTK will be used (and downloaded on first run of `./cli.php r`)
    - On macOS the dialog is a native program bundle in this repository
2. Download this repository, either via [git](https://github.com/KIMB-technologies/TaskTimeTerminate.git)
	or as [zip](https://github.com/KIMB-technologies/TaskTimeTerminate/archive/master.zip) and save to a folder
3. Make executable `chmod +x ./record.php ./cli.php`
4. Setup an autostart for `./record.php`
    - The system needs a background process to check for limits and timeouts and to open dialogs
5. Add a terminal shortcut to the `cli.php`
    - e.g. `echo "alias ttt='/home/user/my-full-path/to/cli.php'" >> ~/.bashrc`
6. Add categories (e.g. Hobby, Work, Musts)
    - `ttt conf cats add`
    - `./cli.php conf cats add`
7. Start the background job (e.g. logout and login again)
8. Add Times and Tasks while working
9. Show stats `ttt s today`

### Collected Data and Update
Per default all data is saved in `~/.tasktimeterminate/`. This can be changed by editing the
`config.json` in the programs root folder.
On Windows we will use `%AppData%/Roaming` for `~`.

Per default all times use the timezone `Europe/Berlin`. This can be changed by editing the `config.json` in the programs 
root folder.

The program folder (not the data folder) can be deleted and replaced by a newer version.
- If downloaded via git `git pull` will do.
- If used the install script, just rerun it or use the `ttt-update` command.

### Installation per OS
#### Linux
- PHP 7.4
	- `sudo apt-get install php7.4-cli`
	- If there is no package, I recommend the builds of Ondřej Surý
		- Ubuntu/ Linux Mint `add-apt-repository ppa:ondrej/php && sudo apt-get update`
		- Debian see https://packages.sury.org/php/README.txt
		- `sudo apt-get install php7.4-cli` should work now
- YAD
	`sudo apt-get install yad`
#### macOS
- PHP 7.4
	- Until now PHP 7.3. is part of macOS, so `/usr/bin/php` will not work!
	- Install Homebrew https://brew.sh/index
	- Install `brew install php` or `brew install php@7.4`
	- PHP will be installed to `/usr/local/Cellar/php@7.4/*/bin/php` or `/usr/local/Cellar/php/*/bin/php`
		(so use something like this `alias ttt="/usr/local/Cellar/php/*/bin/php /Users/<me>/Applications/TaskTimeTerminate/cli.php`)
- Background Job
	- We will register as *Login Object*
		- Got to *System Preferences &rarr; Users and Groups &rarr; Username &rarr; Login Objects*
		- The repository contains an `TTTd.app` it will start `record.php` as background process
			- `TTTd.app` will only work, if you use Homebrew for the PHP installation
			- Also `TTTd.app` looks for TTT at `~/Applications/TaskTimeTerminate/`
	- Create own Automator Application instead of `TTTd.app`
		- Open Automator.app and open a new Program
		- Select *Execute Shell Command* and `/bin/sh` as Shell
		- Add a command like this in the textbox on the right
		- `/usr/local/Cellar/php/*/bin/php /Users/<me>/Applications/TaskTimeTerminate/record.php &> /dev/null &`
		- Save the Application as `.app` and add to *Login Objects*
### Windows
- PHP 7.4
	- Download prebuilt version from https://windows.php.net/download/#php-7.4
	- Unzip and place somewhere on computer
	- Add to `$PATH` or always run like `C:/my/php/path/php.exe C:/Users/<me>/TaskTimeTerminate/cli.php`
- Add `ttt` alias to `~/macros.doskey`
- Add a script to `%AppData%\Roaming\Microsoft\Windows\Start Menu\Programs\Startup` which
	runs `C:/my/php/path/php.exe C:/Users/<me>/TaskTimeTerminate/record.php`
	- There will be an open Command-Prompt, I don't know how to hide it.