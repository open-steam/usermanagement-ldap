<?php
	include_once 'interface.CommandResolver.php';

	class GuiCommandResolver implements CommandResolver{
	
		// Pfad zu dem Verzeichnis, in dem die Command-Klassen gespeichert sind.
		private $path;
		
		// Dieses Command-Objekt wird dann instaniziiert, wenn beim Request kein Command-Parameter
		// übergeben wurde, oder zu dem angegebenen Command-Parameter keine entsprechende Command-Klasse
		// gefunden wurde.
		private $defaultCommand;
		
		/*
		 * Konstruktor
		 * 
		 * $path:			Pfad zu den Command-Klassen.
		 * $defaultCommand:	Name des Standard-Commands.
		 */
		public function __construct($path, $defaultCommand){
			$this->path = $path;
			$this->defaultCommand = $defaultCommand;
		}
		
		/*
		 * Ermittelt anhand des im Request übergebenen Parameters 'cmd' den Namen der benötigten
		 * Command-Klasse, und gibt eine Instanz dieser Klasse zurück.
		 * 
		 * $request:	Request-Objekt.
		 * 
		 * return:		Instanz der benötigten Command-Klasse.
		 */
		public function getCommand(Request $request){ 
			if($request->issetParameter('cmd')){
				$cmdName = $request->getParameter('cmd');
				$command = $this->loadCommand($cmdName);
				if($command instanceof Command) return $command;
			}
			$command = $this->loadCommand($this->defaultCommand);
			return $command;
		}
		
		/*
		 * Führt die Einbindung des benötigten PHP-Codes der Command-Klasse und deren Instanziierung durch.
		 * 
		 * $cmdName:	Name der zu instanziierenden Command-Klasse.
		 * 
		 * return:		Instanz der Command-Klasse.
		 */
		protected function loadCommand($cmdName){
			$class = $cmdName.'Command';
			$file = 'commands/gui/'.$cmdName.'Command.php';
			
			if(!file_exists($file)) return false;
			include_once $file;
			if(!class_exists($class)) return false;
			$command = new $class;
			return $command;
		}
	}
?>