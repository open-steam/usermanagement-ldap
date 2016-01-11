<?php
/*
 * Command-Klasse zum Löschen von Gruppen. Die Gruppen werden endgültig gelöscht. 
 */
	class deleteGroupCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('deleteGroup');
			
			// Benutzeraktion: Gruppe löschen
			if($request->issetParameter('deleteGroup') AND $request->getParameter('deleteGroup') == 'true'){
				$view->assign('groupSelect', $request->getParameter('groupSelect'));
				
				if($request->issetParameter('acknowledgeResponse') AND $sessionRegistry->get('activeGroup') == $request->getParameter('groupSelect')){
					if($registry->get('ldapAccess')->hasSubgroups($request->getParameter('groupSelect')) == true){
						$done = $registry->get('ldapAccess')->removeGroup($request->getParameter('groupSelect'), true);
						if($done){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Die Gruppe und s&auml;mtliche ihrer Untergruppen wurden gel&ouml;scht!');
						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Es ist ein interner Fehler aufgetreten U!');
						}
					}
					else{
						$done = $registry->get('ldapAccess')->removeGroup($request->getParameter('groupSelect'));
						if($done){
							$view->assign('status', 'ok');
							$view->assign('statusMsg', 'Die Gruppe wurde gel&ouml;scht!');
						}
						else{
							$view->assign('status', 'warning');
							$view->assign('statusMsg', 'Es ist ein interner Fehler aufgetreten!');
						}
					}
				}
				else{
					$view->assign('acknowledgeRequest', 'true');
					if($registry->get('ldapAccess')->hasSubgroups($request->getParameter('groupSelect')) == true){
						$view->assign('status', 'warning');
						$view->assign('statusMsg', 'Achtung!<br>Diese Gruppe besitzt noch Untergruppen, welche automatisch mitgel&ouml;scht w&uuml;rden!');
					}
					else{
						$view->assign('status', 'warning');
						$view->assign('statusMsg', 'Diese Gruppe wirklich l&ouml;schen?');
					}
				}
				$sessionRegistry->set('activeGroup', $request->getParameter('groupSelect'));
			}
			
			// Template-Variablen Werte zurodnen
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());
			
			// Ausgabe erzeugen
			$view->render($request, $response);
		}
	}
?>