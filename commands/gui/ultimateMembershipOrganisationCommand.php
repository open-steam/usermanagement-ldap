<?php
/*
 * Command-Klasse zum Bearbeiten von Gruppendaten. Dies betrifft auch die Zuordnung von Mitglieder
 * zu Gruppen.
 */
	class ultimateMembershipOrganisationCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('ultimateMembershipOrganisation');
			$maxParameterIndex = $sessionRegistry->get('maxParameterIndex');	
			
			// Benutzeraktion: neue Gruppe gewählt
			if($request->issetParameter('chooseGroup') AND $request->issetParameter('groupSelect')){
				$sessionRegistry->set('activeGroup', $request->getParameter('groupSelect'));
			}
			
			// Benutzeraktion: Benutzer hinzufügen.
			if($request->issetParameter('addUser')){
				
				// eingegebenen Daten auf Korrektheit überprüfen.
				$datacorrectness = true;
				if(!$registry->get('ldapAccess')->ultimate_userExists($request->getParameter('username'))){
					$datacorrectness = false;
				}

				
				if($datacorrectness){
					$done = $registry->get('ldapAccess')->ultimate_addUser($request->getParameter('username'), $registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup')));
					if($done === true){
						$view->assign('status', 'ok_2');
						$view->assign('statusMsg', 'Der Benutzer wurde hinzugef&uuml;gt!');

						// LookUp durchführen
						$groupDN = $registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup'));
						$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
						$ldapModule = $steamConnector->get_server_module('persistence:ldap');
						$steam_groupname = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN, 0);
						$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname, 0);
						$user = steam_factory::get_user($steamConnector, $request->getParameter('username'));
						$steamGroup->add_member($user, 0);
						$steamConnector->disconnect();
					}
					else{
						$view->assign('status', 'warning_2');
						$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');						
					}				
				}
				else{
					$view->assign('status', 'warning_2');
					$view->assign('statusMsg', 'Dieser Benutzer existiert nicht!');
				}
			}
			

					
		// Benutzeraktion: Benutzer aus Gruppe entfernen.
		if($request->getParameter('remove') == 'true'){
			$unremovedUser = array();
			for($i=1; $i<=$maxParameterIndex; $i++){
				if($request->issetParameter('name'.$i)){
					$done = $registry->get('ldapAccess')->ultimate_removeUser($request->getParameter('name'.$i), $registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup')));
					if($done == false) $unremovedUser[] = $request->getParameter('name'.$i);

					else{
						// LookUp durchführen
						$groupDN = $registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup'));
						$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
						$ldapModule = $steamConnector->get_server_module('persistence:ldap');
						$steam_groupname = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN, 0);
						$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname, 0);
						$user = steam_factory::get_user($steamConnector, $request->getParameter('name'.$i));
						$steamGroup->remove_member($user, 0);
						$steamConnector->disconnect();
					}
				}
			}
			if(count($unremovedUser) === 0){
				$view->assign('status', 'ok');
				$view->assign('statusMsg', 'Alle markierten Benutzer wurden aus dieser Gruppe entfernt!');
			}
			else{
				$statusMsg = 'Folgende Benutzer konnten nicht aus der Gruppe entfernt werden:<ul>';
				foreach($unremovedUser as $uid){
					$statusMsg .= '<li>'.$uid;
				}
				$statusMsg .= '</ul>Die &uuml;brigen der markierten Benutzer wurden aus dieser Gruppe entfernt!';
				$view->assign('status', 'warning');
				$view->assign('statusMsg', $statusMsg);
			}
		}
					

			
			// Template-Variablen Werte zuweisen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());	
			if($request->issetParameter('groupSelect')) $view->assign('groupSelect', $request->getParameter('groupSelect'));	
			
			if($sessionRegistry->get('activeGroup') != null AND $sessionRegistry->get('activeGroup') != ''){
				$memberlist = array();
				$memberlist = $registry->get('ldapAccess')->ultimate_getGroupMembers($sessionRegistry->get('activeGroup'));
				$sessionRegistry->set('maxParameterIndex', count($memberlist)+1); 
				$view->assign('memberlist', $memberlist);
				if(!$request->issetParameter('chooseGroup')) $view->assign('groupSelect', $sessionRegistry->get('activeGroup'));
			}
			
			// Ausgabe erzeugen		
			$view->render($request, $response);
		}
		

	}
?>
