<?php
	echo '<table style="height:100%;" width="140" cellpadding=0 cellspacing=0 border=0>';
	echo '<tr><td class=navBorderL></td><td class=navSpacer style="background-color:#ffffff;"><img src="img/one.gif" width=1 height=10></td><td class=navBorderR></td></tr>';
	
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td class=topic>System</td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td><div class=systemlink><a href=index.php?cmd=logout>Logout</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 5) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=changeSchool>Schule wechseln</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 5) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=createSchool>Schule anlegen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 5) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=createDistrict>Kreis anlegen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 5) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=lookUp>LookUp</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td class=navSpacer><img src="img/one.gif" width=1 height=10></td><td class=navBorderR></td></tr>';

	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td class=topic>Benutzer</td><td class=navBorderR><td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=createUser>Neu anlegen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=deleteUser>L&ouml;schen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 2) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=showUserdata>Daten einsehen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=changeUserdata>Daten &auml;ndern</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=changeOwnPassword>Eigenes Kenn-<br>wort &auml;ndern</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=changePassword>Fremdes Kenn-<br>wort &auml;ndern</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 1) echo '<tr><td class=navBorderL></td><td class=navSpacer><img src="img/one.gif" width=1 height=10></td><td class=navBorderR></td></tr>';

	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL><img src="img/one.gif" width=1 height=1></td><td class=topic>Gruppen</td><td class=navBorderR><td></tr>';
	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=createGroup>Neu anlegen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=deleteGroup>L&ouml;schen</a></div></td><td class=navBorderR></td></tr>';
	//if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=showGroupdata>Daten einsehen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=changeGroupdata>Bearbeiten</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 5) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=ultimateMembershipOrganisation>Schul&uuml;bergrei-<br>fende Gruppen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 3) echo '<tr><td class=navBorderL></td><td class=navSpacer><img src="img/one.gif" width=1 height=10></td><td class=navBorderR><td></tr>';
	
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL><img src="img/one.gif" width=1 height=1></td><td class=topic>Pool</td><td class=navBorderR><td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=moveToPool>Benutzer ver-<br>schieben</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=getFromPool>Benutzer holen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td class=navSpacer><img src="img/one.gif" width=1 height=10></td><td class=navBorderR><td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';

	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL><img src="img/one.gif" width=1 height=1></td><td class=topic>Papierkorb</td><td class=navBorderR><td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=undelete>Benutzer wiederherstellen</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td><div class=link><a href=index.php?cmd=navigation&target=emptyTrash>Leeren</a></div></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td class=navSpacer2></td><td class=navBorderR></td></tr>';
	if($this->accessLevel >= 4) echo '<tr><td class=navBorderL></td><td class=navSpacer><img src="img/one.gif" width=1 height=10></td><td class=navBorderR><td></tr>';
	
	echo '<tr><td class=navBorderL></td><td style="background-color:#C2D1E0;height:100%;"></td><td class=navBorderR><td></tr>';
	echo '<tr><td class=navBorderL></td><td class=navSpacer style="background-color:#ffffff;"><img src="img/one.gif" width=1 height=10></td><td class=navBorderR></td></tr>';
	echo '</table>';
	echo '<script language="javascript">highlightActiveLink();</script>';
?>
