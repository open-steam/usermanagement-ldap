<?php
/*
 * Command-Klasse zum Wiederherstellen von Benutzern.
 */
	class undeleteCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('undelete');
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			
			// Benutzeraktion: Benutzer wiederherstellen
			if($request->issetParameter('undeleteUser')){
			$unmovedUser = array();	
				for($i=1; $i<=$sessionRegistry->get('maxParameterIndex'); $i++){
					if($request->issetParameter('name'.$i)){
						$done = $registry->get('ldapAccess')->moveUser($request->getParameter('name'.$i), 'student');
						if($done == false) $unmovedUser[] = $request->getParameter('name'.$i);
					}
				}
				if(count($unmovedUser) == 0){
					$view->assign('status', 'ok');
					$view->assign('statusMsg', 'Alle markierten Benutzer wurden wiederhergestellt!');
				}
				else{
					$statusMsg = 'Die folgenden Benutzer konnten nicht wiederhergestellt werden:<ul>';
					foreach($unmovedUser as $uid){
						$statusMsg .= '<li>'.$uid;
					}
					$statusMsg .= '</ul>Die �brigen Benutzer wurden erfolgreich wiederhergestellt.';
					$view->assign('status', 'warning');
					$view->assign('statusMsg', $statusMsg);
				}
			unset($unmovedUser);
			}
			
			// Benutzeraktion: Nach Benutzernamen suchen.
			if($request->issetParameter('userSearch')){ 
				if($request->issetParameter('namefilter') AND $request->issetParameter('namefilter') != ''){ 
					$sessionRegistry->set('activeNamefilter', $request->getParameter('namefilter'));
				}
				else $sessionRegistry->set('activeNamefilter', '*');
				$sessionRegistry->set('activeGroup', $request->getParameter('timespan'));
			}
			if($sessionRegistry->get('activeGroup') != null){
				$view->assign('namefilter', $sessionRegistry->get('activeNamefilter'));
				$view->assign('timespan', $sessionRegistry->get('activeGroup'));
				$results = $registry->get('ldapAccess')->search($sessionRegistry->get('activeNamefilter'), 'TRASH', false, $request->getParameter('timespan'));
				$sessionRegistry->set('maxParameterIndex', count($results));
				$view->assign('results', $results);				
			}
			
			// Ausgabe erzeugen.	
			$view->render($request, $response);
		}
	}
?>