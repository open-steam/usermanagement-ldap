<?php
	/*
	 * Durch eine Instanz dieser Klasse erhält man Zugriff auf Objekte, welche für eine persistente Datenspeicherung
	 * oder Datenabfrage zuständig sind, wie z.B. Objekte der Klassen 'LdapAccess' oder 'Configuration'.
	 * 
	 * ACHTUNG: Die in diesem Objekt gespeicherten Daten bleiben nur während eines Requestes erhalten. Um Daten
	 * über mehrere Request hinweg zu speichern, benutzen Sie die SessionRegistry!
	 * 
	 * Diese Klasse ist nach dem sog. 'Singleton-Pattern' aufgebaut. Dies bedeutet, dass man  nicht durch
	 * den 'new-Operator' Instanzen erzeugt, sondern durch den Befehl: $registry = Registry::getInstance();
	 */
	class Registry{
	
		// Instanz dieser Klasse.
		protected static $instance = null;
		
		// Array zum Speichern verschiedener Objekte und Daten.
		protected $values = array();
		
		/*
		 * Erzeugt eine Instanz dieser Klasse und gibt diese zurück. Existiert bereits eine solche Instanz,
		 * wird lediglich eine Referenz auf diese zurückgegeben.
		 */
		public static function getInstance(){
			if(self::$instance === null) self::$instance = new Registry();

			return self::$instance;
		}
		
		/*
		 * Überschreiben des Konstruktors, sodass von dieser Klasse keine Instanz durch Verwenden des
		 * new-Operators erstellt werden kann.
		 */
		protected function __construct(){}
		
		/*
		 * Überschreiben der clone-Funktion, sodass von dieser Klasse keine Instanz durch Verwenden des
		 * clone-Operators erstellt werden kann.
		 */
		protected function __clone(){}
		
		// Speichern von Objekten
		public function set($objectName, $object){
			$this->values[$objectName] = $object;
		}
		
		// Rückgabe von Objekten
		public function get($objectName){
			if(isset($this->values[$objectName])) return $this->values[$objectName];
		}
	}
?>