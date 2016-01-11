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
			@$bind = ldap_bind($connection, $user, $password);
			@ldap_unbind($connection);
			
			return $bind;

		}
		/*
		 * Ermittelt den zu einem Benutzernamen geh�renden DN, und gibt diesen zur�ck.
		 * 
		 * $uid:		Benutzername, zu dem der DN ermittelt werden soll.
		 * 
		 * return:		Falls der DN ermittelt werden konnte, wird dieser zur�ckgegeben, ansonsten FALSE.
		 */
		public function getUserDN($uid){
			$connection = $this->establishConnection();
			$registry = Registry::getInstance();			
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
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
		 * Diese Funktion pr�ft, ob ein Benutzer mit einem bestimmten Benutzernamen an der eigenen Schule existiert.
		 * 
		 * $uid:		Benutzername, den es zu �berpr�fen gilt.
		 * 
		 * return:		Falls ein Benutzer mit dem angegebenen Benutzernamen an der eigenen Schule existiert
		 * 				wird TRUE, ansonsten FALSE zur�ckgegeben.
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
		 * Diese Funktion pr�ft, ob ein Benutzer ein System-Administrator ist
		 * 
		 * $uid:		Benutzername der zu �berpr�fenden Person
		 * 
		 * return:		true/false
		 */
		public function isSystemAdmin($uid){
			$connection = $this->establishConnection();
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$targetUserDN = $this->getUserDN($uid);
			
			if($connection != false AND $targetUserDN != false){
				
				// Falls Benutzer kein Schuladministrator ist:
				if(strpos($targetUserDN, 'ou=schoolAdmin') === false){
					return false;
				}
				
				// Benutzer ist Schuladministrator
				else{
					ldap_bind($connection, $userDN, $userPW);
					$result = ldap_search($connection, $targetUserDN, '(&(uid='.$uid.')(title=systemAdmin))', array('dn'));
					
					if(ldap_count_entries($connection, $result) === 1){
						@ldap_unbind($connection);
						return true;
					}
					else{
						@ldap_unbind($connection);
						return false;
					}
				}
													
			}
			return false;			
		}
		
		/*
		 * Diese Funktion pr�ft, ob an der eigenen Schule eine Gruppe mit einem bestimmten Gruppennamen existiert.
		 * 
		 * $groupname:		Zu �berpr�fender Gruppenname.
		 * 
		 * return:			Falls an der eigene Schule eine Gruppe mit dem angegebenen Namen existiert wird TRUE,
		 * 					ansonsten FALSE zur�ckgegeben.
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
			
			@$connection = ldap_connect($host, $port);
			ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die ('Konnte nicht auf v3 setzen!');
			
			return $connection;
		}
		
		private function getGroupDN($name, $connection){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();

			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();

			$result = ldap_search($connection, $baseDN, 'cn='.$name, array('dn'));
			$size = ldap_count_entries($connection, $result);
			
			if($size === 1){
				$entries = ldap_get_entries($connection, $result);
				return $entries[0]['dn'];
			}
			//else echo 'debug-message: suche nach gruppe nicht eindeutig<br>';
		}
		
		public function getGroupDN_2($name){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			
			// verbindung aufbauen
			$connection = $this->establishConnection();
			if($connection != false){
				ldap_bind($connection, $userDN, $userPW);
				
				$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
				$result = ldap_search($connection, $baseDN, 'cn='.$name, array('dn'));
				$size = ldap_count_entries($connection, $result);
			
				if($size === 1){
					$entries = ldap_get_entries($connection, $result);
					return $entries[0]['dn'];
				}
				else return false;
			}
			else return false;
		}
	
		/*
		 * Diese Funktion startet eine Suche im LDAP-Verzeichnis und gibt die Suchergebnisse zur�ck.
		 * 
		 * $namefilter:		Spezifiziert einen (Teil-)String, welcher in den Benutzernamen vorkommen soll.
		 * $group:			Bestimmt den Bereich, in dem gesucht werden soll. Dies kann eine Gruppe, der POOL,
		 * 					Papierkorb sein. Es ist auch m�glich, alle Gruppen zu durchsuchen, oder nach allen
		 * 					Gruppenlosen Benutzern.
		 * $oldSchool:		Wird dieser Parameter auf TRUE gesetzt, wird automatisch im POOL nach Benutzern, die von
		 * 					einer bestimmten Schule kamen, gesucht. Der Parameter $group gibt hierbei den Namen der
		 * 					alten Schule an.
		 * $timespan:		Nur f�r Suchen im Papierkorb relevant. Es kann eine Zeitspanne angegeben werden,
		 * 					die sich ein Benutzer mindestens im Papierkorb befinden muss.
		 * 
		 * return:			Gibt in einem Array die Suchergebnisse zur�ck. Konnten keine Ergebnisse ermittelt
		 * 					werden, wird ein leeres Array zur�ckgegeben.
		 */
		public function search($namefilter, $group, $oldSchool = '', $timespan = ''){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$data = array();
			$membershipAttr = $registry->get('configuration')->getMemberAttribute();
			
			
			// verbindung aufbauen
			$connection = $this->establishConnection();
			ldap_bind($connection, $userDN, $userPW);
			
			
			// suchfilter und baseDN bestimmen
			switch($group){
				case 'Alle_Gruppen':{
					$filter = '(&(objectClass=inetOrgPerson)';
					$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					break;	
				}
				case 'Gruppenlos':{
					//$filter = '(&(objectClass=inetOrgPerson)(!('.$membershipAttr.'=*))';
					$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					$memberAttr = $registry->get('configuration')->getMemberAttribute();					
					$baseDN_tmp = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					$result_tmp = ldap_search($connection, $baseDN_tmp, 'objectClass=groupOfURLs');
					$entries_tmp = ldap_get_entries($connection, $result_tmp);
					if($entries_tmp['count'] >= 1){
						$filter = '(&(objectClass=inetOrgPerson)';
						for($i=0; $i<$entries_tmp['count']; $i++){
							$filter .= '(!('.$memberAttr.'='.$entries_tmp[$i]['dn'].'))';
						}
						//$filter .= ')';
					}
					else{
						$filter = '(objectClass=inetOrgPerson)';
					}
					break;
				}
				case 'TRASH':{
					$filter = '(&(objectClass=inetOrgPerson)';
					$baseDN = 'ou=TRASH,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					break;
				}
				case 'POOL':{
					$filter = '(&(objectClass=inetOrgPerson)';
					if($oldSchool != ''AND $oldSchool != 'Alle_Schulen') $filter .= '(description='.$oldSchool.')';
					$baseDN = 'ou=POOL,'.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					break;
				}
				default:{ 
					$filter = '(&('.$membershipAttr.'='.$this->getGroupDN($group, $connection).')';
					$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					break;
				}
			}

			if($namefilter == '') $filter .= '(uid=*))';
			else $filter .= '(uid=*'.$namefilter.'*))';
			
			// abfrage starten
			$result = ldap_search($connection, $baseDN, $filter, array('cn', 'uid', 'description'));
			// antwort auswerten
			if(ldap_count_entries($connection, $result) >= 1){
				$entries = ldap_get_entries($connection, $result);
				if($group == 'TRASH' AND $timespan != ''){
					switch($timespan){
						case '1w':{ $limit = 604800; break; }
						case '1m':{ $limit = 2592000; break; }
						case '3m':{ $limit = 7776000; break; }
						case '6m':{ $limit = 15552000; break; }
						default:{ $limit = 0; break; }
					}
					for($i=0; $i<$entries['count']; $i++){
						if((time() - $entries[$i]['description'][0]) >= $limit){
							$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
						}
					}
				}
				else{
					for($i=0; $i<$entries['count']; $i++){
						$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
					}
				}
				ksort($data);
			}

			
			// verbindung trennen
			ldap_unbind($connection);
			
			
			// suchergebnisse zur�ckgeben
			return $data;
		}
		
		/*
		 * Liefert alle Schulnamen der Schulen, die dem angegebenen Kreis angeh�ren.
		 * 
		 * $district:		Name des Kreises.
		 * 
		 * return:			Array mit allen Schulnamen, die dem Kreis angeh�ren.
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
		 * return:		Gibt Array mit den Namen aller Kreise zur�ck.
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
		 * 				leeres Array zur�ckgegeben.
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
		 * 					'Alle_Gruppen' hinzugef�gt.
		 * $groupless:		Wird dieser Parameter auf TRUE gesetzt, wird dem Array der Eintrag
		 * 					'Gruppenlos' hinzugef�gt.
		 * $none:			Wird dieser Parameter auf TRUE gesetzt, wird dem Array der Eintrag
		 * 					'keine' hinzugef�gt.
		 * 
		 * return:			Array mit den Gruppennamen und den evtl. hinzugef�gten Eintr�gen.
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
				// F�r den Fall, dass noch keine Gruppen existieren!
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
		 * Liefert verschiedene Informationen �ber eine bestimmte Gruppe.
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
					
					//�berpr�fen, ob die gruppe eine obergruppe hat, indem �berpr�ft wird, ob das rdn-attribute cn oder ou ist.
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
		 * Speichern/�ndern von Informationen einer bestimmten Gruppe.
		 * 
		 * $currentGroup:		Name der Gruppe deren Daten ge�ndert und gespeichert werden sollen.
		 * $data:				Assoziatives Array mit den neuen Informationen. Die Array-Keys m�ssen
		 * 						mit 'groupname' (neuer Gruppenname), 'owner' (neuer Besitzer), 'description'
		 * 						(neue Gruppenbeschreibung) oder 'parent' (neue elterngruppe) bezeichnet 
		 * 						werden.
		 * 
		 * return:				Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function setGroupInformation_obsolete($currentGroup, $data){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$userDN = $sessionRegistry->get('userDN');
			$userPW = $sessionRegistry->get('userPW');
			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			//acl aktualisieren!!!!
			$systemDN = $registry->get('configuration')->getSystemLogin();
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
				$parentDN = '';
				$currentDN = '';

				if($size === 1){
					$entries = ldap_get_entries($connection, $result);
					$currentDN = $entries[0]['dn'];
					
					if($description != ''){
						@ldap_mod_replace($connection, $currentDN, array('description'=>$description));
					}
					if($owner != ''){
						$baseDN_user = $registry->get('configuration')->getRoot();
						@$result3 = ldap_search($connection, $baseDN_user, 'uid='.$owner, array('dn'));
						@$entries3 = ldap_get_entries($connection, $result3);
						$ownerDN = $entries3[0]['dn'];
						@ldap_mod_replace($connection, $currentDN, array('owner'=>$ownerDN));
					}
					/*
					if($parent != ''){
						if($parent == '---'){
							$parentDN = $baseDN;
						}
						else{
							$result2 = ldap_search($connection, $baseDN, 'cn='.$parent, array('dn'));
							$entries2 = ldap_get_entries($connection, $result2);
							$parentDN = $entries2[0]['dn'];							
						}
						ldap_rename($connection, $currentDN, 'cn='.$groupname, $parentDN, true);
					}
					*/
					if($groupname != '' AND $groupname != $currentGroup){
						$result2 = ldap_search($connection, $baseDN, 'cn='.$currentGroup);
						$entries2 = ldap_get_entries($connection, $result2);
						$oldDN = $entries2[0]['dn'];
						
						//pr�fen, ob die gruppe noch untergruppen besitzt
						$result4 = ldap_search($connection, $oldDN, 'objectClass=groupOfURLs');
						$entries4 = ldap_get_entries($connection, $result4);
			
						// falls subtree
						if($entries4['count'] > 1){
								// subtree kopieren
								for($i=0; $i<$entries4['count']; $i++){
									$data = array();
									$data['objectClass'][0] = 'groupOfURLs';
									$data['objectClass'][1] = 'top';
									if($i == 0) $data['cn'] = $groupname;
									else $data['cn'] = $entries4[$i]['cn'][0];
									$data['owner'] = $entries4[$i]['owner'][0];
									$data['memberURL'] = str_replace('cn='.$currentGroup, 'cn='.$groupname, $entries4[$i]['memberurl'][0]);
									$data['description'] = $entries4[$i]['description'][0]; 					
									$dn = str_replace('cn='.$currentGroup, 'cn='.$groupname, $entries4[$i]['dn']);
									ldap_add($connection, $dn, $data);
									
									// gruppenverlinkungen anpassen
									$result5 = ldap_search($connection, $registry->get('configuration')->getRoot(), '(&(objectClass=inetOrgPerson)(seeAlso='.$entries4[$i]['dn'].'))');
									$entries5 = ldap_get_entries($connection, $result5);
									for($j=0; $j<$entries5['count']; $j++){
										ldap_mod_del($connection, $entries5[$j]['dn'], array('seeAlso' => $entries4[$i]['dn']));
										ldap_mod_add($connection, $entries5[$j]['dn'], array('seeAlso' => $dn));
									}
									
								}
								//alten subtree l�schen
								for($i=$entries4['count']-1; $i>=0; $i--){
									ldap_delete($connection, $entries4[$i]['dn']);
								}
						}
						else{
							$newMemberURL = str_replace('cn='.$currentGroup, 'cn='.$groupname, $entries2[0]['memberurl'][0]);
							ldap_mod_replace($connection, $oldDN, array('memberURL' => $newMemberURL));
							ldap_rename($connection, $oldDN, 'cn='.$groupname, str_replace('cn='.$currentGroup.',', '', $oldDN), true);

							@$result = ldap_search($connection, $registry->get('configuration')->getRoot(), '(&(objectClass=inetOrgPerson)(seeAlso='.$oldDN.'))');
		                                        @$entries = ldap_get_entries($connection, $result);
                		                        for($i=0; $i<$entries['count']; $i++){
                                	                ldap_mod_del($connection, $entries[$i]['dn'], array('seeAlso' => $oldDN));
                                        	        ldap_mod_add($connection, $entries[$i]['dn'], array('seeAlso' => str_replace('cn='.$currentGroup, 'cn='.$groupname, $oldDN)));

                                        }

						}
						
						
					}
					
					
					// Gruppenverlinkungen in den Benutzereintr�gen aktualisieren
					@$result = ldap_search($connection, $registry->get('configuration')->getRoot(), '(&(objectClass=inetOrgPerson)(seeAlso='.$currentDN.'))');
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						@ldap_mod_del($connection, $entries[$i]['dn'], array('seeAlso' => $currentDN));
						@ldap_mod_add($connection, $entries[$i]['dn'], array('seeAlso' => 'cn='.$groupname.','.$parentDN));
						
					}			
				}
				ldap_unbind($connection);
			}
			return true;
		}

			function setGroupInformation($group_name, $data){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$baseDN = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			if(isset($data['groupname'])) $group_name_new = $data['groupname']; else $group_name_new = '';
			if(isset($data['owner'])) $owner_new = $data['owner']; else $owner_new = '';
			if(isset($data['description'])) $description_new = $data['description']; else $description_new = '';
			if(isset($data['parent'])) $parent_new = $data['parent']; else $parent_new = '';
			
			//echo $description_new;
			
			$connection = $this->establishConnection();
			if($connection != false){
				ldap_bind($connection, $systemDN, $systemPassword);
				$result = ldap_search($connection, $baseDN, 'cn='.$group_name);
				$entries = ldap_get_entries($connection, $result);
				
				if($entries['count'] === 1){
					
					// gruppenbeschreibung �ndern
					if($description_new != ''){
						ldap_mod_replace($connection, $entries[0]['dn'], array('description' => $description_new));
					}
					
					// besitzer �ndern
					if($owner_new != ''){
						$baseDN_owner = $registry->get('configuration')->getRoot();
						$result_owner = ldap_search($connection, $baseDN_owner, 'uid='.$owner_new, array('dn'));
						$entries_owner = ldap_get_entries($connection, $result_owner);
						
						if($entries_owner['count'] === 1){
							ldap_mod_replace($connection, $entries[0]['dn'], array('owner' => $entries_owner[0]['dn']));
						}
					}
					
					// gruppennamen �ndern
					if($group_name_new != '' AND $group_name_new != $group_name){
			
						// steam-umbenennung
						$this->rename_group_on_steam($entries[0]['dn'], $group_name_new);						
			
						// old DN => new DN
						$changed_groups = array();
						
						// pr�fe, ob gruppe noch untergruppen besitzt
						$result_subgroups = ldap_search($connection, $entries[0]['dn'], 'objectClass=groupOfURLs');
						$entries_subgroups = ldap_get_entries($connection, $result_subgroups);
						
						// hat keine untergruppen
						if($entries_subgroups['count'] == 1){
							// memberURL-Attribut �ndern
							$memberURL_new = str_replace('cn='.$group_name, 'cn='.$group_name_new, $entries[0]['memberurl'][0]);
							ldap_mod_replace($connection, $entries[0]['dn'], array('memberURL' => $memberURL_new));
							
							// gruppe umbenennen
							$parentDN = str_replace('cn='.$group_name.',', '', $entries[0]['dn']);
							ldap_rename($connection, $entries[0]['dn'], 'cn='.$group_name_new, $parentDN, true);
							
							// ins array
							$changed_groups[$entries[0]['dn']] = 'cn='.$group_name_new.','.$parentDN;
						}
						// hat untergruppen
						elseif($entries_subgroups['count'] > 1){
							// subtree kopieren
							for($i=0; $i<$entries_subgroups['count']; $i++){
								
								// daten f�r den zu kopierenden eintrag ermitteln
								$data = array();
								$data['objectClass'][0] = 'groupOfURLs';
								$data['objectClass'][1] = 'top';
								if($i==0) $data['cn'] = $group_name_new; else $data['cn'] = $entries_subgroups[$i]['cn'][0];
								$data['owner'] = $entries_subgroups[$i]['owner'][0];
								$data['memberURL'] = str_replace('cn='.$group_name, 'cn='.$group_name_new, $entries_subgroups[$i]['memberurl'][0]);
								$data['description'] = $entries_subgroups[$i]['description'][0]; 					
								$dn = str_replace('cn='.$group_name, 'cn='.$group_name_new, $entries_subgroups[$i]['dn']);

								// eintrag kopieren
								ldap_add($connection, $dn, $data);
								
								// ins array
								$changed_groups[$entries_subgroups[$i]['dn']] = $dn;	
							}
							//alten subtree l�schen
							for($i=$entries_subgroups['count']-1; $i>=0; $i--){
								ldap_delete($connection, $entries_subgroups[$i]['dn']);
							}
						}
						
						// gruppenverlinkungen aktualisieren
						foreach($changed_groups AS $dn_old => $dn_new){
							$baseDN_grouplinks = $registry->get('configuration')->getRoot();
							$filter_grouplinks = '(&(objectClass=inetOrgPerson)(seeAlso='.$dn_old.'))';
							
							$result_grouplinks = ldap_search($connection, $baseDN_grouplinks, $filter_grouplinks);
							$entries_grouplinks = ldap_get_entries($connection, $result_grouplinks);
							
							for($j=0; $j<$entries_grouplinks['count']; $j++){
								ldap_mod_add($connection, $entries_grouplinks[$j]['dn'], array('seeAlso' => $dn_new));
								ldap_mod_del($connection, $entries_grouplinks[$j]['dn'], array('seeAlso' => $dn_old));
								//echo 'dn: '.$entries_grouplinks[$j]['dn'].'<br>';
								//echo 'old link: '.$dn_old.'<br>';
								//echo 'new link: '.$dn_new.'<br>';
							}

						}
						
						// array wieder l�schen
						$changed_groups = array();
						
						// gruppennamen der variablen anpassen
						$group_name = $group_name_new;
					}
				}
				
				// gruppe verschieben
				if($parent_new != ''){
					$baseDN_parent = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					
					if($parent_new != '---'){
						$result_parent = ldap_search($connection, $baseDN_parent, 'cn='.$parent_new, array('dn'));
						$entries_parent = ldap_get_entries($connection, $result_parent);
						$parentDN = $entries_parent[0]['dn'];	
					}
					else{
						$parentDN = $baseDN_parent;
					}
							
					// dn der gruppe nochmals ermitteln, da er sich ggf. durch umbenennen ge�ndert hat
					$baseDN_group_tmp = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					$result_group_tmp = ldap_search($connection, $baseDN_group_tmp, 'cn='.$group_name, array('dn'));
					$entries_group_tmp = ldap_get_entries($connection, $result_group_tmp);
					
					// gruppe auf steam verschieben
					$this->move_group_on_steam($entries_group_tmp[0]['dn'], $parentDN);
							
					ldap_rename($connection, $entries_group_tmp[0]['dn'], 'cn='.$group_name, $parentDN, true);

					// gruppenverlinkungen anpassen
					$baseDN_grouplinks = $registry->get('configuration')->getRoot();
					$filter_grouplinks = '(&(objectClass=inetOrgPerson)(seeAlso='.$entries_group_tmp[0]['dn'].'))';
							
					$result_grouplinks = ldap_search($connection, $baseDN_grouplinks, $filter_grouplinks);
					$entries_grouplinks = ldap_get_entries($connection, $result_grouplinks);
							
					for($j=0; $j<$entries_grouplinks['count']; $j++){
						ldap_mod_del($connection, $entries_grouplinks[$j]['dn'], array('seeAlso' => $entries_group_tmp[0]['dn']));
						ldap_mod_add($connection, $entries_grouplinks[$j]['dn'], array('seeAlso' => 'cn='.$group_name.','.$parentDN));
					}
				}
				
				ldap_unbind($connection);
			}			
			return true;
		}
		function rename_group_on_steam($dn_old, $name_new){
					
			// verbindung zu steam aufbauen
			$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
					
			// alten steam-namen der gruppe ermitteln
			$ldap_module = $steamConnector->get_server_module('persistence:ldap');					
			$steam_groupname_old = $steamConnector->predefined_command($ldap_module, 'dn_to_group_name', $dn_old, 0);
					
			//echo 'alter steam_name: '.$steam_groupname_old.'<br>';
			//echo 'neuer steam_name: '.$steam_groupname_new.'<br>';
					
			$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname_old, 0);
			$steamGroup->set_name($name_new, 0);
		}
		function move_group_on_steam($groupDN, $parentDN){
			
			// verbindung zu steam aufbauen
			$steamConnector = new steam_connector('localhost', 1900, 'root', 'h6518_W#');
			
			// referenz auf die steam-gruppe holen
			$ldap_module = $steamConnector->get_server_module('persistence:ldap');					
			$steam_groupname = $steamConnector->predefined_command($ldap_module, 'dn_to_group_name', $groupDN, 0);
			$steamGroup = steam_factory::get_group($steamConnector, $steam_groupname, 0);

			// referenz auf die neue parent-group holen
			$steam_groupname_parent_new = $steamConnector->predefined_command($ldap_module, 'dn_to_group_name', $parentDN, 0);
			$steamGroup_parent_new = steam_factory::get_group($steamConnector, $steam_groupname_parent_new, 0);
			
			// parent-group bestimmen
			$steamGroup_parent = $steamGroup->get_parent_group();
			
			// gruppe aus der alten parent-group entfernen
			$steamGroup_parent->remove_member($steamGroup);
			
			// gruppe der neuen parent-group hinzuf�gen
			$steamGroup_parent_new->add_member($steamGroup);
		}
		
		/*
		 * Liefert die Benutzernamen aller Mitglieder einer Gruppe.
		 * 
		 * $groupname:		Name der Gruppe, deren Mitglieder zur�ckgegeben werden sollen.
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
			$memberAttr = $registry->get('configuration')->getMemberAttribute();
			
			
			// verbindung erstellen
			$connection = $this->establishConnection();
			ldap_bind($connection, $userDN, $userPW);
			
			if($connection != false){
				
				// suchfilter bestimmen
				if($groupname == 'Gruppenlos'){
					$baseDN_tmp = 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					$result_tmp = ldap_search($connection, $baseDN_tmp, 'objectClass=groupOfURLs');
					$entries_tmp = ldap_get_entries($connection, $result_tmp);
					if($entries_tmp['count'] >= 1){
						$filter = '(&(objectClass=inetOrgPerson)';
						for($i=0; $i<$entries_tmp['count']; $i++){
							$filter .= '(!('.$memberAttr.'='.$entries_tmp[$i]['dn'].'))';
						}
						$filter .= ')';
					}
					else{
						$filter = '(objectClass=inetOrgPerson)';
					}
		
				}
				else{ 
					$groupDN = $this->getGroupDN($groupname, $connection);
					$filter = '(&(objectClass=inetOrgPerson)('.$memberAttr.'='.$groupDN.'))';
				}
				
				
				// suche starten
				$result = ldap_search($connection, $baseDN, $filter, array('cn', 'uid'));
				
				
				// ergebnisse auswerten
				if(ldap_count_entries($connection, $result) >= 1){
					$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
					}
					ksort($data);
				}
			}
			
			
			// verbindung trennen
			ldap_unbind($connection);
				

			return $data;
		}

		
		
		/*
		 * Liefert Informationen �ber einen bestimmten Benutzer.
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
		 * Speichert/�ndert Informationen eines Benutzers.
		 * 
		 * $uid:			Benutzername des Benutzers, dessen Daten ge�ndert werden.
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
			$systemDN = $registry->get('configuration')->getSystemLogin();
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
		 * Speichert den Wert f�r ein bestimmtes Attribut eines Benutzereintrages.
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
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$memberAttr = $registry->get('configuration')->getMemberAttribute();
			$targetUserDN = $this->getUserDN($uid);
			
			
			// verbindung aufbauen
			$connection = $this->establishConnection();
			ldap_bind($connection, $systemDN, $systemPassword);
			
			
			// werte speichern
			if($key == 'membership'){ 
				$groupDN = $this->getGroupDN($value, $connection);
				
				
				// Benutzereintrag aktualisieren
				$returnValue1 = ldap_mod_add($connection, $targetUserDN, array($memberAttr => $groupDN));
				
				// Gruppeneintrag aktualisieren
				// $returnValue2 = ldap_mod_add($connection, $groupDN, array('member' => $targetUserDN));
			}
			else{
				$returnValue1 = ldap_mod_add($connection, $targetUserDN, array($key => $value));
			}
			
			
			// verbindung trennen
			ldap_unbind($connection);
		
			
			// Status-R�ckgabe
			return $returnValue1;
		}
		
		/*
		 * L�scht den Wert eines bestimmten Attributes eines bestimmten Benutzers.
		 * 
		 * $uid:		Benutzername.
		 * $key:		Attribut-Bezeichnung.
		 * $value:		Der zu l�schende Wert.
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
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$memberAttr = $registry->get('configuration')->getMemberAttribute();
			$targetUserDN = $this->getUserDN($uid);
			
			
			// verbindung aufbauen
			$connection = $this->establishConnection();
			ldap_bind($connection, $systemDN, $systemPassword);
			
			
			// werte entfernen
			if($key == 'membership'){ 
				$groupDN = $this->getGroupDN($value, $connection);
				
				// Benutzereintrag aktualisieren
				$returnValue1 = ldap_mod_del($connection, $targetUserDN, array($memberAttr => $groupDN));
				
				// Gruppeneintrag aktualisieren
				// $returnValue2 = ldap_mod_del($connection, $groupDN, array('member' => $targetUserDN));
			}
			
			
			// verbindung trennen
			ldap_unbind($connection);
		
	
			// Status-R�ckgabe
			return $returnValue1;
		}
		
		/*
		 * Ersetzt den Wert eines Attributes eines bestimmten Benutzers.
		 * 
		 * $uid:		Benutzername.
		 * $key:		Bezeichnung des Attributes.
		 * $value:		Neuer Wert. Wird dieser Parameter nicht gesetzt, so wird der Wert des Attributes gel�scht.
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
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$memberAttr = $registry->get('configuration')->getMemberAttribute();
			$targetUserDN = $this->getUserDN($uid);
			
			
			// verbindung aufbauen
			$connection = $this->establishConnection();
			ldap_bind($connection, $systemDN, $systemPassword);
			
			
			// werte ersetzen
			if($key == 'membership'){
				$groupDN = $this->getGroupDN($value, $connection);
				
				// Benutzereintrag aktualisieren
				$returnValue1 = ldap_mod_replace($connection, $targetUserDN, array($memberAttr => $groupDN));
				
				// Gruppeneintrag aktualisieren
				// $returnValue2 = ldap_mod_replace($connection, $groupDN, array('member' => $targetUserDN));
			}
			if($key == 'userPassword'){
				$returnValue1 = ldap_mod_replace($connection, $targetUserDN, array('userPassword' => '{SHA}'.base64_encode(pack('H*', sha1($value)))));
			}
			if($key != 'membership' AND $key != 'userPassword') $returnValue1 = ldap_mod_replace($connection, $targetUserDN, array($key => $value));
			
			
			// verbindung trennen
			ldap_unbind($connection);
		
	
			return $returnValue1;
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

			//m�gliche leerzeichen entfernen
			$givenname = str_replace(' ', '', $givenname);
			$surname = str_replace(' ', '', $surname);
			$email = str_replace(' ', '', $email);
			
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$systemDN = $registry->get('configuration')->getSystemLogin();
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
				$password = '{SHA}'.base64_encode(pack('H*', sha1($uid)));
			@ldap_unbind($connection);
			}

			//die ACL noch anpassen, weil erstellter user hat noch kein schreibrecht!
			$userDN = $registry->get('configuration')->getSystemLogin().','.$registry->get('configuration')->getRoot();
			$userPW = $registry->get('configuration')->getSystemPassword();
			$DN = 'uid='.$uid.',ou='.$role.',ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
						
			$data['objectClass'][0] = 'person';
			$data['objectClass'][1] = 'organizationalPerson';
			$data['objectClass'][2] = 'inetOrgPerson';
			$data['sn'] = $surname;
			$data['cn'] = $givenname.' '.$surname;
			$data['givenname'] = $givenname;
			$data['uid'] = $uid;
			$data['userPassword'] = '{SHA}'.base64_encode(pack('H*', sha1($uid)));
			if($email != '') $data['mail'] = $email;
			//if($defaultGroup != '' AND $defaultGroup != '---') $data['ou'][0] = $defaultGroup;
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				$addStatus = @ldap_add($connection, $DN, $data);
				@ldap_unbind($connection);	
			}
			
			
			// gff in default-gruppe eintragen
			if($defaultGroup != '' AND $defaultgroup != '---' AND $this->groupExists($defaultGroup)) $this->setUserData($uid, 'membership', $defaultGroup);
			
			
			// r�ckgabe
			if($addStatus != false) return $uid;
			else return $addStatus;

		}
		
		/*
		 * L�scht einen Benutzer. ACHTUNG: Dieser Benutzer wird endg�ltig aus dem LDAP-Verzeichnis entfernt!
		 * 
		 * $uid:		Benutzername des zu l�schenden Benutzers.
		 * 
		 * return:		Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function removeUser($uid){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$systemDN = $registry->get('configuration')->getSystemLogin();
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
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$timestamp = time();
			$userDN = $this->getUserDN($uid);
			$memberAttr = $registry->get('configuration')->getMemberAttribute();
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				
				// Benutzer in Papierkorb verschieben
				if($target == 'TRASH'){ 
					// ZielDN bestimmen
					$newParentDN = 'ou=TRASH,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					
					// Eintr�ge aus dem Gruppeneintrag entfernen
					/*
					$result = ldap_search($connection, $userDN, 'uid='.$uid, array($memberAttr));
					$entries = ldap_get_entries($connection, $result);
					if($entries['count'] === 1){
						foreach($entries[0][strtolower($memberAttr)] AS $groupDN){
							@ldap_mod_del($connection, $groupDN, array('member' => $userDN));
						}
					}
					*/
				}
				
				// Benutzer in Pool verschieben
				if($target == 'POOL'){
					// ZielDN bestimmen
					$newParentDN = 'ou=POOL,'.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					
					/*
					// Eintr�ge aus dem Gruppeneintrag entfernen
					$result = ldap_search($connection, $userDN, 'uid='.$uid, array($memberAttr));
					$entries = ldap_get_entries($connection, $result);
					if($entries['count'] === 1){
						foreach($entries[0][strtolower($memberAttr)] AS $groupDN){
							@ldap_mod_del($connection, $groupDN, array('member' => $userDN));
						}
					}
					//Eintr�ge aus dem Benutzereintrag entfernen
					@ldap_mod_del($connection, $userDN, array($memberAttr => array()));
*/
				}
				
				// sonst
				if($target != 'TRASH' AND $target != 'POOL'){
					$newParentDN = 'ou='.$target.',ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					
					// Falls Benutzer aus dem Papierkorb kommt
					if(strpos($userDN, 'ou=TRASH') != false){
						
						// die member-Attribute in den Gruppeneintr�gen wiederherstellen
						$result = ldap_search($connection, $userDN, 'uid='.$uid, array($memberAttr));
						$entries = ldap_get_entries($connection, $result);
						if($entries['count'] === 1){
							foreach($entries[0][strtolower($memberAttr)] AS $groupDN){
								@ldap_mod_add($connection, $groupDN, array('member' => 'uid='.$uid.','.$newParentDN));
							}
						}
					}
				}
			
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
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$memberAttr = $registry->get('configuration')->getMemberAttribute();
			if($description == '') $description = 'Keine Beschreibung';
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result2 = ldap_search($connection, $registry->get('configuration')->getRoot(), 'uid='.$owner);
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
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			$filter = $memberAttr.'='.($parent!='---'?'cn='.$name.','.$parentDN:'cn='.$name.',ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot());
			$memberURL = 'ldap:///'.$registry->get('configuration')->getRoot().'??sub?(&('.$filter.')(objectClass=inetOrgPerson))';
						
			$data['objectClass'][0] = 'groupOfURLs';
			//$data['objectClass'][0] = 'groupOfNames';
			$data['objectClass'][1] = 'top';
			$data['cn'] = $name;
			$data['owner'] = $ownerDN;
			//$data['member'] = $ownerDN;
			$data['memberURL'] = $memberURL;
			$data['description'] = $description; 
			
			$connection = $this->establishConnection();
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				if($parent != '---') $returnValue = ldap_add($connection, 'cn='.$name.','.$parentDN, $data);
				else $returnValue = ldap_add($connection, 'cn='.$name.',ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), $data);
				@ldap_unbind($connection);	
			}
			
			return $returnValue;
		}
		
		/*
		 * L�scht eine Gruppe aus dem LDAP-Verzeichnis.
		 * 
		 * $groupname:		Name der zu l�schenden Gruppe.
		 * 
		 * return:			Im Erfolgsfall TRUE, ansonsten FALSE.
		 */
		public function removeGroup($groupname, $subtree = false){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			$systemDN = $registry->get('configuration')->getSystemLogin();
			$systemPassword = $registry->get('configuration')->getSystemPassword();
			
			$connection = $this->establishConnection();
			
			
			if($connection != false){
				@ldap_bind($connection, $systemDN, $systemPassword);
				@$result = ldap_search($connection, 'ou=groups,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot(), 'cn='.$groupname, array('dn'));
				@$entries = ldap_get_entries($connection, $result);
				$groupDN = $entries[0]['dn'];
				$memberAttr = $registry->get('configuration')->getMemberAttribute();
			 	
				if($subtree){				
					// die ou-Attribute aus den user l�schen
					@$searchResults = ldap_search($connection, $groupDN, 'objectClass=groupOfURLs', array('dn', 'cn'));
					@$groupEntries = ldap_get_entries($connection, $searchResults);
					$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
				
					for($i=0; $i<$groupEntries['count']; $i++){
						$groupDN_tmp = $groupEntries[$i]['dn'];
						$filter = '(&(objectClass=inetOrgPerson)('.$memberAttr.'='.$groupDN_tmp.'))';
						@$userSearchResults = ldap_search($connection, $baseDN, $filter, array('dn'));
						@$userEntries = ldap_get_entries($connection, $userSearchResults);
						for($j=0; $j<$userEntries['count']; $j++){
							//return false;
							@ldap_mod_del($connection, $userEntries[$j]['dn'], array($memberAttr => $groupDN_tmp));
						}
					}
					
					
					
					
					
					// Die Eintr�ge entfernen
					$returnValue = $this->recursiveDelete($connection, $groupDN);
				}
				else{
					// ou-Attribut aus usern l�schen
					$baseDN = 'ou=user,'.$sessionRegistry->get('school').','.$sessionRegistry->get('district').','.$registry->get('configuration')->getRoot();
					$filter = '(&(objectClass=inetOrgPerson)('.$memberAttr.'='.$groupDN.'))';
					@$result = ldap_search($connection, $baseDN, $filter, array('dn'));
					@$entries = ldap_get_entries($connection, $result);
					for($i=0; $i<$entries['count']; $i++){
						@ldap_mod_del($connection, $entries[$i]['dn'], array($memberAttr => $groupDN));
					}
					//-----
								
					@$returnValue = ldap_delete($connection, $groupDN);
				}
				@ldap_unbind($connection);			
			}
			return $returnValue;	
		}
		
		/*
		 * Funktion zum rekursiven L�schen eines Teilbaumes des LDAP-Verzeichnisses.
		 * 
		 * $connection:		Verbindungskennung zum LDAP-Server.
		 * $dn:				Wurzel des zu l�schenden Teilbaumes.
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
		 * Pr�ft, ob eine Gruppe noch Untergruppen besitzt.
		 * 
		 * $groupname:		Name der zu �berpr�fenden Gruppe.
		 * 
		 * return:			Falls die angegebene Gruppe Untergruppen besitzt TRUE, falls nicht FALSE.
		 */
		public function hasSubgroups($groupname){
			$sessionRegistry = SessionRegistry::getInstance();
			$registry = Registry::getInstance();
			
			$systemDN = $registry->get('configuration')->getSystemLogin();
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
			
			$systemDN = $registry->get('configuration')->getSystemLogin();
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
		
		$systemDN = $registry->get('configuration')->getSystemLogin();
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
		
		$systemDN = $registry->get('configuration')->getSystemLogin();
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



	function ultimate_userExists($uid){
		$connection = $this->establishConnection();
		$sessionRegistry = SessionRegistry::getInstance();
		$registry = Registry::getInstance();
		$systemDN = $registry->get('configuration')->getSystemLogin();
		$systemPW = $registry->get('configuration')->getSystemPassword();
		$baseDN = $registry->get('configuration')->getRoot();
				
		if($connection != false){
			@ldap_bind($connection, $systemDN, $systemPW);
			$result = @ldap_search($connection, $baseDN, 'uid='.$uid, array('dn'));
			$entries = @ldap_get_entries($connection, $result);
			@ldap_unbind($connection);
			
			if($entries['count'] === 1) return true;
			else return false;
		}
	}
	
	function ultimate_addUser($uid, $groupDN){
		$connection = $this->establishConnection();
		$sessionRegistry = SessionRegistry::getInstance();
		$registry = Registry::getInstance();
		$systemDN = $registry->get('configuration')->getSystemLogin();
		$systemPW = $registry->get('configuration')->getSystemPassword();
		$baseDN = $registry->get('configuration')->getRoot();
				
		if($connection != false){
			// userDN ermitteln
			@ldap_bind($connection, $systemDN, $systemPW);
			$result = @ldap_search($connection, $baseDN, 'uid='.$uid);
			$entries = @ldap_get_entries($connection, $result);
			if($entries['count'] === 1) $userDN = $entries[0]['dn'];
			else return false;
			
			// seeAlso Attribut setzen
			$data['seeAlso'] = $groupDN;
			$status = @ldap_mod_add($connection, $userDN, $data);
			@ldap_unbind($connection);
			return $status;
		}
		else return false;
	}
	
	function ultimate_removeUser($uid, $groupDN){
		$connection = $this->establishConnection();
		$sessionRegistry = SessionRegistry::getInstance();
		$registry = Registry::getInstance();
		$systemDN = $registry->get('configuration')->getSystemLogin();
		$systemPW = $registry->get('configuration')->getSystemPassword();
		$baseDN = $registry->get('configuration')->getRoot();
				
		if($connection != false){
			// userDN ermitteln
			@ldap_bind($connection, $systemDN, $systemPW);
			$result = @ldap_search($connection, $baseDN, 'uid='.$uid);
			$entries = @ldap_get_entries($connection, $result);
			if($entries['count'] === 1) $userDN = $entries[0]['dn'];
			else return false;
			
			// seeAlso Attribut setzen
			$data['seeAlso'] = $groupDN;
			$status = @ldap_mod_del($connection, $userDN, $data);
			@ldap_unbind($connection);
			return $status;
		}
		else return false;
	}
	
	public function ultimate_getGroupMembers($groupname){
		$sessionRegistry = SessionRegistry::getInstance();
		$registry = Registry::getInstance();
		$systemDN = $registry->get('configuration')->getSystemLogin();
		$systemPW = $registry->get('configuration')->getSystemPassword();
		$baseDN = $registry->get('configuration')->getRoot();
		$data = array();
		$memberAttr = $registry->get('configuration')->getMemberAttribute();
		
		
		// verbindung erstellen
		$connection = $this->establishConnection();
		@ldap_bind($connection, $systemDN, $systemPW);
		
		if($connection != false){
			
			// suchfilter bestimmen
			$groupDN = $this->getGroupDN($groupname, $connection);
			$filter = '(&(objectClass=inetOrgPerson)('.$memberAttr.'='.$groupDN.'))';
		
			// suche starten
			$result = @ldap_search($connection, $baseDN, $filter, array('cn', 'uid'));
			
			
			// ergebnisse auswerten
			if(@ldap_count_entries($connection, $result) >= 1){
				$entries = @ldap_get_entries($connection, $result);
				for($i=0; $i<$entries['count']; $i++){
					$data[$entries[$i]['uid'][0]] = $entries[$i]['cn'][0];
				}
				ksort($data);
			}
		}
		
		
		// verbindung trennen
		@ldap_unbind($connection);
			

		return $data;
	}
}
?>
