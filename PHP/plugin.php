<?php
/**
 * plugin.php
 */

class plugin{
	
	/**
	 * constructor
	 */
	
	public function __construct(){
		return self::__load();
	}
	
	/**
	 * loading plugins
	 */
	
	public function __load(){
		print "loading plugins";
	}
	
	public function __destruct(){
		print "Destructing class";
	}
}
?>