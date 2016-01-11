<?php
	/*
	 * Mit Instanzen dieser Klasse lässt sich letztendlich der HTML-Code, mit dem der gewünschte Bereich der
	 * Benutzerverwaltung dargestellt wird, erzeugen. Es werden 'Template-Dateien', welche das HTML-Grundgerüst
	 * enthalten, eingebunden, und mit verschiedenen Daten und Informationen ergänzt.
	 */
	class TemplateView{
	
		// Name des benötigten Templates.
		private $template;
		
		// Dient zur Speicherung verschiedener Daten, welche die PHP-Variablen aus den Template-Dateien ersetzen.
		private $vars = array();
		
		/* 
		 * Konstruktor
		 * 
		 * $template: Name der Template-Datei, welche eingebunden werden soll.
		 */
		public function __construct($template){
			$this->template = $template;
		}
		
		/*
		 * Diese Funktion weist den Variablen aus den Template-Dateien bestimmte Werte zu.
		 * 
		 * $name:		Bezeichnung der Variablen in der Template-Datei.
		 * $value:		Einzusetzender Wert.
		 */ 
		public function assign($name, $value){
			$this->vars[$name] = $value;
		}
		
		/*
		 * Erzeugt den eigentlichen HTML-Ausgabe-Code und speichert diesen im Response-Objekt.
		 * 
		 * $request:	Request-Objekt.
		 * $response:	Response-Objekt.
		 */
		public function render(Request $request, Response $response){
			ob_start();
			$file = 'templates/'.$this->template.'Template.php';
			include_once $file;
			$data = ob_get_clean();
			$response->write($data);
		}
		
		public function __get($property){
			if(isset($this->vars[$property])) return $this->vars[$property];
			return null;
		}
	
	}
?>