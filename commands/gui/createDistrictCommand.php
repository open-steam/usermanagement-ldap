<?php
/*
 * Command-Klasse zum Erstellen eines neuen Kreises.
 */
	class CreateDistrictCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$view = new TemplateView('createDistrict');
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			
			// Benutzeraktion: neuen Kreis anlegen.
			if($request->issetParameter('create')){
				
				// Eingabedaten auf Korrektheit prüfen
				$datacorrectness = true;
				if(!$request->issetParameter('districtName') OR $request->getParameter('districtName') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte geben Sie einen Namen f&uuml;r den Kreis an!');								
				}

				
				if($datacorrectness){
					
					$status = $registry->get('ldapAccess')->createDistrict($request->getParameter('districtName'));
					if($status != false){
						$view->assign('status', 'ok');
						$view->assign('statusMsg', 'Der Kreis wurde korrekt angelegt!');					
					}
					else{
						$view->assign('status', 'warning');
						$view->assign('statusMsg', 'Es ist ein Fehler aufgetreten!');						
					}
				}
			}

			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>