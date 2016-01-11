<?php
/*
 * Command-Klasse zum Erstellen einer neuen Schule.
 */
	class CreateSchoolCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$view = new TemplateView('createSchool');
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('districtList', $registry->get('ldapAccess')->getDistricts());
			
			// Benutzeraktion: neuen Kreis anlegen.
			if($request->issetParameter('create')){
				
				// Eingabedaten auf Korrektheit prüfen
				$datacorrectness = true;
				if(!$request->issetParameter('schoolName') OR $request->getParameter('schoolName') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte geben Sie einen Namen f&uuml;r die Schule an!');								
				}
				if(!$request->issetParameter('districtName') OR $request->getParameter('districtName') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte geben Sie einen g&uuml;ltigen Kreis an!');								
				}

				
				if($datacorrectness){
					
					$status = $registry->get('ldapAccess')->createSchool($request->getParameter('schoolName'), $request->getParameter('districtName'));
					
					if($request->getParameter('vorname') != '' AND $request->getParameter('nachname') != ''){
						$uid = $registry->get('ldapAccess')->createUser($request->getParameter('vorname'), $request->getParameter('nachname'), 'schoolAdmin', $request->getParameter('email'));
					}
					
					
					
					if($status != false){
						$msg = 'Die Schule wurde korrekt angelegt.';
						if(isset($uid)){
							if($uid != false) $msg .= '<br>Es wurde ein Schuladministrator angelegt. Dessen Benutzername lautet: '.$uid;
							else $msg .= '<br>Der Schuladministrator konnte nicht angelegt werden!';
						}
							
						$view->assign('status', 'ok');
						$view->assign('statusMsg', $msg);					
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
