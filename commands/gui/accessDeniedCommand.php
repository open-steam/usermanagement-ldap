<?php
/*
 * Command-Klasse zur Erzeugung einer Fehlerausgabe.
 */
	class AccessDeniedCommand implements Command{
	
		public function execute(Request $request, Response $response){

			$sessionRegistry = SessionRegistry::getInstance();
			$view = new TemplateView('index');
			$view->assign('message', 'Diese Funktionalit&auml;t steht Ihnen nicht zur Verf&uuml;gung!');
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->render($request, $response);
		}

	}
?>