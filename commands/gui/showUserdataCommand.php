<?php
/*
 * Command-Klasse zum Anzeigen von Benutzerdaten.
 */
	class showUserdataCommand implements Command{

		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('showUserdata');
			
			// Variablen der Template-Dateien Werte zuordnen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroupsDN());

			// Suche nach Benutzernamen starten.
			if($request->issetParameter('userSearch')){ 
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
			if($request->issetParameter('userSelect')){
				$view->assign('userSelect', $request->getParameter('userSelect'));
				$view->assign('userdata', $registry->get('ldapAccess')->getUserInformation($request->getParameter('userSelect')));
			}

			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>