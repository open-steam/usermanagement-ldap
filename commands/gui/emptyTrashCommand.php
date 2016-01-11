<?php
/*
 * Command-Klasse zu Leeren des Papierkorbes. Dabei werden die Benutzer endgültig gelöscht.
 */
	class emptyTrashCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('emptyTrash');
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			
			// Benutzeraktion: Papierkorb leeren.
			if($request->issetParameter('empty')){
			$unmovedUser = array();
				for($i=1; $i<=$sessionRegistry->get('maxParameterIndex'); $i++){
					if($request->issetParameter('name'.$i)){ 
						$done = $registry->get('ldapAccess')->removeUser($request->getParameter('name'.$i));
						if($done == false) $unmovedUser[] = $request->getParameter('name'.$i);
					}
				}
				if(count($unmovedUser) == 0){
					$view->assign('status', 'ok');
					$view->assign('statusMsg', 'Alle markierten Benutzer wurden endg&uuml;ltig gel&ouml;scht!');
				}
				else{
					$statusMsg = 'Die folgenden Benutzer konnten nicht endg&uuml;ltig gel&ouml;scht werden:<ul>';
					foreach($unmovedUser as $uid){
						$statusMsg .= '<li>'.$uid;
					}
					$statusMsg .= '</ul>Die �brigen Benutzer wurden endg&uuml;ltig gel&ouml;scht.';
					$view->assign('status', 'warning');
					$view->assign('statusMsg', $statusMsg);
				}
			unset($unmovedUser);
			}
			
			// Benutzeraktion: Suche nach Benutzernamen starten.
			if($request->issetParameter('userSearch')){ 
				// TODO: gruppen und datenfilter pr�fen
				$sessionRegistry->set('activeGroup', $request->getParameter('timespan'));
			}
			if($sessionRegistry->get('activeGroup') != null){
				$view->assign('timespan', $sessionRegistry->get('activeGroup'));
				$results = $registry->get('ldapAccess')->search('', 'TRASH', false, $request->getParameter(('timespan')));
				$sessionRegistry->set('maxParameterIndex', count($results));
				$view->assign('results', $results);				
			}
			
			// Ausgabe erzeugen.		
			$view->render($request, $response);
		}
	}
?>