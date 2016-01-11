<?php
/*
 * Diese Command-Klasse wird zur Navigation benötigt. Wird ein Link im Navigationsmenü gedrückt,
 * erzeugt diese Klasse die Benutzungsoberfläche des gewählten Links.
 */
	class navigationCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$target = '';
		
			// Navigationsziel bestimmen und prüfen, ob der Benutzer die Rechte besitzt, diese Seite aufrufen zu dürfen.
			if($request->issetParameter('target')){
				$target = $request->getParameter('target');
				$accessCheck = false;
				
				if($target == 'changeOwnPassword' AND $sessionRegistry->get('accessLevel') >= 1) $accessCheck = true;
				if($target == 'showUserdata' AND $sessionRegistry->get('accessLevel') >= 2) $accessCheck = true;
				if($target == 'createGroup' AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
				if($target == 'changeGroupdata' AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
				if($target == 'deleteGroup' AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
				if($target == 'changePassword' AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
				if($target == 'changeUserdata' AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
				if($target == 'createUser' AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
				if($target == 'deleteUser' AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
				if($target == 'moveToPool' AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
				if($target == 'getFromPool' AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
				if($target == 'emptyTrash' AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
				if($target == 'undelete' AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
				if($target == 'changeSchool' AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
				if($target == 'createDistrict' AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
				if($target == 'createSchool' AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
				if($target == 'lookUp' AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
				if($target == 'ultimateMembershipOrganisation' AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;				
				
				if($accessCheck) $view = new TemplateView($request->getParameter('target'));
				else{ 
					$view = new TemplateView('index');
					$view->assign('message', 'Sie haben keine Berechtigung, die angeforderte Seite aufzurufen!');
				}
				
			}
			else $view = new TemplateView('index');
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			
			// SessionRegistry-Daten resetten
			if($sessionRegistry->get('activeGroup') != null) $sessionRegistry->set('activeGroup', '');
			if($sessionRegistry->get('activeNamefilter') != null) $sessionRegistry->set('activeNamefilter', '');
			if($sessionRegistry->get('activeUser') != null) $sessionRegistry->set('activeUser', '');
			if($sessionRegistry->get('maxParameterIndex') != null) $sessionRegistry->set('maxParameterIndex', 0);
				
			// Initiale Daten für die Template-Dateien der einzelnen Seiten erzeugen
			switch($target){
				case 'getFromPool':{
					$view->assign('groupList', $registry->get('ldapAccess')->getOldSchools());
					break;
				}
				case 'createUser':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'deleteUser':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'showUserdata':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'changeUserdata':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'changePassword':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'moveToPool':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'showGroupdata':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'changeGroupdata':{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				case 'changeSchool':{
					$view->assign('districtList', $registry->get('ldapAccess')->getDistricts());
					break;
				}
				case 'createSchool':{
					$view->assign('districtList', $registry->get('ldapAccess')->getDistricts());
					break;
				}
				case 'createGroup':{
					$view->assign('standardOwner', $sessionRegistry->get('uid'));
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
					break;
				}
				default:{
					$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
				}
			}
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>
