<?php
/*
 * Dies ist das Default-Command. Es erzeugt den Login-Bildschirm, in welchem man sich am 
 * System anmelden kann.
 */
	class helloCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$view = new TemplateView('login');
			$view->render($request, $response);
		}
	}
?>