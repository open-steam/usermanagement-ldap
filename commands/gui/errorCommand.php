<?php
/*
 * Command-Klasse zur Erzeugung einer Fehlerausgabe, wie z.B. Ablauf einer Session.
 */
	class errorCommand implements Command{
	
		public function execute(Request $request, Response $response){

			$view = new TemplateView('error');
			$view->render($request, $response);
		}

	}
?>