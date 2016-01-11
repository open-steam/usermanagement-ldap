<?php
/*
 * Command-Klasse zum Durchführen eines Gruppen-LookUps
 */
	class LookUpCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$view = new TemplateView('lookUp');
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
			
			// Benutzeraktion: 
			if($request->issetParameter('lookUp')){
				$groupname = $request->getParameter('directSelect');
				$groupDN = $registry->get('ldapAccess')->getGroupDN_2($groupname);
				$user = $sessionRegistry->get('uid');
				$userPW = $sessionRegistry->get('userPW');
				// sTeam
				$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
				if(!$steamConnector->get_login_status()){ 
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Verbindung zum sTeam-Server konnte nicht erstellt werden!');
				}
				else{
					$ldapModule = $steamConnector->get_server_module('persistence:ldap');
					
					$steam_groupname = $steamConnector->predefined_command($ldapModule, 'dn_to_group_name', $groupDN, 0);
					
					$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname, 0);
					$steamGroup->get_members(0);
					
					// Rückmeldung
					$view->assign('status', 'ok');
					$view->assign('statusMsg', 'LookUp wurde durchgef&uuml;hrt!');
				}
				
			}

			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>
