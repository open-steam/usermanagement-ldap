<?php
	/*
	 * Eine Instanz dieser Klasse speichert Daten, welche während der gesamten Session erhalten bleiben müssen.
	 * 
	 * Diese Klasse ist nach dem sog. 'Singleton-Pattern' aufgebaut. Dies bedeutet, dass man  nicht durch
	 * den 'new-Operator' Instanzen erzeugt, sondern durch den Befehl: $sessionRegistry = SessionRegistry::getInstance();
	 */
	class SessionRegistry extends Registry{
	
		// Instanz dieser Klasse.
		protected static $instance = null;
		
		/*
		 * Erzeugt eine Instanz dieser Klasse und gibt diese zurück. Existiert bereits eine solche Instanz,
		 * wird lediglich eine Referenz auf diese zurückgegeben.
		 */
		public static function getInstance(){
			if(self::$instance === null) self::$instance = new SessionRegistry();
			return self::$instance;
		}
		
		/*
		 * Konstruktor
		 */
		protected function __construct(){
			session_start();
			if(!isset($_SESSION['__registry'])) $_SESSION['__registry'] = array();
		}
		
		// Speichern von Daten
		public function set($key, $value){
			$_SESSION['__registry'][$key] = $value;
		}
		
		// Abfragen von Daten.
		public function get($key){
			if(isset($_SESSION['__registry'][$key])) return $_SESSION['__registry'][$key];
			return null;
		}
	}
?>