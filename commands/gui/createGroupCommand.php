<?php
/*
 * Command-Klasse zum Erstellen neuer Gruppen.
 */
	class createGroupCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('createGroup');

			// Benutzeraktion: neue Gruppe erstellen.
			if($request->issetParameter('createGroup')){
				$datacorrectness = true;
				if(!$request->issetParameter('name') OR !$request->issetParameter('owner')){
					$datacorrectness = false;
				}
				elseif($request->getParameter('name') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte geben Sie einen Gruppennamen an!');
				}
				elseif($this->checkGroupname($request->getParameter('name')) == false){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Dieser Gruppenname kann nicht verwendet werden. Bitte verwenden Sie keine der genannten Sonderzeichen!');
				}
				elseif($request->getParameter('owner') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Sie haben keinen Besitzer angegeben!');
				}
				elseif($request->getParameter('owner') != '' AND $registry->get('ldapAccess')->ultimate_userExists($request->getParameter('owner')) == false){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Der angegebene Benutzer existiert nicht!');
				}
				elseif($request->getParameter('name') != '' AND $registry->get('ldapAccess')->groupExists($request->getParameter('name'), $sessionRegistry->get('school'), $sessionRegistry->get('district')) == true){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Dieser Gruppenname existiert bereits. Bitte w&auml;hlen Sie einen anderen Namen!');
				}
								
				if($datacorrectness){
					
					$done = false;

					// Fall: Zu erstellende Gruppe ist eine Hauptgruppe
					if($request->getParameter('groupType') == 'maingroup'){
						$done = $registry->get('ldapAccess')->createGroup($request->getParameter('name'), $request->getParameter('owner'), $request->getParameter('description'));
						if($done != false){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Die Gruppe wurde erfolgreich angelegt!');						
						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');
						}
					}
					
					// Fall: Zu erstellende Gruppe ist Untergruppe
					elseif($request->getParameter('groupType') == 'subgroup'){
						$done = $registry->get('ldapAccess')->createGroup($request->getParameter('name'), $request->getParameter('owner'), $request->getParameter('description'), $request->getParameter('parent'));
						if($done !== false){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Die Gruppe wurde erfolgreich angelegt!');						
						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');
						}
					}

					// falls gruppe erfolgreich angelegt wurde, lookUp durchführen
					if($done){
						$groupDN = $registry->get('ldapAccess')->getGroupDN_2($request->getParameter('name'));
						// sTeam
						$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
						if(!$steamConnector->get_login_status()){ 
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Verbindung zum sTeam-Server konnte nicht erstellt werden.<br>Die Gruppe wurde zwar angelegt, der  LookUp jedoch nicht durchgef&uuml;hrt!');
						}
						else{
							$ldapModule = $steamConnector->get_server_module('persistence:ldap');					
							$steam_groupname = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN, 0);					
							$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname, 0);
							$steamGroup->get_members(0);
						}	
					}
				}
			}
			
			// Werte den Template-Variablen zuweisen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('standardOwner', $sessionRegistry->get('uid'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
		
		/*
		 * Ein potentieller Gruppenname wird auf das Vorkommen bestimmter, verbotener Sonderzeichen untersucht.
		 */
		private function checkGroupname($name){
			$status = true;
			if(strpos($name, '.') !== false) $status = false;
			if(strpos($name, '@') !== false) $status = false;
			if(strpos($name, '/') !== false) $status = false;
			if(strpos($name, '\\') !== false) $status = false;
			if(strpos($name, ',') !== false) $status = false;
			if(strpos($name, ' ') !== false) $status = false;
			
			return $status;
		}

	}
?>
