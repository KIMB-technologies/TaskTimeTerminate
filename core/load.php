<?php
// Autoloader
spl_autoload_register(function ($class) {
	if( is_string($class) && preg_match( '/^[A-Za-z0-9]+$/', $class ) === 1 ){
		$candidates = array(
			__DIR__ . '/',
			__DIR__ . '/platform/'
		);
		foreach( $candidates as $cand ){
			$classfile = $cand . $class . '.php';
			if( is_file($classfile) ){
				require_once( $classfile );
				break;
			}
		}
	}
});
?>