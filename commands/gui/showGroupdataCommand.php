<?php
/*
 * OBSOLETE
 * Command-Klasse zum Anzeigen von Gruppendaten.
 */
	class showGroupdataCommand implements Command{

		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('showGroupdata');
			
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('groupList', $registry->get('ldapAccess')->getGroups(false, true));
			if($request->issetParameter('groupSelect')) $view->assign('groupSelect', $request->getParameter('groupSelect'));
			
			// muss in jedes command, was mit gruppen zu tun hat, rein
			if($request->issetParameter('chooseGroup') AND $request->issetParameter('groupSelect')){
				$sessionRegistry->set('activeGroup', $request->getParameter('groupSelect'));
			}
			
			if($sessionRegistry->get('activeGroup') != null AND $sessionRegistry->get('activeGroup') != ''){
				$view->assign('groupdata', $registry->get('ldapAccess')->getGroupInformation($sessionRegistry->get('activeGroup')));
				$view->assign('memberlist', $registry->get('ldapAccess')->search('', $sessionRegistry->get('activeGroup')));
				if(!$request->issetParameter('chooseGroup')) $view->assign('groupSelect', $sessionRegistry->get('activeGroup'));
			}
			
			
			$view->render($request, $response);
		}
	}
?>