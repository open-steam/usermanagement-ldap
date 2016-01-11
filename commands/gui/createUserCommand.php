<?php
/*
 * Command-Klasse zum Erstellen neuer Benutzer.
 */
	class createUserCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$view = new TemplateView('createUser');
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
			
			// Benutzeraktion: neuen Benutzer anlegen.
			if($request->issetParameter('create')){
				
				// Eingabedaten auf Korrektheit prüfen
				$datacorrectness = true;
				if(!$request->issetParameter('vorname') OR $request->getParameter('vorname') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte geben Sie einen Vornamen ein!');								
				}
				elseif(!$request->issetParameter('nachname') OR $request->getParameter('nachname') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte geben Sie einen Nachnamen ein!');								
				}
				
				if($datacorrectness){
					
					$newUser = $registry->get('ldapAccess')->createUser($request->getParameter('vorname'), $request->getParameter('nachname'), $request->getParameter('roleSelect'), $request->getParameter('email'), $request->getParameter('defaultGroup_1'));
					if($newUser != false){
						$view->assign('status', 'ok');
						$view->assign('statusMsg', 'Der Benutzer wurde erfolgreich erstellt!<br>Das erstellte Login lautet: '.$newUser.'<br>Dies ist zudem das Passwort.');					
					}
					else{
						$view->assign('status', 'warning');
						$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');						
					}
					/*
					// Fall: Angaben mit email-Adresse
					if($request->issetParameter('email') AND $request->getParameter('email') != ''){
						$newUser = $registry->get('ldapAccess')->createUser($request->getParameter('vorname'), $request->getParameter('nachname'), $request->getParameter('roleSelect'), $request->getParameter('email'));
						if($newUser != false){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Der Benutzer wurde erfolgreich erstellt!<br>Das erstellte Login lautet: '.$newUser.'<br>Dies ist zudem das Passwort.');					
						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');						
						}
					}
					
					// Angaben ohne email-Adresse
					else{
						$newUser = $registry->get('ldapAccess')->createUser($request->getParameter('vorname'), $request->getParameter('nachname'), $request->getParameter('roleSelect'));
						if($newUser != false){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Der Benutzer wurde erfolgreich erstellt!<br>Das erstellte Login lautet: '.$newUser.'<br>Dies ist zudem das Passwort.');					
						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');						
						}
					}
					*/
				}
			}
			
			// Benutzeraktion: Benutzer aus text-file anlegen.
			if($request->issetParameter('multipleCreation')){
				
				$userDatas = array();
				$createdUsers = array();
				
				if($request->issetParameter('creationType') AND $request->getParameter('creationType') == 'useUpload'){
					if($_FILES['uploadFile']['tmp_name']){
						
						$file = $_FILES['uploadFile']['tmp_name'];

						$fileContent = file_get_contents($file);
						$userDatas = explode(';', $fileContent);
						array_pop($userDatas);
					}
				}
				elseif($request->issetParameter('creationType') AND $request->getParameter('creationType') == 'useTextfield'){
					if($request->issetParameter('creationText') AND $request->getParameter('creationText') != ''){
						$userDatas = explode(';', $request->getParameter('creationText'));
						array_pop($userDatas);
					}
				}

				// jeden datensatz durchlaufen
				foreach($userDatas AS $userData){
					$userData = trim($userData, "\x00..\x1F");
					$dataElements = explode (',', $userData);
					$surname = $dataElements[0];
					$givenname = $dataElements[1];
					$email = '';
					$role = $request->getParameter('defaultRole');
					$group = $request->getParameter('defaultGroup_2');
					
					for($i=2; $i<=4; $i++){
						if(isset($dataElements[$i])){
							if(strpos($dataElements[$i], '@') != false) $email = $dataElements[$i];
							elseif($dataElements[$i] == 'Schüler') $role = 'student';
							elseif($dataElements[$i] == 'Lehrer') $role = 'teacher';
							elseif($dataElements[$i] == 'Gruppenadministrator') $role = 'groupAdmin';
							elseif($dataElements[$i] == 'Schuladministrator') $role = 'schoolAdmin';		
							else $group = $dataElements[$i];
						}
					}
					
					// neuen benutzer erstellen
					$login = $registry->get('ldapAccess')->createUser($surname, $givenname, $role, $email, $group);
					
					if($login != false){ 
						$createdUsers[] = array('login'=>$login, 'name'=>$surname.' '.$givenname);
					
						// gegebenenfalls benutzer einer gruppe hinzufügen
						//if($group != '---') $done = $registry->get('ldapAccess')->setUserData($login, 'ou', $group);
					}
				}
			
				$view->assign('userCreated', $createdUsers);

			}
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>
