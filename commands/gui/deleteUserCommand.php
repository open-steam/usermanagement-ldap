<?php
/*
 * Command-Klasse zum Löschen von Benutzern. Die Benutzer werden dabei lediglich in den
 * Papierkorb verschoben, und müssen von dort endgültig gelöscht werden.
 */
	class deleteUserCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('deleteUser');
			
			// Template-Variablen Werte zuordnen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
			
			// Benutzeraktion: Benutzer löschen.
			if($request->issetParameter('delete')){
				$unmovedUser = array();	
				for($i=1; $i<=$sessionRegistry->get('maxParameterIndex'); $i++){
					if($request->issetParameter('name'.$i)){ 
						$done = $registry->get('ldapAccess')->moveUser($request->getParameter('name'.$i), 'TRASH');
						if($done == false) $unmovedUser[] = $request->getParameter('name'.$i);	
					}
				}
				if(count($unmovedUser) == 0){
					$view->assign('status', 'ok');
					$view->assign('statusMsg', 'Alle markierten Benutzer wurden in den Papierkorb verschoben!');
				}
				else{
					$statusMsg = 'Folgende Benutzer konnten nicht gel&ouml;scht werden:<ul>';
					foreach($unmovedUser as $uid){
						$statusMsg .= '<li>'.$uid;
					}
					$statusMsg .= '</ul>Die &uuml;brigen wurden in den Papierkorb verschoben.';
					$view->assign('status', 'warning');
					$view->assign('statusMsg', $statusMsg);
				}
				
			}
			
			// Benutzeraktion: Nach Benutzernamen suchen.
			if($request->issetParameter('userSearch')){ 
				if($request->issetParameter('namefilter') AND $request->issetParameter('namefilter') != ''){ 
					$sessionRegistry->set('activeNamefilter', $request->getParameter('namefilter'));
				}
				else $sessionRegistry->set('activeNamefilter', '*');
				$sessionRegistry->set('activeGroup', $request->getParameter('groupSelect'));
			}
			if($sessionRegistry->get('activeGroup') != null){
				$view->assign('namefilter', $sessionRegistry->get('activeNamefilter'));
				$view->assign('groupSelect', $sessionRegistry->get('activeGroup'));
				$results = $registry->get('ldapAccess')->search($sessionRegistry->get('activeNamefilter'), $sessionRegistry->get('activeGroup'));
				$sessionRegistry->set('maxParameterIndex', count($results));
				$view->assign('results', $results);				
			}
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
		

	}
?>