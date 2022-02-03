<?php
/**
 * Class with useful functions.
 */
class Utilities {

	const VERSION = 'v1.1.3';

	const DEFAULT_LINE_LENGTH = 125;

	/**
	 * OS Consts
	 */
	const OS_MAC = "mac";
	const OS_WIN = "win";
	const OS_LINUX = "lin";
	const OS_OTHER = "oth";
	const OS_TELEGRAM = "tel";

	/**
	 * Possible chars for:
	 */
	const ID = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';
	const CODE = 'abcdefghijklmnopqrstuvwxyz01234567890';
	const CAPTCHA = 'abcdefghjkmnpqrstuvwxyz23456789';

	/**
	 * Checks if a String is a valid file-name (file only, no dirs)
	 * @param $name the filename
	 */
	public static function checkFileName($name){
		return is_string($name) && preg_match( '/^[A-Za-z0-9]+$/', $name ) === 1;
	}

	/**
	 * Does some optimizing on the give string to output it for html display
	 * 	nl2br and htmlentities
	 * @param $cont the string to optimized
	 */
	public static function optimizeOutputString($cont){
		return nl2br( htmlentities( $cont, ENT_COMPAT | ENT_HTML401, 'UTF-8' ));
	}

	/**
	 * Validates a string by the given rex and cuts lenght
	 * 	**no boolean return**
	 * @param $s the string to check
	 * @param $reg the regular expressions (/[^a-z]/ to allow only small latin letters)
	 * @param $len the maximum lenght
	 * @return the clean string (empty, if other input than string or only dirty characters)
	 */
	public static function validateInput($s, $reg, $len){
		if( !is_string($s) ){
			return '';
		}
		return substr(trim(preg_replace( $reg, '' , $s )), 0, $len);
	}

	/**
	 * Generates a random code
	 * @param $len the code lenght
	 * @param $chars the chars to choose of (string)
	 * 	e.g. consts POLL_ID, ADMIN_CODE
	 */
	public static function randomCode( $len, $chars ){
		$r = '';
		$charAnz = strlen( $chars );
		for($i = 0; $i < $len; $i++){
			$r .= $chars[random_int(0, $charAnz-1)];
		}
		return $r;
	}

	/**
	 * Determine the columns of terminal
	 * @return the number of columns or the default value (margin already subtracted)
	 */
	public static function getTerminalColumns() : int {
		if( self::getOS() === self::OS_LINUX || self::getOS() === self::OS_MAC || self::getOS() === self::OS_TELEGRAM ){
			$value = shell_exec('stty size');
			if($value !== null ){
				$value = explode(' ', $value);
				return intval($value[1]) - 3; // columns are second int
			}
		}
		return self::DEFAULT_LINE_LENGTH - 3;
	}

	/**
	 * Get the OS running on.
	 * @return one of OS_MAC, OS_WIN, OS_LINUX, OS_OTHER, OS_TELEGRAM
	 */
	public static function getOS() : string {
		if( is_file(Config::getStorageDir()  . '/telegram.json') ){
			return self::OS_TELEGRAM;
		}
		$os = php_uname('s');
		if( stripos($os, 'darwin') !== false ){
			return self::OS_MAC;
		}
		else if( stripos($os, 'linux') !== false ){
			return self::OS_LINUX;
		}
		else if( stripos($os, 'windows') !== false ){
			return self::OS_WIN;
		}
		else{
			return self::OS_OTHER;
		}
	}

	public static function isWindowsOS() : bool {
		return stripos(php_uname('s'), 'windows') !== false ;
	}

	/**
	 * Determines if a host is online.
	 * @param $url give a URI/ URL of the host (supports http(s))
	 */
	public static function isOnline(string $url) : bool {
		preg_match('/^http(s?):\/\/([^:\/]+)((?::\d+)?)(?:\/.*)?$/', $url, $matches);
		$host = $matches[2];
		if(!empty($matches[1])){ // http_s_??
			$host = 'ssl://' . $host;
			$port = 443;
		}
		else{
			$port = 80;
		}
		if(!empty($matches[3])){ // different port?
			$port = substr($matches[3], 1);
		}
	
		$r = @fsockopen( $host, $port, $errno, $errstr, 2);
		if( is_resource($r) ){
			fclose($r);
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * Checks if given variable is a socket.
	 * @param mixed $s the var to check
	 * @return bool is socket?
	 */
	public static function isSocketType($s) : bool {
		if( class_exists('Socket') && version_compare(PHP_VERSION, '8.0.0', '>=') ){
			return $s instanceof Socket;
		}
		else{
			return is_resource($s);
		}
	}
}

?>
