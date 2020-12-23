#!/bin/bash

# some settings
curr_time=$(date +"%d.%m.%Y %T")
daemon_log="/tmp/TaskTimeTerminateDeamon.log"
search_path=(
	/usr/local/Cellar/php@7*/7.4*/bin/php
	/usr/local/Cellar/php@8*/*/bin/php
	/usr/local/Cellar/php*/*/bin/php
)
php_binary="php"

# look for php
for path in ${search_path[*]}
do
	# get the path
	php_cand=$(echo $path);
	if [ -f "$php_cand" ]; then # check if exsists
		if [ $("$php_cand" -r "echo version_compare(PHP_VERSION, '7.4', '>=') ? 'ok' : 'error';") = "ok" ]; then # check version
			php_binary="$php_cand" # remember found
			break;
		fi;
	fi;
done

# final test
if [ $("$php_binary" -r "echo version_compare(PHP_VERSION, '7.4', '>=') ? 'ok' : 'error';") = "error" ]; then
	echo "Unable to find PHP 7.4 or newer using '$php_binary' at $curr_time" >> $daemon_log;
	exit;
fi;

# start background job
echo "Starting with '$php_binary' at $curr_time" >> $daemon_log;
"$php_binary" ~/Applications/TaskTimeTerminate/record.php &> /dev/null &