<?php
/*
 * Command-Klasse zum Bearbeiten von Gruppendaten. Dies betrifft auch die Zuordnung von Mitglieder
 * zu Gruppen.
 */
	class changeGroupdataCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('changeGroupdata');
			$maxParameterIndex = $sessionRegistry->get('maxParameterIndex');	
			
			// Benutzeraktion: neue Gruppe gewählt
			if($request->issetParameter('chooseGroup') AND $request->issetParameter('groupSelect')){
				$sessionRegistry->set('activeGroup', $request->getParameter('groupSelect'));
			}
			
			// Benutzeraktion: Gruppendaten bearbeiten.
			if($request->issetParameter('changeData')){
				
				// eingegebenen Daten auf Korrektheit überprüfen.
				$datacorrectness = true;
				if($request->issetParameter('groupname') == false OR $request->issetParameter('owner') == false){
					$datacorrectness = false;
				}
				elseif($request->getParameter('groupname') == '' OR $request->getParameter('owner') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning_2');
					$view->assign('statusMsg', 'Bitte geben Sie alle benötigten Daten ein!');
				}
				elseif($request->getParameter('groupname') != '' AND $request->getParameter('groupname') != $sessionRegistry->get('activeGroup') AND $registry->get('ldapAccess')->groupExists($request->getParameter('groupname'), $sessionRegistry->get('school'), $sessionRegistry->get('district')) == true){
					$datacorrectness = false;
					$view->assign('status', 'warning_2');
					$view->assign('statusMsg', 'Dieser Gruppenname existiert bereits. Bitte w&auml;hlen Sie einen anderen Namen!');
				}
				//TMP
				/*
				elseif($request->getParameter('groupname') != '' AND $registry->get('ldapAccess')->hasSubgroups($sessionRegistry->get('activeGroup'))){
					$datacorrectness = false;
					$view->assign('status', 'warning_2');
					$view->assign('statusMsg', 'Achtung: momentan k&ouml;nnen Gruppen, die noch Untergruppen besitzen, aus technischen Gr&uuml;nden nicht umbenannt oder verschoben werden. An einer L&ouml;sung wird gerade gearbeitet!');
				}*/
				elseif($request->getParameter('owner') != '' AND $registry->get('ldapAccess')->ultimate_userExists($request->getParameter('owner')) == false){
					$datacorrectness = false;
					$view->assign('status', 'warning_2');
					$view->assign('statusMsg', 'Der als Besitzer angegebene Benutzername existiert nicht!');
				}
				elseif($request->getParameter('parent') == $sessionRegistry->get('activeGroup')){
					$datacorrectness = false;
					$view->assign('status', 'warning_2');
					$view->assign('statusMsg', 'Eine Gruppe kann nicht sich selbst als Elterngruppe haben!');
				}
				
				elseif($request->getParameter('parent') != $registry->get('ldapAccess')->getParentGroupname($sessionRegistry->get('activeGroup')) AND $registry->get('ldapAccess')->hasSubgroups($sessionRegistry->get('activeGroup'))){
					$datacorrectness = false;
					$view->assign('status', 'warning_2');
					$view->assign('statusMsg', 'Diese Gruppe besitzt noch Untergruppen, und kann daher keiner neuen Elterngruppe zugeordnet werden!');
				}
				/*
				// falls die gruppe umbenannt wurde, auf steam-seite aktualisieren
				if($datacorrectness AND $request->getParameter('groupname') != $sessionRegistry->get('activeGroup')){
					
					$groupDN_new = str_replace('cn='.$sessionRegistry->get('activeGroup'), 'cn='.$request->getParameter('groupname') ,$registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup')));
					$groupDN_old = $registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup'));
					
					// verbindung zu steam aufbauen
					$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
					
					// neuen steam-namen der gruppe ermitteln
					$ldap_module = $steamConnector->get_server_module('persistence:ldap');					
					$steam_groupname_new = $steamConnector->predefined_command($ldap_module, 'dn_to_group_name', $groupDN_new, 0);
					$steam_groupname_old = $steamConnector->predefined_command($ldap_module, 'dn_to_group_name', $groupDN_old, 0);
					
					echo 'alter steam_name: '.$steam_groupname_old.'<br>';
					echo 'neuer steam_name: '.$request->getParameter('groupname').'<br>';
					
					$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname_old, 0);
					echo $steamGroup->set_name($request->getParameter('groupname'), 0);
				}
				*/
				if($datacorrectness){
					$data = array();
					$data['groupname'] = $request->getParameter('groupname');
					$data['owner'] = $request->getParameter('owner');
					$data['description'] = $request->getParameter('description');
					$data['parent'] = $request->getParameter('parent');
					
					$done = $registry->get('ldapAccess')->setGroupInformation($sessionRegistry->get('activeGroup'), $data);
					if($done === true){
						$view->assign('status', 'ok_2');
						$view->assign('statusMsg', 'Die Daten wurden ge&auml;ndert!');
					}
					else{
						$view->assign('status', 'warning_2');
						$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');						
					}				
				}
			}
			
			if($request->issetParameter('performAction')){
				if($request->issetParameter('actionSelect')){
					
					// Benutzeraktion: Benutzer aus Gruppe entfernen.
					if($request->getParameter('actionSelect') == 'remove'){
						$unremovedUser = array();
						$groupDN = $registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup'));
						$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
						$ldapModule = $steamConnector->get_server_module('persistence:ldap');
						$steam_groupname = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN, 0);
						$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname, 0);
						
						for($i=1; $i<=$maxParameterIndex; $i++){
							if($request->issetParameter('name'.$i)){
								$done = $registry->get('ldapAccess')->unsetUserData($request->getParameter('name'.$i), 'membership', $sessionRegistry->get('activeGroup'));
								if($done == false) $unremovedUser[] = $request->getParameter('name'.$i);
								else{
									$user = steam_factory::get_user($steamConnector, $request->getParameter('name'.$i));
									$steamGroup->remove_member($user, 0);
								}
							}
						}
						$steamConnector->disconnect();
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
					
					// Benutzeraktion: Benutzer einer anderen Gruppe hinzufügen.
					elseif($request->getParameter('actionSelect') == 'add'){
						$unaddedUser = array();
						$groupDN = $registry->get('ldapAccess')->getGroupDN_2($request->getParameter('targetGroup'));
						$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
						$ldapModule = $steamConnector->get_server_module('persistence:ldap');
						$steam_groupname = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN, 0);
						$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname, 0);
						
						for($i=1; $i<=$maxParameterIndex; $i++){
							if($request->issetParameter('name'.$i)){
								if($request->issetParameter('targetGroup')){ 
									$done = $registry->get('ldapAccess')->setUserData($request->getParameter('name'.$i), 'membership', $request->getParameter('targetGroup'));
									if($done == false) $unaddedUser[] = $request->getParameter('name'.$i);
									else{
										$user = steam_factory::get_user($steamConnector, $request->getParameter('name'.$i));
										$steamGroup->add_member($user, 0);
									}
								}
							}
						}
						$steamConnector->disconnect();
						if(count($unaddedUser) === 0){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Alle markierten Benutzer wurden der Gruppe "'.$request->getParameter('targetGroup').'" hinzugef&uuml;gt!');
						}
						else{
							$statusMsg = 'Folgende Benutzer konnten der Gruppe "'.$request->getParameter('targetGroup').'" nicht hinzugef&uuml;gt werden:<ul>';
							foreach($unaddedUser as $uid){
								$statusMsg .= '<li>'.$uid;
							}
							$statusMsg .= '</ul>';
							$view->assign('status', 'warning');
							$view->assign('statusMsg', $statusMsg);
						}
					}
					
					// Benutzeraktion: Benutzer in andere Gruppe verschieben.
					elseif($request->getParameter('actionSelect') == 'move'){
						$unmovedUser = array();
						$groupDN_old = $registry->get('ldapAccess')->getGroupDN_2($sessionRegistry->get('activeGroup'));
						$groupDN_new = $registry->get('ldapAccess')->getGroupDN_2($request->getParameter('targetGroup'));
						$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
						$ldapModule = $steamConnector->get_server_module('persistence:ldap');
						$steam_groupname_old = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN_old, 0);
						$steam_groupname_new = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN_new, 0);
						$steamGroup_old = steam_factory::get_group($steamConnector, $steam_groupname_old, 0);
						$steamGroup_new = steam_factory::get_group($steamConnector, $steam_groupname_new, 0);
						
						for($i=1; $i<=$maxParameterIndex; $i++){
							if($request->issetParameter('name'.$i)){
								if($sessionRegistry->get('activeGroup') != 'Gruppenlos') $done = $registry->get('ldapAccess')->unsetUserData($request->getParameter('name'.$i), 'membership', $sessionRegistry->get('activeGroup'));
								$done2 = $registry->get('ldapAccess')->setUserData($request->getParameter('name'.$i), 'membership', $request->getParameter('targetGroup'));
								if($done == false OR $done2 == false) $unremovedUser[] = $request->getParameter('name'.$i);
								else{
									$user = steam_factory::get_user($steamConnector, $request->getParameter('name'.$i));
									$steamGroup_old->remove_member($user, 0);
									$steamGroup_new->add_member($user, 0);
								}
							}
						}
						if(count($unmovedUser) === 0){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Alle markierten Benutzer wurden in die Gruppe "'.$request->getParameter('targetGroup').'" verschoben!');
						}
						else{
							$statusMsg = 'Folgende Benutzer konnten nicht in die Gruppe verschoben werden:<ul>';
							foreach($unremovedUser as $uid){
								$statusMsg .= '<li>'.$uid;
							}
							$statusMsg .= '</ul>Die &uuml;brigen der markierten Benutzer wurden verschoben!';
							$view->assign('status', 'warning');
							$view->assign('statusMsg', $statusMsg);
						}
					}
				}
			}
			
			// Template-Variablen Werte zuweisen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
			$view->assign('groupList2', $registry->get('ldapAccess')->getGroupsDN());
			$view->assign('targetGroupList', $registry->get('ldapAccess')->getGroupsDN());
			if($request->issetParameter('groupSelect')) $view->assign('groupSelect', $request->getParameter('groupSelect'));	
			if($sessionRegistry->get('activeGroup') != null AND $sessionRegistry->get('activeGroup') != ''){
				$view->assign('groupdata', $registry->get('ldapAccess')->getGroupInformation($sessionRegistry->get('activeGroup')));			
				if($sessionRegistry->get('activeGroup') == 'Gruppenlos'){
					$memberlist = array();
					//$memberlist = $registry->get('ldapAccess')->search('', $sessionRegistry->get('activeGroup'));
					$memberlist = $registry->get('ldapAccess')->getGroupMembers('Gruppenlos');
					$sessionRegistry->set('maxParameterIndex', count($memberlist)+1); 
					$view->assign('memberlist', $memberlist);
				}
				else{
					$memberlist = array();
					$memberlist = $registry->get('ldapAccess')->getGroupMembers($sessionRegistry->get('activeGroup'));
					$sessionRegistry->set('maxParameterIndex', count($memberlist)+1); 
					$view->assign('memberlist', $memberlist);
				}
				if(!$request->issetParameter('chooseGroup')) $view->assign('groupSelect', $sessionRegistry->get('activeGroup'));
			}
			
			// Ausgabe erzeugen		
			$view->render($request, $response);
		}
		

	}
?>
