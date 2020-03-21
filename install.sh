#!/bin/sh

# check for php 7.4
if ! command -v php > /dev/null; then
	echo "PHP not found!";
	echo "Install PHP 7.4"
	exit;
fi;

if ! php -i | grep -q "7.4" ; then
	echo "PHP is not version 7.4!"
	exit;
fi; 

# check for yad if linx
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

# git init
if [ ! -d .git ]; then 
	git init > /dev/null
	git remote add origin https://github.com/KIMB-technologies/TaskTimeTerminate.git > /dev/null
fi;

# download/ update
git checkout -- . 
git pull origin master 
chmod +x ./cli.php ./record.php

# add to shell
if [ -f ~/.bashrc ]; then 
	if ! grep -q "alias ttt" ~/.bashrc; then 
		echo "alias ttt='$(pwd)/cli.php'" >> ~/.bashrc
	fi;
fi;
if [ -f ~/.zshrc ]; then 
	if ! grep -q "alias ttt" ~/.zshrc; then 
		echo "alias ttt='$(pwd)/cli.php'" >> ~/.zshrc
	fi;
fi;

echo "================================================="
echo "TaskTimeTerminate by KIMB-technologies           "
echo "	Installation/ Update successful!           "
echo "                                                 "
echo "Please restart your shell, afterwards the 'ttt'  "
echo "command can be used to acess TTT's cli interface."
echo "                                                 "
echo "Make sure to start the background job on login!  "
echo "    $(pwd)/record.php                            "
echo "================================================="
