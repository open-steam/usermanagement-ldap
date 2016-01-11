<?php
	set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/PHPsTeam');
        // Einbindung aller benötigten PHP-Dateien
	include_once 'class.GuiCommandResolver.php';
	include_once 'class.FrontController.php';
	include_once 'class.HttpRequest.php';
	include_once 'class.HttpResponse.php';
	include_once 'interface.Command.php';
	include_once 'class.Registry.php';
	include_once 'class.SessionRegistry.php';
	include_once 'class.TemplateView.php';
	include_once 'testclass.LdapAccess.php';
	include_once 'class.Configuration.php';
	include_once 'layoutFunctions.php';
	include_once '../PHPsTeam/steam_connector.class.php';
	include_once 'class.steamObjectUsertool.php';
	
	include_once '../PHPsTeam/steam_factory.class.php';

	$registry = Registry::getInstance();
	$registry->set('ldapAccess', new LdapAccess());
	$registry->set('configuration', new Configuration());
	$resolver = new GuiCommandResolver('commands/gui', 'login');
	$controller = new FrontController($resolver);
	
	$request = new HttpRequest();
	$response = new HttpResponse();
	
	// Beginn der Abarbeitung des Requests.
	$controller->handleRequest($request, $response);
?>
	
