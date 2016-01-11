<?php
/*
 * Command-Klasse zum Bearbeiten von Benutzerdaten.
 */
	class changeUserDataCommand implements Command{

		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('changeUserdata');
			
			// Template-Variablen Werte zuwesien
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());

			// Benutzeraktion: Benutzerdaten ändern.
			if($request->issetParameter('changeData')){
				
				// Eigegebene Daten auf Korrektheit überprüfen
				$datacorrectness = true;
				if($request->getParameter('givenname') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte einen Vornamen eingeben!');
				}
				if($request->getParameter('surname') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte einen Nachnamen eingeben!');
				}
		
				if($datacorrectness){
					$givenname = $request->getParameter('givenname');
					$surname = $request->getParameter('surname');
					$email = $request->getParameter('email');
					$role = $request->getParameter('role');
					$uid = $sessionRegistry->get('activeUser');
					$done = $registry->get('ldapAccess')->setUserInformation($uid, $givenname, $surname, $email, $role);
					if($done){
						$view->assign('status', 'ok');
						$view->assign('statusMsg', 'Die Daten wurden ge&auml;ndert!');
					}
					else{
						$view->assign('status', 'warning');
						$view->assign('statusMsg', 'Systemfehler: LDAP-Kommand fehlgeschlagen!');
					}
				}
			}
			
			// Benutzeraktion: Suche nach Benutzernamen starten.
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
				$view->assign('results', $registry->get('ldapAccess')->search($sessionRegistry->get('activeNamefilter'), $sessionRegistry->get('activeGroup')));				
			}
			
			// Daten des gewählten Benutzers ermitteln
			if($request->issetParameter('userSelect') OR ($sessionRegistry->get('activeUser') != null AND $sessionRegistry->get('activeUser') != '')){
				if($request->issetParameter('userSelect')) $sessionRegistry->set('activeUser', $request->getParameter('userSelect'));
				$view->assign('userSelect', $sessionRegistry->get('activeUser'));
				$view->assign('userdata', $registry->get('ldapAccess')->getUserInformation($sessionRegistry->get('activeUser')));
			}
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>