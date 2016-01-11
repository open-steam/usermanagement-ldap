<?php
/*
 * Command-Klasse zum Ändern eines fremden Passwortes.
 */
	class changePasswordCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('changePassword');
			
			// Variablen der Template-Dateien Wrte zuweisen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());

			// Benutzeraktion: fremdes Passwort ändern
			if($request->issetParameter('changePW')){
				
				// Eingabedaten auf Korrektheit prüfen
				$datacorrectness = true;
				if(!$request->issetParameter('name') OR $request->getParameter('name') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Sie haben keinen Benutzernamen angegeben!');
				}
				elseif(!$registry->get('ldapAccess')->userExists($request->getParameter('name'))){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Der angegebene Benutzer existiert nicht an dieser Schule!');
				}
				elseif(!$request->issetParameter('passwordNew') OR !$request->issetParameter('passwordRetype') OR $request->getParameter('passwordNew') == '' OR $request->getParameter('passwordRetype') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Sie haben kein neues Passwort angegeben!');				
				}
				elseif($request->getParameter('passwordNew') != $request->getParameter('passwordRetype')){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Das neue Passwort und die Best&auml;tigung stimmen nicht &uuml;berein!');	
				}
				
				if($datacorrectness){
					
					// Email-Adresse des Benutzers ermitteln
					$data = array();
					$data = $registry->get('ldapAccess')->getUserInformation($request->getParameter('name'));
					if(isset($data['email'])) $email = $data['email'];
					else $email = '';				
				
					// Fall: Es wurde eine Email-Adresse ermittelt und ein zufälliges Passwort generiert.
					if($request->issetParameter('randomPW') AND $email != ''){
						$newPW = $this->randomPW();
						$done = $registry->get('ldapAccess')->replaceUserData($request->getParameter('name'), 'userPassword', $newPW);
						if($done != false){
							$status = $this->mailPW($email, $newPW);
							if($status){
								$view->assign('status', 'ok');
								$view->assign('statusMsg', 'Das Passwort wurde ge&auml;ndert!');
							}
							else{
								$view->assign('status', 'warning');
								$view->assign('statusMsg', 'Das Passwort wurde ge&auml;ndert, allerdings konnte die Email nicht versendet werden.');
							}

							// passwort auf steam �ndern
                                                        $steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
                                                        $ldap_module = $steamConnector->get_server_module("ldap");
                                                        $steamConnector->predefined_command($ldap_module, "uncache_user", array($request->getParameter('name')), 0);

						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');			
						}
					}
					
					// Fall: Es konnte keine Email-Adresse ermittelt werden, aber es wurde ein zufälliges
					// Passwort generiert.
					elseif($request->issetParameter('randomPW') AND $email == ''){
						$view->assign('status', 'warning');
						$view->assign('statusMsg', 'Dieser Benutzer hat keine Email-Adresse angegeben. Deswegen kann kein zuf&auml;lliges Passwort generiert werden!');			
	
					}
					
					// Fall: Es wurde manuell ein Passwort eingegeben.
					elseif(!$request->issetParameter('randomPW')){
						$done = $registry->get('ldapAccess')->replaceUserData($request->getParameter('name'), 'userPassword', $request->getParameter('passwordNew'));
						if($done != false){
							if($request->issetParameter('mail')) $mailStatus = $this->mailPW($request->getParameter('name'), $request->getParameter('passwordNew'));
							
							if($request->issetParameter('mail') AND $mailStatus == true){
								$view->assign('status', 'ok');
								$view->assign('statusMsg', 'Das Passwort wurde ge&auml;ndert und per Mail an den Benutzer gesendet!');
							}
							elseif($request->issetParameter('mail') AND $mailStatus == false){
								$view->assign('status', 'warning');
								$view->assign('statusMsg', 'Das Passwort wurde ge&auml;ndert jedoch hatte der Benutzer keine Email-Adresse angegeben!');
							}
							elseif(!$request->issetParameter('mail')){
								$view->assign('status', 'ok');
								$view->assign('statusMsg', 'Das Passwort wurde ge&auml;ndert!');
							}

							// passwort auf steam �ndern
							$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
							$ldap_module = $steamConnector->get_server_module("ldap");
							$steamConnector->predefined_command($ldap_module, "uncache_user", array($request->getParameter('name')), 0);
						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');			
						}
					}
				}
			}

			// Benutzeraktion: Suche nach Benutzernamen
			if($request->issetParameter('userSearch')){ 
				// Daten der SessionRegistry aktualisieren.
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
			
			// Ausgabe erzeugen
			$view->render($request, $response);
		}
		
		/*
		 * Diese Funktion generiert ein zufälliges Passwort und gibt dieses zurück. Die Länge dieses
		 * Passwortes lässt sich durch die Variable $passwortLength der Funktion ändern.
		 */
		private function randomPW(){
			$passwordLength = 8; 
  			$chars = ("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890");

  			for ($i=0; $i<$passwordLength; $i++) {
        		$password .= $chars{mt_rand (0, strlen($chars))};
  			}
			
  			return $password;
		}
		
		/*
		 * Versendet ein Passwort an die angegebene Email-Adresse. Gibt im Erfolgsfall TRUE, 
		 * ansonsten FALSE zurück.
		 */
		private function mailPW($email, $password){
			$subject = 'neues Passwort';
			$message = 'Passwort: '.$password.'\n';
			$header = 'From: webmaster@example.com'."\r\n".'Reply-To: webmaster@example.com'."\r\n".'X-Mailer: PHP/' . phpversion();
			
			return mail('dalucks@upb.de', $subject, $message, $header);
		}

	}
?>
