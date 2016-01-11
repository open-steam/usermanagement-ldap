<?php
/*
 * Command-Klasse zum wechseln der Schule.
 */
	class changeSchoolCommand implements Command{
	
		public function execute(Request $request, Response $response){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$view = new TemplateView('changeSchool');
			
			// Template-Variablen Werte zuweisen.
			$view->assign('accessLevel', $sessionRegistry->get('accessLevel'));
			$view->assign('districtList', $registry->get('ldapAccess')->getDistricts());
			if($request->issetParameter('districtSelect')) $view->assign('districtSelect', $request->getParameter('districtSelect'));
			
			// Benutzeraktion: Kreis gewählt.
			if($request->issetParameter('changeDistrict')){
				$view->assign('schoolList', $registry->get('ldapAccess')->getSchools($request->getParameter('districtSelect')));
			}
			
			// Benutzeraktion: Schule gewechselt.
			if($request->issetParameter('changeSchool')){
				$sessionRegistry->set('school', 'ou='.$request->getParameter('schoolSelect'));
				$sessionRegistry->set('district', 'ou='.$request->getParameter('districtSelect'));
				$view->assign('schoolList', $registry->get('ldapAccess')->getSchools($request->getParameter('districtSelect')));
				$view->assign('schoolSelect', str_replace('ou=', '', $sessionRegistry->get('school')));		
			}
			
			// Ausgabe erzeugen.
			$view->render($request, $response);
		}
	}
?>