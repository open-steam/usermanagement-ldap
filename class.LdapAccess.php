<?php
	include_once 'interface.DatabaseAccess.php';
	
	/*
	 * Diese Klasse ist im Wesentlichen eine Sammlung aller Funktionen, die auf den LDAP-Server zugreifen.
	 */
	class LdapAccess implements DatabaseAccess{
	
		/*
		 * Authentifikation eines Bentzers, der sich am System anmelden will.
		 * 
		 * $user:		Bei der Anmeldung angegebener Benutzername.
		 * $password:	Bei der Anmeldung angegebenes Passwort.
		 * $time:		Zeitpunkt der Anmeldung.
		 *  
		 * return:		Bei erfolgreicher Authentifikation TRUE, ansonsten FALSE.		
		 */
		public function auth($user, $password, $time){
			// Wird erst in der Weiterentwicklung relevant. Hat zur Zeit noch keine Bedeutung.
			$currentTime = time();
			
			$connection = $this->establishConnection();
			$bind = ldap_bind($connection, $user, $password);
			@ldap_unbind($connection);
			
			return $bind;

		}
		/*
		 * Ermittelt den zu einem Benutzernamen gehörenden DN, und gibt diesen zurück.
		 * 
		 * $uid:		Benutzername, zu dem der DN ermittelt werden soll.
		 * 
		 * return:		Falls der DN ermittelt werden konnte, wird dieser zurückgegeben, ansonsten FALSE.
		 */
		public function getUserDN($uid){
			echo 'bla';
			$connection = $this->establishConnection();
			$registry = Registry::getInstance();			
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			echo $systemDN; echo $systemPassword;
			ldap_bind($connection, $systemDN, $systemPassword);
				$result = ldap_search($connection, $registry->get('configuration')->getRoot(), 'uid='.$uid);
				$size = ldap_count_entries($connection, $result);
				if($size === 1){
					$entry = ldap_get_entries($connection, $result);
					$returnValue = $entry[0]['dn'];
				}
				else{
					$returnValue = false;
				}
			ldap_unbind($connection);
			
			return $returnValue;
			
		}
		
		/*
		 * Diese Funktion prüft, ob ein Benutzer mit einem bestimmten Benutzernamen an der eigenen Schule existiert.
		 * 
		 * $uid:		Benutzername, den es zu überprüfen gilt.
		 * 
		 * return:		Falls ein Benutzer mit dem angegebenen Benutzernamen an der eigenen Schule existiert
		 * 				wird TRUE, ansonsten FALSE zurückgegeben.
		 */
		public function userExists($uid){
			$connection = $this->establishConnection();
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
				
			if($connection != false){
				ldap_bind($connection, $userDN, $userPW);
				$result = ldap_search($connection, $baseDN, 'uid='.$uid, array('dn'));
				$entries = ldap_get_entries($connection, $result);
				ldap_unbind($connection);
				
				if($entries['count'] === 1) return true;
				else return false;
			}
		}
		
		/*
		 * Diese Funktion prüft, ob an der eigenen Schule eine Gruppe mit einem bestimmten Gruppennamen existiert.
		 * 
		 * $groupname:		Zu überprüfender Gruppenname.
		 * 
		 * return:			Falls an der eigene Schule eine Gruppe mit dem angegebenen Namen existiert wird TRUE,
		 * 					ansonsten FALSE zurückgegeben.
		 */
		public function groupExists($groupname){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$connection = $this->establishConnection();
			
			if($connection != false){
				ldap_bind($connection, $userDN, $userPW);
				$result = ldap_search($connection, $baseDN, 'cn='.$groupname, array('dn'));
				$entries = ldap_get_entries($connection, $result);
				ldap_unbind($connection);
				
				if($entries['count'] === 1) return true;
				else return false;	
			}
		}
		
		/*
		 * Stellt eine Verbindung zum LDAP-Server her. 
		 * 
		 * return:		Verbindungs-Resource zum LDAP-Server.
		 */
		public function establishConnection(){
			$registry = Registry::getInstance();
			$host = $registry->get('configuration')->getHost();
			$port = $registry->get('configuration')->getPort();
			
			@$connection = ldap_connect($host);
			ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die ('Konnte nicht auf v3 setzen!');
			
			return $connection;
		}
	
		/*
		 * Diese Funktion startet eine Suche im LDAP-Verzeichnis und gibt die Suchergebnisse zurück.
		 * 
		 * $namefilter:		Spezifiziert einen (Teil-)String, welcher in den Benutzernamen vorkommen soll.
		 * $group:			Bestimmt den Bereich, in dem gesucht werden soll. Dies kann eine Gruppe, der POOL,
		 * 					Papierkorb sein. Es ist auch möglich, alle Gruppen zu durchsuchen, oder nach allen
		 * 					Gruppenlosen Benutzern.
		 * $oldSchool:		Wird dieser Parameter auf TRUE gesetzt, wird automatisch im POOL nach Benutzern, die von
		 * 					einer bestimmten Schule kamen, gesucht. Der Parameter $group gibt hierbei den Namen der
		 * 					alten Schule an.
		 * $timespan:		Nur für Suchen im Papierkorb relevant. Es kann eine Zeitspanne angegeben werden,
		 * 					die sich ein Benutzer mindestens im Papierkorb befinden muss.
		 * 
		 * return:			Gibt in einem Array die Suchergebnisse zurück. Konnten keine Ergebnisse ermittelt
		 * 					werden, wird ein leeres Array zurückgegeben.
		 */
		public function search($namefilter, $group, $oldSchool = false, $timespan = ''){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$data = array();
			
			// baseDN bestimmen
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			if($oldSchool) $baseDN = 'ou=POOL,'.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			if($group == 'TRASH') $baseDN = 'ou=TRASH,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			if($group == 'POOL') $baseDN = 'ou=POOL,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			
			$connection = $this->establishConnection();
			if($connection != false){
				
				// suchfilter bestimmen
				$filter = '(&(ou='.$group.')(objectClass=inetOrgPerson)';
				if($group == 'Alle_Gruppen') $filter = '(&(objectClass=inetOrgPerson)';
				if($group == 'Gruppenlos') $filter = '(&(objectClass=inetOrgPerson)(!(ou=*))';
				if($group == 'TRASH') $filter = '(&(objectClass=inetOrgPerson)';
				
				if($oldSchool){
					if($group == 'Alle_Schulen') $filter = '(&(objectClass=inetOrgPerson)';
					else $filter = '(&(description='.$group.')(objectClass=inetOrgPerson)';
				}
				
				if($namefilter == '') $filter .= '(uid=*))';
				else $filter .= '(uid=*'.$namefilter.'*))';
				
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, $filter, array('cn', 'uid', 'description'));
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1){
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
					}
					asort($data);
				}
				@ldap_unbind($connection);
				
				if($timespan != ''){
					$data = array();
					switch($timespan){
						case '1w':{
							for($i=0; $i<$entries['count']; $i++){
								if((time() - $entries[$i]['description'][0]) >= 604800){
									$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
								}
							}
							break;
						}
						case '1m':{
							for($i=0; $i<$entries['count']; $i++){
								if((time() - $entries[$i]['description'][0]) >= 2592000){
									$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
								}
							}
							break;							
						}
						case '3m':{
							for($i=0; $i<$entries['count']; $i++){
								if((time() - $entries[$i]['description'][0]) >= 7776000){
									$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
								}
							}	
							break;						
						}
						case '6m':{
							for($i=0; $i<$entries['count']; $i++){
								if((time() - $entries[$i]['description'][0]) >= 15552000){
									$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
								}
							}	
							break;						
						}
						case 'noLimit':{
							for($i=0; $i<$entries['count']; $i++){
								$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
							}
							break;
						}
					}
				}
			}
						
			return $data;
		}
		
		/*
		 * Liefert alle Schulnamen der Schulen, die dem angegebenen Kreis angehören.
		 * 
		 * $district:		Name des Kreises.
		 * 
		 * return:			Array mit allen Schulnamen, die dem Kreis angehören.
		 */
		public function getSchools($district){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou='.$district.','.$registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, '(&(objectClass=organizationalUnit)(description=Schule))', array('ou'));
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1){
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						$data[] = $entries[$i]['ou'][0];
					}
					asort($data);
				}
				@ldap_unbind($connection);
			}
			return $data;
		}
		
		/*
		 * Liefert die Namen aller Kreise.
		 * 
		 * return:		Gibt Array mit den Namen aller Kreise zurück.
		 */
		public function getDistricts(){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = $registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, '(&(objectClass=organizationalUnit)(description=Kreis))', array('ou'));
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1){
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						$data[] = $entries[$i]['ou'][0];
					}
					asort($data);
				}
				@ldap_unbind($connection);
			}
			return $data;
		}
		
		/*
		 * Liefert die Namen aller Schulen, die Benutzer in den Pool des Kreises verschoben haben, und sich
		 * noch dort befinden.
		 * 
		 * return:		Array mit den Schulnamen. Falls sich keine Benutzer im Pool befinden, wird ein
		 * 				leeres Array zurückgegeben.
		 */
		public function getOldSchools(){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=POOL,'.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, 'objectClass=inetOrgPerson', array('description'));
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1){
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						$data[] = $entries[$i]['description'][0];
					}
					$data[] = 'Alle_Schulen';
					$data = array_unique($data);
					asort($data);
				}
				@ldap_unbind($connection);
			}
				
			return $data;
		}
		
		/*
		 * Liefert ein Array mit den Namen aller Gruppen, die an der eigenen Schule existieren.
		 * 
		 * $allGroups:		Wird dieser Parameter auf TRUE gesetzt, wird dem Array der Eintrag
		 * 					'Alle_Gruppen' hinzugefügt.
		 * $groupless:		Wird dieser Parameter auf TRUE gesetzt, wird dem Array der Eintrag
		 * 					'Gruppenlos' hinzugefügt.
		 * $none:			Wird dieser Parameter auf TRUE gesetzt, wird dem Array der Eintrag
		 * 					'keine' hinzugefügt.
		 * 
		 * return:			Array mit den Gruppennamen und den evtl. hinzugefügten Einträgen.
		 */
		public function getGroups($allGroups = false, $groupless = false, $none = false){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, 'objectClass=groupOfURLs', array('cn'));
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1){
					@$entries = ldap_get_entries($connection, $result);
					if($allGroups) $data[] = 'Alle_Gruppen';
					if($groupless) $data[] = 'Gruppenlos';
					if($none) $data[] = 'keiner';
					for($i=0; $i<$entries['count']; $i++){
						$data[] = $entries[$i]['cn'][0];
					}
					//asort($data);
				}
				// Für den Fall, dass noch keine Gruppen existieren!
				else{
					if($groupless) $data[] = 'Gruppenlos';		
				}
				@ldap_unbind($connection);
			}			
			return $data;
		}
		public function getGroupsDN(){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, 'objectClass=groupOfURLs', array('cn'));
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1){
					@$entries = ldap_get_entries($connection, $result);

					$data = array();
					for($i=0; $i<$entries['count']; $i++){
						//$data[$entries[$i]['dn']] = $entries[$i]['cn'][0];
						
						$newDN = str_replace(strstr($entries[$i]['dn'], ',ou='), '', $entries[$i]['dn']);
						$tmp = explode(',', $newDN);
						$tmp = array_reverse($tmp);
						$newDN = implode(',', $tmp);
						$data[$newDN] = $entries[$i]['cn'][0];
					}
					
				
				}
				@ldap_unbind($connection);
			}		
			//foreach($data AS $key => $val) echo $key.': '.$val.'<br>';	
			return $data;
		}

		
		/*
		 * Liefert verschiedene Informationen über eine bestimmte Gruppe.
		 * 
		 * $groupname:		Name der Gruppe.
		 * 
		 * return:			Assoziatives Array mit den Informationen zu der angegebenen Gruppe:
		 * 					key		=>		value
		 * 					'name'			Gruppenname
		 * 					'owner'			Benutzername des Besitzers der Gruppe
		 * 					'description'	Gruppenbeschreibung
		 * 					'parent'		Name der Elterngruppe (sofern diese existiert)
		 */
		public function getGroupInformation($groupname){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, 'cn='.$groupname, array('dn', 'cn', 'owner', 'description'));
				@$size = ldap_count_entries($connection, $result); 
				if($size === 1){
					@$entries = ldap_get_entries($connection, $result);
					$tmp = explode(',', $entries[0]['owner'][0]);
					$owner = $tmp[0];
					$owner = str_replace('uid=', '', $owner);
					$parent = 'none';
					
					//überprüfen, ob die gruppe eine obergruppe hat, indem überprüft wird, ob das rdn-attribute cn oder ou ist.
					$tmp2 = explode(',', $entries[0]['dn']);
					$tmp3 = explode('=', $tmp2[1]);
					if($tmp3[0] == 'cn'){ 
						$isSubgroup = true;
						$parent = $tmp3[1];	
					}
					else $isSubgroup = false;
						
					$data['name'] = $entries[0]['cn'][0];
					$data['owner'] = $owner;
					$data['description'] = $entries[0]['description'][0];
					$data['parent'] = $parent;
				}
				@ldap_unbind($connection);
			}		
			return $data;
		}
		
		/*
		 * Speichern/Ändern von Informationen einer bestimmten Gruppe.
		 * 
		 * $currentGroup:		Name der Gruppe deren Daten geändert und gespeichert werden sollen.
		 * $data:				Assoziatives Array mit den neuen Informationen. Die Array-Keys müssen
		 * 						mit 'groupname' (neuer Gruppenname), 'owner' (neuer Besitzer), 'description'
		 * 						(neue Gruppenbeschreibung) oder 'parent' (neue elterngruppe) bezeichnet 
		 * 						werden.
		 * 
		 * return:				Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function setGroupInformation($currentGroup, $data){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			//acl aktualisieren!!!!
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			if(isset($data['groupname'])) $groupname = $data['groupname']; else $groupname = '';
			if(isset($data['owner'])) $owner = $data['owner']; else $owner = '';
			if(isset($data['description'])) $description = $data['description']; else $description = '';
			if(isset($data['parent'])) $parent = $data['parent']; else $parent = '';
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, $baseDN, 'cn='.$currentGroup, array('dn'));
				@$size = ldap_count_entries($connection, $result);
				
				if($size === 1){
					$entries = ldap_get_entries($connection, $result);
					$currentDN = $entries[0]['dn'];
					
					if($description != ''){
						@ldap_mod_replace($connection, $currentDN, array('description'=>$description));
					}
					if($owner != ''){
						$baseDN_user = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
						@$result3 = ldap_search($connection, $baseDN_user, 'uid='.$owner, array('dn'));
						@$entries3 = ldap_get_entries($connection, $result3);
						$ownerDN = $entries3[0]['dn'];
						@ldap_mod_replace($connection, $currentDN, array('owner'=>$ownerDN));
					}
					if($parent != '' OR $groupname != ''){ 
						if($parent != '' AND $parent != 'keiner'){
							$result2 = ldap_search($connection, $baseDN, 'cn='.$parent, array('dn'));
							$entries2 = ldap_get_entries($connection, $result2);
							$parentDN = $entries2[0]['dn'];	
						}
						else{
							$parentDN = $baseDN;
						}
						
						
						ldap_rename($connection, $currentDN, 'cn='.$groupname, $parentDN, true);
					}			
				}
				ldap_unbind($connection);
			}
			return true;
		}
		
		/*
		 * Liefert die Benutzernamen aller Mitglieder einer Gruppe.
		 * 
		 * $groupname:		Name der Gruppe, deren Mitglieder zurückgegeben werden sollen.
		 * 
		 * return:			Array mit den Benutzernamen aller Mitglieder dieser Gruppe. 
		 */
		public function getGroupMembers($groupname){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				if($groupname == 'Gruppenlos') $filter = '(&(objectClass=inetOrgPerson)(!(ou=*))';
				else $filter = 'ou='.$groupname;
				@$result = ldap_search($connection, $baseDN, $filter, array('cn', 'uid'));
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1){
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
					}
					asort($data);
				}
				@ldap_unbind($connection);
			}
			return $data;
		}

		
		
		/*
		 * Liefert Informationen über einen bestimmten Benutzer.
		 * 
		 * $uid:			Benutzername des Benutzers, dessen Informationen abgefragt werden sollen.
		 * 
		 * return:			Assoziatives Array mit den Informationen:
		 * 					key			=> 		value
		 * 					'uid'				Benutzername
		 * 					'givenname'			Vorname
		 * 					'surname'			Nachname
		 * 					'email'				Email-Adresse
		 * 					'school'			Schule
		 * 					'role'				Benutzerrolle
		 */
		public function getUserInformation($uid){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$data = array();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				@$result = ldap_search($connection, $baseDN, 'uid='.$uid, array('sn', 'givenname', 'mail', 'uid', 'createTimestamp'));
				@$size = ldap_count_entries($connection, $result);
				if($size == 1){
					$entries = ldap_get_entries($connection, $result);
					$tmp = explode(',' ,$entries[0]['dn']);
					$school = str_replace('ou=', '', $tmp[3]);
					$role = str_replace('ou=', '', $tmp[1]);
					
					$data['uid'] = $entries[0]['uid'][0];
					$data['givenname'] = $entries[0]['givenname'][0];
					$data['surname'] = $entries[0]['sn'][0];
					$data['email'] = $entries[0]['mail'][0];
					$data['school'] = $school;
					$data['role'] = $role;
					
					$data['timeStamp'] = $entries[0]['createTimestamp'][0];
				}
				ldap_unbind($connection);				
			}
			return $data;
		}
		
		/*
		 * Speichert/Ändert Informationen eines Benutzers.
		 * 
		 * $uid:			Benutzername des Benutzers, dessen Daten geändert werden.
		 * $givenname:		Neuer Vorname.
		 * $surname:		Neuer Nachname.
		 * $email:			Neue Email-Adresse.
		 * $role:			Neue Benutzerrolle.
		 * 
		 * return:			Im Erfolgsfall TRUE, ansonsten FALSE. 
		 */
		public function setUserInformation($uid, $givenname, $surname, $email, $role){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			//acl aktualisieren!!!!
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, $baseDN, 'uid='.$uid, array('dn'));
				@$size = ldap_count_entries($connection, $result);
				$status = true;
				
				if($size == 1){
					$entries = ldap_get_entries($connection, $result);
					$targetDN = $entries[0]['dn'];
					$tmp = explode(',', $targetDN);
					$targetRole = str_replace('ou=', '', $tmp[1]);
					if($surname != '') $status1 = @ldap_mod_replace($connection, $targetDN, array('sn'=>$surname));
					if($givenname != '') $status2 = @ldap_mod_replace($connection, $targetDN, array('givenname'=>$givenname));
					if($email != '') $status3 =@ldap_mod_replace($connection, $targetDN, array('mail'=>$email));
					if($surname != '' AND $givenname != '') $status4 = @ldap_mod_replace($connection, $targetDN, array('cn'=>$givenname.' '.$surname));
					if($role != '') $status5 = $this->moveUser($uid, $role);
					if($status1 === false AND $status2 === false AND $status3 === false AND $status4 === false AND $status5 === false) $status = false;
				}
				ldap_unbind($connection);
				return $status;
			}
		}
		
		/*
		 * Speichert den Wert für ein bestimmtes Attribut eines Benutzereintrages.
		 * 
		 * $uid:		Benutzername.
		 * $key:		Attribut-Bezeichnung.
		 * $value:		Zu speichernder Wert.
		 * 
		 * return:		Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function setUserData($uid, $key, $value){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			//acl aktualisieren!!!!
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, $baseDN, 'uid='.$uid, array('dn'));
				@$size = ldap_count_entries($connection, $result);
				if($size == 1){
					@$entries = ldap_get_entries($connection, $result);
					$targetDN = $entries[0]['dn'];
					if($value != '') $returnValue = @ldap_mod_add($connection, $targetDN, array($key=>$value));			
				}
				else{
					$returnValue = false;
				}
				@ldap_unbind($connection);
			}	
			return $returnValue;
		}
		
		/*
		 * Löscht den Wert eines bestimmten Attributes eines bestimmten Benutzers.
		 * 
		 * $uid:		Benutzername.
		 * $key:		Attribut-Bezeichnung.
		 * $value:		Der zu löschende Wert.
		 * 
		 * return:		Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function unsetUserData($uid, $key, $value){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			//acl aktualisieren!!!!
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, $baseDN, 'uid='.$uid, array('dn'));
				@$size = ldap_count_entries($connection, $result);
				if($size == 1){
					@$entries = ldap_get_entries($connection, $result);
					$targetDN = $entries[0]['dn'];
					if($value != '') $returnValue = @ldap_mod_del($connection, $targetDN, array($key=>$value));			
				}
				else{
					$returnValue = false;
				}
				@ldap_unbind($connection);
			}	
			return $returnValue;
		}
		
		/*
		 * Ersetzt den Wert eines Attributes eines bestimmten Benutzers.
		 * 
		 * $uid:		Benutzername.
		 * $key:		Bezeichnung des Attributes.
		 * $value:		Neuer Wert. Wird dieser Parameter nicht gesetzt, so wird der Wert des Attributes gelöscht.
		 * 
		 * return:		Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function replaceUserData($uid, $key, $value=''){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			//acl aktualisieren!!!!
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, $baseDN, 'uid='.$uid, array('dn'));
				@$size = ldap_count_entries($connection, $result);
				if($size == 1){
					@$entries = ldap_get_entries($connection, $result);
					$targetDN = $entries[0]['dn'];
					if($value != '') $returnValue = @ldap_mod_replace($connection, $targetDN, array($key=>$value));			
				}
				else{
					$returnValue = false;
				}
				@ldap_unbind($connection);
			}	
			return $returnValue;
		}
		
		/*
		 * Erstellt einen neuen Benutzer. Der Benutzername wird automatisch erstellt.
		 * 
		 * $givenname:		Vorname.
		 * $surname:		Nachname.
		 * $role:			Benutzerrolle.
		 * $email:			Email-Adresse.
		 * 
		 * return:			Im Erfolgsfall den Benutzernamen, ansonsten FALSE.
		 */
		public function createUser($givenname, $surname, $role, $email='', $defaultGroup=''){

			//mögliche leerzeichen entfernen
			$givenname = str_replace(' ', '', $givenname);
			$surname = str_replace(' ', '', $surname);
			$email = str_replace(' ', '', $email);
			
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$uid = strtolower($givenname[0].$surname);
			$index = 1;
			$isUnique = false;
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, $registry->get('configuration')->getRoot(), 'uid='.$uid);
				@$size = ldap_count_entries($connection, $result);
				if($size >= 1) $isUnique = false;
				else $isUnique = true;
				while(!$isUnique){
					@$result = ldap_search($connection, $registry->get('configuration')->getRoot(), 'uid='.$uid.$index);
					@$size = ldap_count_entries($connection, $result);
					if($size >= 1) $isUnique = false;
					else{ 
						$isUnique = true;
						$uid = $uid.$index;
					}
					$index++;
				}
				$password = $uid;
			@ldap_unbind($connection);
			}

			//die ACL noch anpassen, weil erstellter user hat noch kein schreibrecht!
			$userDN = 'cn=admin,o=bidowl,dc=upb,dc=de';
			$userPW = 'ldap.bidowl';
			$DN = 'uid='.$uid.',ou='.$role.',ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
						
			$data['objectClass'][0] = 'person';
			$data['objectClass'][1] = 'organizationalPerson';
			$data['objectClass'][2] = 'inetOrgPerson';
			$data['sn'] = $surname;
			$data['cn'] = $givenname.' '.$surname;
			$data['givenname'] = $givenname;
			$data['uid'] = $uid;
			$data['userPassword'] = $uid;
			if($email != '') $data['mail'] = $email;
			if($defaultGroup != '' AND $defaultGroup != '---') $data['ou'][0] = $defaultGroup;
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				$addStatus = @ldap_add($connection, $DN, $data);
				@ldap_unbind($connection);	
			}
			
			if($addStatus != false) return $uid;
			else return $addStatus;

		}
		
		/*
		 * Löscht einen Benutzer. ACHTUNG: Dieser Benutzer wird endgültig aus dem LDAP-Verzeichnis entfernt!
		 * 
		 * $uid:		Benutzername des zu löschenden Benutzers.
		 * 
		 * return:		Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function removeUser($uid){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				$status = @ldap_delete($connection, 'uid='.$uid.',ou=TRASH,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot());
				@ldap_unbind($connection);
			}
			
			return $status;
		}
		
		/*
		 * Verschiebt einen Benutzereintrag im LDAP-Verzeichnis.
		 * 
		 * $uid:		Benutzername des zu verschiebenen Benutzers.
		 * $target:		Ziel des neuen Eintrages in Form des entsprechenden DNs.
		 * 
		 * return:		Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function moveUser($uid, $target){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$timestamp = time();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				if($target == 'TRASH') $newParentDN = 'ou=TRASH,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
				if($target == 'POOL') $newParentDN = 'ou=POOL,'.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
				if($target != 'TRASH' AND $target != 'POOL') $newParentDN = 'ou='.$target.',ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			
				$result = @ldap_search($connection, $sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), 'uid='.$uid, array('dn'));
				$entries = @ldap_get_entries($connection, $result);
				$userDN = $entries[0]['dn'];
				//timestamp in description setzen:
				if($target == 'TRASH') @ldap_mod_replace($connection, $userDN, array('description'=>$timestamp));
				
				$returnValue = @ldap_rename($connection, $userDN, 'uid='.$uid, $newParentDN, true);
				@ldap_unbind($connection);
			}
			return $returnValue;
		}
		
		/*
		 * Erstellt eine neue Gruppe.
		 * 
		 * $name:			Name der Gruppe.
		 * $owner:			Besitzer der Gruppe.
		 * $description:	Gruppenbeschreibung.
		 * $parent:			Name der Elterngruppe (sofern diese existiert)
		 * 
		 * return:			Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function createGroup($name, $owner, $description, $parent='---'){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();		
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			if($description == '') $description = 'Keine Beschreibung';
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result2 = ldap_search($connection, 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), 'uid='.$owner);
				@$entries = ldap_get_entries($connection, $result2);
				$ownerDN = $entries[0]['dn'];
				if($parent != '---'){
					@$result3 = ldap_search($connection, 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), 'cn='.$parent);
					@$entries3 = ldap_get_entries($connection, $result3);
					$parentDN = $entries3[0]['dn'];
				}
				@ldap_unbind($connection);
			}
				
			//die ACL noch anpassen, weil erstellter user hat noch kein schreibrecht!
			$userDN = 'cn=admin,o=bidowl,dc=upb,dc=de';
			$userPW = 'ldap.bidowl';
			$memberURL = 'ldap:///ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot().'??sub?(&(ou='.$name.')(objectClass=inetOrgPerson))';
						
			$data['objectClass'][0] = 'groupOfURLs';
			$data['objectClass'][1] = 'top';
			$data['cn'] = $name;
			$data['owner'] = $ownerDN;
			$data['memberURL'] = $memberURL;
			$data['description'] = $description; 
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $userDN, $userPW);
				if($parent != '---') $returnValue = ldap_add($connection, 'cn='.$name.','.$parentDN, $data);
				else $returnValue = ldap_add($connection, 'cn='.$name.',ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), $data);
				@ldap_unbind($connection);	
			}
			
			return $returnValue;
		}
		
		/*
		 * Löscht eine Gruppe aus dem LDAP-Verzeichnis.
		 * 
		 * $groupname:		Name der zu löschenden Gruppe.
		 * 
		 * return:			Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function removeGroup($groupname, $subtree = false){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			$connection = $this->establishConnection();
			
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), 'cn='.$groupname, array('dn'));
				@$entries = ldap_get_entries($connection, $result);
				$groupDN = $entries[0]['dn'];
			 	
				if($subtree){				
					// die ou-Attribute aus den user löschen
					@$searchResults = ldap_search($connection, $groupDN, 'objectClass=groupOfURLs', array('dn', 'cn'));
					@$groupEntries = ldap_get_entries($connection, $searchResults);
					$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
				
					for($i=0; $i<$groupEntries['count']; $i++){
						$groupCN = $groupEntries[$i]['cn'][0];
						$filter = '(&(objectClass=inetOrgPerson)(ou='.$groupCN.'))';
						@$userSearchResults = ldap_search($connection, $baseDN, $filter, array('dn'));
						@$userEntries = ldap_get_entries($connection, $userSearchResults);
						for($j=0; $j<$userEntries['count']; $j++){
							//return false;
							@ldap_mod_del($connection, $userEntries[$j]['dn'], array('ou'=>$groupCN)) or die ('ou konnte nicht entfernt werden');
						}
					}
					
					
					
					
					
					// Die Einträge entfernen
					$returnValue = $this->recursiveDelete($connection, $groupDN);
				}
				else{
					// ou-Attribut aus usern löschen
					$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					$filter = '(&(objectClass=inetOrgPerson)(ou='.$groupname.'))';
					@$result = ldap_search($connection, $baseDN, $filter, array('dn'));
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						@ldap_mod_del($connection, $entries[$i]['dn'], array('ou'=>$groupname));
					}
					//-----
								
					@$returnValue = ldap_delete($connection, $groupDN);
				}
				@ldap_unbind($connection);			
			}
			return $returnValue;	
		}
		
		/*
		 * Funktion zum rekursiven Löschen eines Teilbaumes des LDAP-Verzeichnisses.
		 * 
		 * $connection:		Verbindungskennung zum LDAP-Server.
		 * $dn:				Wurzel des zu löschenden Teilbaumes.
		 */
		public function recursiveDelete($connection, $dn){
       		$result = ldap_list($connection, $dn, "objectClass=groupOfURLs", array(''));
       		$entries = ldap_get_entries($connection, $result);
       		for($i=0; $i<$entries['count']; $i++){
           		$status = $this->recursiveDelete($connection, $entries[$i]['dn']);
           		if(!$status) return($status);
       		}
		
       		return ldap_delete($connection, $dn);
		}
		
		/*
		 * Prüft, ob eine Gruppe noch Untergruppen besitzt.
		 * 
		 * $groupname:		Name der zu überprüfenden Gruppe.
		 * 
		 * return:			Falls die angegebene Gruppe Untergruppen besitzt TRUE, falls nicht FALSE.
		 */
		public function hasSubgroups($groupname){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$connection = $this->establishConnection();
			
			if($connection != false){	
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), 'cn='.$groupname, array('dn'));
				@$entries = ldap_get_entries($connection, $result);
				$groupDN = $entries[0]['dn'];
			
				@$result2 = ldap_search($connection, $groupDN, 'objectClass=groupOfURLs', array('dn'));
				@$entries2 = ldap_get_entries($connection, $result2);
				$size = $entries2['count'];
				@ldap_unbind($connection);
			}
			if($size === 1) return false;
			else return true;
		}
		
	public function getParentGroupname($groupname){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$connection = $this->establishConnection();
			
			if($connection != false){	
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), 'cn='.$groupname, array('dn'));
				@$entries = ldap_get_entries($connection, $result);
				$groupDN = $entries[0]['dn'];
			
				$tmp = str_replace('cn='.$groupname.',', '', $groupDN);
				//echo $tmp;
				$tmpArr = explode(',', $tmp);
				$tmpArr = array_reverse($tmpArr);
				$parentName = array_pop($tmpArr);
				if(strpos($parentName, 'ou=') === 0) return '---';
				else return str_replace('cn=', '', $parentName);
				
				
				@ldap_unbind($connection);
			}
	}
		
	public function createDistrict($districtName){
		$sessionRegistry = SessionRegistry::getInstance();
		$registry = Registry::getInstance();
		
		$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
		$systemPW = $registry->get('configuration')->getSystemPassword();
		
		
		$DN_district = 'ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_district['objectClass'][0] = 'organizationalUnit';
		$data_district['ou'][0] = $districtName;
		$data_district['description'] = 'Kreis';
		
		$DN_pool = 'ou=POOL'.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_pool['objectClass'][0] = 'organizationalUnit';
		$data_pool['ou'][0] = 'POOL';
	
		$connection = $this->establishConnection();
		
		if($connection != false){
			ldap_bind($connection, $systemDN, $systemPW);
			$status1 = ldap_add($connection, $DN_district, $data_district);
			$status2 = ldap_add($connection, $DN_pool, $data_pool);
			ldap_unbind($connection);	
		}
		
		if($status1 AND $status2) return true; else return false;
	}
	
	public function createSchool($schoolName, $districtName){
		$sessionRegistry = SessionRegistry::getInstance();
		$registry = Registry::getInstance();
		
		$systemDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
		$systemPW = $registry->get('configuration')->getSystemPassword();
		
		
		$DN_school = 'ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_school['objectClass'][0] = 'organizationalUnit';
		$data_school['ou'][0] = $schoolName;
		$data_school['description'] = 'Schule';
		
		$DN_trash = 'ou=TRASH,'.'ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_trash['objectClass'][0] = 'organizationalUnit';
		$data_trash['ou'][0] = 'TRASH';
				
		$DN_user = 'ou=user,'.'ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_user['objectClass'][0] = 'organizationalUnit';
		$data_user['ou'][0] = 'user';
				
		$DN_groups = 'ou=groups,'.'ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_groups['objectClass'][0] = 'organizationalUnit';
		$data_groups['ou'][0] = 'groups';
		
		$DN_student = 'ou=student,ou=user,ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_student['objectClass'][0] = 'organizationalUnit';
		$data_student['ou'][0] = 'student';
		
		$DN_teacher = 'ou=teacher,ou=user,ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_teacher['objectClass'][0] = 'organizationalUnit';
		$data_teacher['ou'][0] = 'teacher';
		
		$DN_groupAdmin = 'ou=groupAdmin,ou=user,ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_groupAdmin['objectClass'][0] = 'organizationalUnit';
		$data_groupAdmin['ou'][0] = 'groupAdmin';
		
		$DN_schoolAdmin = 'ou=schoolAdmin,ou=user,ou='.$schoolName.',ou='.$districtName.','.$registry->get('configuration')->getRoot();			
		$data_schoolAdmin['objectClass'][0] = 'organizationalUnit';
		$data_schoolAdmin['ou'][0] = 'schoolAdmin';
	
		$connection = $this->establishConnection();
		
		if($connection != false){
			ldap_bind($connection, $systemDN, $systemPW);
			$status1 = ldap_add($connection, $DN_school, $data_school);
			$status2 = ldap_add($connection, $DN_user, $data_user);
			$status3 = ldap_add($connection, $DN_groups, $data_groups);
			$status4 = ldap_add($connection, $DN_trash, $data_trash);
			$status5 = ldap_add($connection, $DN_student, $data_student);
			$status6 = ldap_add($connection, $DN_teacher, $data_teacher);
			$status7 = ldap_add($connection, $DN_groupAdmin, $data_groupAdmin);
			$status8 = ldap_add($connection, $DN_schoolAdmin, $data_schoolAdmin);
			ldap_unbind($connection);	
		}
		
		if($status1 AND $status2 AND $status3 AND $status4 AND $status5 AND $status6 AND $status7 AND $status8) return true;
		else return false;
	}
}
?>