<?php
/*
 * Command-Klasse zum sicheren Abmelden vom System.
 */
	class logoutCommand implements Command{
	
		public function execute(Request $request, Response $response){

			// Alle Daten der Session löschen
			$_SESSION = array();
			if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
			session_destroy();

			$view = new TemplateView('login');
			$view->render($request, $response);
		}

	}
?>