#!/bin/sh

# check for php
if ! command -v php > /dev/null; then
	echo "PHP not found!";
	echo "Please install PHP 7.4 or newer!"
	exit;
fi;

# check php-version
if [ $(php -r "echo version_compare(PHP_VERSION, '7.4', '>=') ? 'ok' : 'error';") = "error" ]; then
	echo "TTT needs PHP version 7.4 or newer!"
	exit;
fi; 

# check for yad if linux
if [ $(uname) = "Linux" ]; then
	if ! command -v yad > /dev/null; then
		echo "yad not found!";
		echo "Install yad (yet another dialog)!"
		exit;
	fi;
fi;

# check for git to use this script
if ! command -v git > /dev/null; then
	echo "git not found!";
	echo "This install script uses git!"
	exit;
fi;

if [ ! -f ./record.php ]; then 
	# where to install
	echo "Where to install TaskTimeTerminate?"
	HOME=$(echo ~);
	if [ $(uname) = "Linux" ]; then
		installpath="$HOME/.tasktimeterminateSrc";
		
	elif [ $(uname) = "Darwin" ]; then
		installpath="$HOME/Applications/TaskTimeTerminate";
	else
		echo "Only Linux and macOS supported by this script!"
		exit;
	fi;
	echo "Install to '$installpath'? Type other absolute path or enter to use suggested."
	read installcust;
	if [ ! $installcust = "" ]; then 
		phpc="php -r 'echo substr(\""${installcust}"\", -1) === \"/\" ? substr(\""${installcust}"\", 0, -1) : \""${installcust}"\";'";
		installpath=$(eval $phpc)
	fi;
else
	echo "Updating TaskTimeTerminate!"
	installpath=$(pwd);
fi;

# create install path and goto
if [ ! -d $installpath ]; then 
	mkdir -p "$installpath";
fi;
cd "$installpath";
if [ ! $(pwd) = $installpath ]; then 
	echo "Error creating/ moving to installpath '$installpath'!"
	exit;
fi;

# git init
if [ ! -d .git ]; then 
	git init > /dev/null
	git remote add origin https://github.com/KIMB-technologies/TaskTimeTerminate.git > /dev/null
fi;

# download/ update
git checkout -- . 
git fetch --tags --quiet
latestTag=$(git describe --tags `git rev-list --tags --max-count=1`)
git checkout "$latestTag" --quiet

chmod +x ./cli.php ./record.php ./install.sh

# add to shell
if [ -f ~/.bashrc ]; then 
	if ! grep -q "alias ttt" ~/.bashrc; then 
		echo "alias ttt='\"$(pwd)/cli.php\"'" >> ~/.bashrc
	fi;
	if ! grep -q "alias ttt-update" ~/.bashrc; then 
		echo "alias ttt-update='cd \"$(pwd)\" && ./install.sh'" >> ~/.bashrc
	fi;
fi;
if [ -f ~/.zshrc ]; then 
	if ! grep -q "alias ttt" ~/.zshrc; then 
		echo "alias ttt='\"$(pwd)/cli.php\"'" >> ~/.zshrc
	fi;
	if ! grep -q "alias ttt-update" ~/.zshrc; then 
		echo "alias ttt-update='cd \"$(pwd)\" && ./install.sh'" >> ~/.zshrc
	fi;
fi;

# setting up TTTd.app on macOS
if [ $(uname) = "Darwin" ]; then 
	if [ -d "$(pwd)/TTTd.app" ]; then
		if [ "$(xattr "$(pwd)/TTTd.app")" = "com.apple.quarantine" ]; then 
			xattr -d com.apple.quarantine "$(pwd)/TTTd.app"
			echo "Removed quarantine lock on TTTd.app!"
		fi;
	fi;
fi;

echo "========================================================="
echo "TaskTimeTerminate by KIMB-technologies                   "
echo "	Installation/ Update successful!                   "
echo "                                                         "
echo "Please restart your shell, afterwards the 'ttt'          "
echo "command can be used to acess TTT's cli interface.        "
echo "	(Works only if bash or zsh shell used.)             "
echo "                                                         "

if [ $(uname) = "Linux" ]; then 
	echo "[Desktop Entry]" > ~/.config/autostart/TaskTimeTerminate.desktop
	echo "Type=Application" >> ~/.config/autostart/TaskTimeTerminate.desktop
	echo "Name=TaskTimeTerminate" >> ~/.config/autostart/TaskTimeTerminate.desktop
	echo "Exec=$(command -v php) $(pwd)/record.php" >> ~/.config/autostart/TaskTimeTerminate.desktop
	echo "X-GNOME-Autostart-Delay=10" >> ~/.config/autostart/TaskTimeTerminate.desktop

	echo "We have created an autostart into '~/.config/autostart/' "
else
	echo "Make sure to start the background job on login!          "
	echo "    $(pwd)/record.php                                    "
	echo "On macOS see the Readme.md for instructions how to add   "
	echo "TTTd.app to Login Objects.                               "
fi;

echo "                                                         "
echo "Please add categories first 'ttt c cats add' afterwards  "
echo "logout and login again to enable background job.         "
echo "========================================================="

echo ""
echo "Try to (Re)Start background job? (y/n)"
read restart
if [ $restart = "y" ]; then 
	if [ $(uname) = "Linux" ]; then 
		php "$(pwd)/record.php" & # (re)start php background job
		echo "	Started background job!"
	elif [ $(uname) = "Darwin" ]; then
		if [ -d ~/Applications/TaskTimeTerminate/TTTd.app ]; then 
			open ~/Applications/TaskTimeTerminate/TTTd.app;
			echo "	Started background job!"
		else
			echo "	Unable to find Starter at '~/Applications/TaskTimeTerminate/TTTd.app!"
		fi;
	fi;
fi;
echo ""