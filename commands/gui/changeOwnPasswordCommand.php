<?php
/*
 * Command-Klasse zum Ändern des eigenen Passwortes.
 */
	class changeOwnPasswordCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('changeOwnPassword');
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			
			// Benutzeraktion: eigenes Passwort ändern
			if($request->issetParameter('changePW')){
				
				// Eingabedaten auf Korrektheit prüfen
				$datacorrectness = true;
				if(!$request->issetParameter('passwordOld') OR !$request->issetParameter('passwordNew') OR !$request->issetParameter('passwordRetype')){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte alle Felder ausf&uuml;llen!');
				}
				elseif($request->getParameter('passwordOld') == '' OR $request->getParameter('passwordNew') == '' OR $request->getParameter('passwordRetype') == ''){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Bitte alle Felder ausf&uuml;llen!');
				}
				elseif($request->getParameter('passwordOld') != $sessionRegistry->get('userPW')){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Das alte Passwort war falsch!');
				}
				elseif($request->getParameter('passwordNew') != $request->getParameter('passwordRetype')){
					$datacorrectness = false;
					$view->assign('status', 'warning');
					$view->assign('statusMsg', 'Das neue Passwort und die Best&auml;tigung stimmen nicht &uuml;berein!');
				}
		
				if($datacorrectness){
					$done = $registry->get('ldapAccess')->replaceUserData($sessionRegistry->get('uid'), 'userPassword', $request->getParameter('passwordNew'));
					if($done){
						$sessionRegistry->set('userPW', $request->getParameter('passwordNew'));
						$view->assign('status', 'ok');
						$view->assign('statusMsg', 'Das Passwort wurde ge&auml;ndert!');

						// passwort auf steam �ndern
						$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
						$ldap_module = $steamConnector->get_server_module("ldap");
						$steamConnector->predefined_command($ldap_module, "uncache_user", array($sessionRegistry->get('uid')), 0);
					}
					else{
						$view->assign('status', 'warning');
						$view->assign('statusMsg', 'Systemfehler: LDAP-Kommando fehlgeschlagen!');							
					}
				}
			}
			
			// Ausgabe erzeugen
			$view->render($request, $response);
		}

	}
?>
