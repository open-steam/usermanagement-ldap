<?php
	interface DatabaseAccess{
	
		public function auth($userDN, $password, $time);
		public function getUserDN($uid);
		public function userExists($uid);
		public function groupExists($groupname);
		public function establishConnection();
		public function search($namefilter, $group, $oldSchool = false, $timespan = '');
		public function getSchools($district);
		public function getOldSchools();
		public function getDistricts();
		public function getGroups($allgroups=false, $groupless=false, $none=false);
		public function getGroupInformation($groupname);
		public function setGroupInformation($groupname, $data);
		public function getGroupMembers($groupname);
		public function getUserInformation($uid);
		public function setUserInformation($uid, $newGivenname, $newSurname, $newEmail, $newRole);
		public function setUserData($uid, $key, $value);
		public function unsetUserData($uid, $key, $value);
		public function replaceUserData($uid, $key, $value='');
		public function createUser($givenname, $surname, $role, $email='');
		public function removeUser($uid);
		public function moveUser($uid, $targetDN);
		public function createGroup($groupname, $owner, $description, $parent="");
		public function removeGroup($groupname, $subtree=false);
		public function hasSubgroups($groupname);
	}
?>