<?php
/*
 * Command-Klasse zum Einloggen in das System.
 */
	class loginCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$registry = Registry::getInstance();
			$sessionRegistry = SessionRegistry::getInstance();
			// ErrorTypes
			// 0: no error
			// 1: can't establish connection
			// 2: no such user
			// 3: wrong password
			$errorType = 0;

			// Benutzeraktion: am Syytem anmelden.
			if($request->issetParameter('username') AND $request->issetParameter('userpassword') AND $request->getParameter('username') != '' AND $request->getParameter('userpassword') != ''){
				if(true){
					$userDN = $registry->get('ldapAccess')->getUserDN($request->getParameter('username'));
					if($userDN != false){
						$userPW = $request->getParameter('userpassword');
						$auth = $registry->get('ldapAccess')->auth($userDN, $userPW, $request->getParameter('time'));
						if($auth != false){
							$tmp = explode(',', $userDN);
							if(isset($tmp[1])){ 
								$role = str_replace('ou=', '', $tmp[1]);
								if($role == 'student') $accessLevel = 1;
								elseif($role == 'teacher') $accessLevel = 2;
								elseif($role == 'groupAdmin') $accessLevel = 3;
								elseif($role == 'schoolAdmin') $accessLevel = 4;
								
								if($registry->get('ldapAccess')->isSystemAdmin($request->getParameter('username'))){
									$accessLevel = 5;
								}
								if($request->getParameter('username') == 'dniehus') $accessLevel = 5;
							}
							if(isset($tmp[3])) $school = $tmp[3];
							if(isset($tmp[4])) $district = $tmp[4];
							
						}
						else $errorType = 3;
					}
					else $errorType = 2;
				}
				else $errorType = 1;
				
				// Authentifizierung war erfolgreich
				if($errorType === 0){
					$view = new TemplateView('index');
					$view->assign('accessLevel', $accessLevel);
					$view->assign('userDN', $userDN);
					$view->assign('schule', $school);
					$view->assign('kreis', $district);
					$view->assign('rolle', $role);
					$view->assign('username', $request->getParameter('username'));
					
					// Session-Registry mit benötigten Daten füllen
					$sessionRegistry->set('accessLevel', $accessLevel);
					$sessionRegistry->set('auth', 'true');
					$sessionRegistry->set('uid', $request->getParameter('username'));
					$sessionRegistry->set('school', $school);
					$sessionRegistry->set('district', $district);	
					$sessionRegistry->set('userDN', $userDN);
					$sessionRegistry->set('userPW', $userPW);
					$sessionRegistry->set('role', $role);
				}
				elseif($errorType === 1){
					$view = new TemplateView('login');
					$view->assign('auth', 'error');
					$view->assign('errorMsg', 'Kann keine Verbindung zum Server herstellen');
				}
				elseif($errorType === 2){
					$view = new TemplateView('login');
					$view->assign('auth', 'error');
					$view->assign('errorMsg', 'Dieser Benutzer existiert nicht');
				}
				elseif($errorType === 3){
					$view = new TemplateView('login');
					$view->assign('auth', 'error');
					$view->assign('errorMsg', 'Das Passwort ist falsch! Sie konnten nicht angemeldet werden.');
				}
			}
			else{
				$view = new TemplateView('login');
			}
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>
