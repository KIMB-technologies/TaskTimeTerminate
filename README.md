> Alpha Version, only works for Linux until now!  
> MacOS and Windows will be added soon
# TaskTimeTerminate

> A Tool to record timings for tasks per category and rembering to terminate work sessions.

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

## Setup
Use the [`install.sh`](https://raw.githubusercontent.com/KIMB-technologies/TaskTimeTerminate/master/install.sh) (for linux and mac) or follow the steps:

1. Install PHP 7.4 (only the CLI component is needed)
    - On Linux install `yad` for dialog windows
    - On Mac OS install `xxxxx` for dialog windows
    - No Support for Windows until now (but different dialog-windows are easy to add!)
3. Download this repository, either via git or as zip and save to a folder
4. Make executable `chmod +x ./record.php ./cli.php`
5. Start `./cli.php` and add categories (e.g. Hobby, Work, Musts)
    - `./cli.php conf cats`
6. Setup an autostart for `./record.php`
    - The system needs a background process to check for limits and timeouts and to open dialogs
7. Add a terminal shortcut to the `cli.php`
    - e.g. `echo "alias ttt='/home/user/my-full-path/to/cli.php'" >> ~/.bashrc`
8. Add Times and Tasks while working
9. Show stats `ttt s today`

### Data and Update
Per default all data is saved in `~/.tasktimeterminate/`. This can be changed by editing the `config.json` in the programs 
root folder.

The program folder (not the data folder) can be deleted and replaced by a newer version. (If downloaded via git `git pull` should work.)