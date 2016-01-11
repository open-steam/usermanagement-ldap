<?php
	include_once 'interface.ExternalConfiguration.php';
	/*
	 * Eine Instanz dieser Klasse importiert die in der Datei "config.php" gespeicherten Konfigurationsdaten
	 * des LDAP-Servers und stellt Funktionen zum Lesen dieser Daten bereit.
	 */
	class Configuration implements ExternalConfiguration{
		// Hostname des LDAP-Servers.
		static $host;
		
		// Portnummer des LDAP-Servers.
		static $port;
		
		// Hat den Wert TRUE, falls eine SSL-Verbindung zwischen Client und LDAP-Server erstellt werden soll,
		// ansonsten FALSE.
		// WICHTIG: Das Erstellen einer SSL-Verschlüsselten Verbindung ist in dieser Prototyp-Version noch nicht enthalten!
		static $enableSSL;
		
		// Das Root-Verzeichnis des LDAP-Servers.
		static $root;

		// Loginname des benötigten Zugangs auf den LDAP-Server für die Programmlogik der Benutzerverwaltung.
		static $systemLogin;
		
		// Entsprechendes Passwort.
		static $systemPassword;
		
		// Das Attribut, durch das die Gruppenmitgliedschaft festgelegt wird
		static $membership;
		
		
		/*
		 * Konstruktor: Einlesen der Daten aus der 'config.php' und Speichern in den Klassenvariablen.
		 */
		public function __construct(){
			include_once 'config.php';
			if(isset($host)) $this->host = $host;
			if(isset($port)) $this->port = $port;
			if(isset($enableSSL)) $this->enableSSL = $enableSSL;
			if(isset($root)) $this->root = $root;
			if(isset($systemLogin)) $this->systemLogin = $systemLogin;
			if(isset($systemPassword)) $this->systemPassword = $systemPassword;
			if(isset($membership)) $this->membership = $membership;
		}
		
		// Folgende Funktionen dienen lediglich zum Ausgeben der Klassenvariablen.
		public function getRoot(){
			return $this->root;
		}
		public function getHost(){
			return $this->host;
		}
		public function getSystemLogin(){
			return $this->systemLogin;
		}
		public function getSystemPassword(){
			return $this->systemPassword;
		}
		public function getPort(){
			return $this->port;
		}
		public function issetSSL(){
			return $this->enableSSL;
		}
		public function getMemberAttribute(){
			return $this->membership;
		}
	}
?>