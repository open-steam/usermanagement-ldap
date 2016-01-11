<?php
/*
 * Command-Klasse zum Holen eines Benutzers aus dem Pool.
 */
	class getFromPoolCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('getFromPool');
			
			// Template-Variablen Werte zurordnen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getOldSchools());
			
			// Benutzeraktion: Benutzer aus dem Pool holen.
			if($request->issetParameter('getUser')){
			$unmovedUser = array();
				for($i=1; $i<=$sessionRegistry->get('maxParameterIndex'); $i++){
						$done = $registry->get('ldapAccess')->moveUser($request->getParameter('name'.$i), $request->getParameter('roleSelect'));
						if($done == false) $unmovedUser[] = $request->getParameter('name'.$i);
				}
				if(count($unmovedUser) == 0){
					$view->assign('status', 'ok');
					$view->assign('statusMsg', 'Alle markierten Benutzer wurden der eigenen Schule hinzugef&uuml;gt!');
				}
				else{
					$statusMsg = 'Die folgenden Benutzer konnten nicht der eigenen Schule hinzugef&uuml;gt werden:<ul>';
					foreach($unmovedUser as $uid){
						$statusMsg .= '<li>'.$uid;
					}
					$statusMsg .= '</ul>Die &uuml;brigen Benutzer wurden erfolgreich der eigenen Schule hinzugef&uuml;gt.';
					$view->assign('status', 'warning');
					$view->assign('statusMsg', $statusMsg);
				}
			unset($unmovedUser);
			}
			
			// Benutzeraktion: Suche nach Benutzernamen starten.
			if($request->issetParameter('userSearch')){ 
				if($request->issetParameter('namefilter') AND $request->issetParameter('namefilter') != ''){ 
					$sessionRegistry->set('activeNamefilter', $request->getParameter('namefilter'));
				}
				else $sessionRegistry->set('activeNamefilter', '*');
				$sessionRegistry->set('activeGroup', $request->getParameter('schoolSelect'));
			}
			if($sessionRegistry->get('activeGroup') != null){
				$view->assign('namefilter', $sessionRegistry->get('activeNamefilter'));
				$view->assign('schoolSelect', $sessionRegistry->get('activeGroup'));
				$results = $registry->get('ldapAccess')->search($sessionRegistry->get('activeNamefilter'), 'POOL', $sessionRegistry->get('activeGroup'));
				$sessionRegistry->set('maxParameterIndex', count($results));
				$view->assign('results', $results);				
			}
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>
