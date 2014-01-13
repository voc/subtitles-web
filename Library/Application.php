<?php
	
	/**
	 * Application
	 * 
	 * @author Jannes Jeising, Jonathan Alainis
	 * @version $Id: Application.php 713 2013-11-24 23:53:05Z j.jeising $
	 */
	
	function requires() {
		static $_index = array();
		
		foreach (func_get_args() as $file) {
			if (isset($_index[$file])) {
				continue;
			}
			
			if ($file[0] == '/') {
				require APPLICATION . $file . '.php';
			} else {
				require LIBRARY . $file . '.php';
			}
			
			$_index[$file] = true;
		}
	}
	
	function requiresSession() {
		if (session_status() !== PHP_SESSION_NONE) {
			return;
		}
		
		session_start();
	}
	
	class NotImplementedException extends LogicException { }
	
	function mb_ucwords($string) {
	    return mb_convert_case($string, MB_CASE_TITLE);
	}

	function mb_ucfirst($string) {
		return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
	}
	
?>