<?php
class steamObjectUsertool extends steam_object{
	
	public function getSteamGroupname($module, $function, $groupDN){
		return $this->steam_command($module, $function, $groupDN);
	}
}
?>