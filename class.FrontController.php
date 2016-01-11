<?php
	/*
	 * Dies ist die zentrale Kontroll-Instanz der Anwendung. Hier werden die zentralen Programmlogiken
	 * ausgeführt.
	 */
	class FrontController{
	
		// Referenz auf die GuiCommandResolver-Instanz
		private $resolver;
		
		/*
		 * Konstruktor
		 * 
		 * $resolver:		Referenz auf den GuiCoammandResolver
		 */
		public function __construct(CommandResolver $resolver){
			$this->resolver = $resolver;
		}
		
		/*
		 * Hier werden die zentralen Programmlogiken implementiert/ausgeführt, und die Request- und
		 * Response-Objekte anschliessend an das Command-Objekt weitergegeben.
		 * 
		 * $request:		Referenz auf das Request-Objekt
		 * $response:		Referenz auf das Response-Objekt.
		 */
		public function handleRequest(Request $request, Response $response){
			
			$command = $this->resolver->getCommand($request);
			if(!$command instanceof LoginCommand){
				$sessionRegistry = SessionRegistry::getInstance();
				if($sessionRegistry->get('auth') == 'true'){
					$accessCheck = false;
					if($command instanceof NavigationCommand AND $sessionRegistry->get('accessLevel') >= 1) $accessCheck = true;
					if($command instanceof LogoutCommand AND $sessionRegistry->get('accessLevel') >= 1) $accessCheck = true;
					if($command instanceof ChangeOwnPasswordCommand AND $sessionRegistry->get('accessLevel') >= 1) $accessCheck = true;
					if($command instanceof ShowUserdataCommand AND $sessionRegistry->get('accessLevel') >= 2) $accessCheck = true;
					if($command instanceof CreateGroupCommand AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
					if($command instanceof ChangeGroupdataCommand AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
					if($command instanceof DeleteGroupCommand AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
					if($command instanceof ChangePasswordCommand AND $sessionRegistry->get('accessLevel') >= 3) $accessCheck = true;
					if($command instanceof ChangeUserdataCommand AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
					if($command instanceof CreateUserCommand AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
					if($command instanceof DeleteUserCommand AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
					if($command instanceof MoveToPoolCommand AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
					if($command instanceof GetFromPoolCommand AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
					if($command instanceof EmptyTrashCommand AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
					if($command instanceof UndeleteCommand AND $sessionRegistry->get('accessLevel') >= 4) $accessCheck = true;
					if($command instanceof ChangeSchoolCommand AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
					if($command instanceof CreateDistrictCommand AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
					if($command instanceof CreateSchoolCommand AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
					if($command instanceof LookUpCommand AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
					if($command instanceof UltimateMembershipOrganisationCommand AND $sessionRegistry->get('accessLevel') >= 5) $accessCheck = true;
						
					if($accessCheck) $command->execute($request, $response);	
					else{
						include_once 'commands/gui/accessDeniedCommand.php';
						$noAccCommand = new AccessDeniedCommand();
						$noAccCommand->execute($request, $response);
					}
					
					//$command->execute($request, $response);
				}
				else{
					include_once 'commands/gui/errorCommand.php';
					$errCommand = new ErrorCommand();
					$errCommand->execute($request, $response);
				}
				
			}
			else{
				$command->execute($request, $response);
			}
			$response->flush();
		}
	}
?>
