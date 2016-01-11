<?php
/*
 * Command-Klasse zum Verschieben eines Benutzers in den Pool.
 */
	class moveToPoolCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('moveToPool');
			
			// Tempalte-Variablen Werten zuweisen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
			
			// Benutzeraktion: Benutzer in Pool verschieben.
			if($request->issetParameter('moveUser')){
				$unmovedUser = array();	
				for($i=1; $i<=$sessionRegistry->get('maxParameterIndex'); $i++){
					if($request->issetParameter('name'.$i)){ 
						$registry->get('ldapAccess')->replaceUserData($request->getParameter('name'.$i), 'description', str_replace('ou=', '', $sessionRegistry->get('school')));
						$registry->get('ldapAccess')->unsetUserData($request->getParameter('name'.$i), 'membership', array());
						$done = $registry->get('ldapAccess')->moveUser($request->getParameter('name'.$i), 'POOL');
						if($done == false) $unmovedUser[] = $request->getParameter('name'.$i);
					}
				}
				if(count($unmovedUser) === 0){
					$view->assign('status', 'ok');
					$view->assign('statusMsg', 'Alle markierten Benutzer wurden in den Pool verschoben!');
				}
				else{
					$statusMsg = 'Die folgenden Benutzer konnten nicht verschoben werden:<ul>';
					foreach($unmovedUser as $uid){
						$statusMsg .= '<li>'.$uid;
					}
					$statusMsg .= '</ul>Die &uuml;brigen Benutzer wurden erfolgreich verschoben.';
					$view->assign('status', 'warning');
					$view->assign('statusMsg', $statusMsg);
				}
			unset($unmovedUser);
			}
			
			// Benutzeraktion: Suche nach Benutzernamen starten.
			if($request->issetParameter('userSearch')){
				$sessionRegistry->set('activeNamefilter', $request->getParameter('namefilter'));
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