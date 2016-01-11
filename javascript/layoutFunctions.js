	// Öffnet einen Hilfe-Text.
	// helper:	id des zu öffnenden Hilfe-Textes.
	function openHelper(helper){
		document.getElementById(helper).style.visibility = "visible";
	}
	
	// Schliesst einen Hilfe-Text.
	// helper:	id des zu schliessenden Helpers.
	function closeHelper(helper){
		document.getElementById(helper).style.visibility = "hidden";
	}
	
	// Markiert bei einer Benutzerauswahl alle Checkboxen.
	function markAll(elements, type){
		if(document.getElementById('mainMarker').checked == true)
			for(i=1;i<=elements;i++) document.getElementById(type + '_' + i).checked = true;
		if(document.getElementById('mainMarker').checked == false)
			for(i=1;i<=elements;i++) document.getElementById(type + '_' + i).checked = false;
	}
	
	// Regelt bei der Mitglieder-Zuordnung unter "Gruppen bearbeiten" das Ein- und Ausblenden
	// der benötigten Drop-Down-Menüs.
	function setVisibleMode(action){
		if(action == 'add'){
			document.getElementById('moveOrAddLabel').style.display = 'block';
			document.getElementById('moveOrAddSelector').style.display = 'block';
		}
		if(action == 'move'){
			document.getElementById('moveOrAddLabel').style.display = 'block';
			document.getElementById('moveOrAddSelector').style.display = 'block';
		}
		if(action == 'remove'){
			document.getElementById('moveOrAddLabel').style.display = 'none';
			document.getElementById('moveOrAddSelector').style.display = 'none';
		}
		
		if(action == 'maingroupSelected'){
			document.getElementById('subgroupLabel').style.display = 'none';
			document.getElementById('subgroupSelector').style.display = 'none';
		}
		if(action == 'subgroupSelected'){
			document.getElementById('subgroupLabel').style.display = 'block';
			document.getElementById('subgroupSelector').style.display = 'block';
		}
		if(action == 'useTextfield'){
			document.getElementById('usercreationTextfield').style.display = 'block';
			document.getElementById('usercreationUpload').style.display = 'none';
		}
		if(action == 'useUpload'){
			document.getElementById('usercreationTextfield').style.display = 'none';
			document.getElementById('usercreationUpload').style.display = 'block';
		}
	}
	
	// Setzt Sternchen in das Passwort-Feld (unter "fremdes Passwort ändern").
	// Hier wird NICHT ein zufälliges Passwort generiert!
	function showRandomPassword(){
		if(document.getElementById('randomPW').checked == true){
			document.getElementById('passwordNew').value = '********';
			document.getElementById('passwordRetype').value = '********';
		}
		else{
			document.getElementById('passwordNew').value = '';
			document.getElementById('passwordRetype').value = '';
		}
	}
	
	// Aktiviert die Checkbox für "Benutzer neues Passwort mailen", falls zufälliges
	// Passwort generieren gewählt wurde.
	function setMailCheckbox(){
		if(document.changePWForm.randomPW.checked == true){
			document.changePWForm.mail.checked = true;
		}
	}
	
	// Markiert den zur Zeit aktiven Link dauerhaft.
	function highlightActiveLink(){
	
		var countLinks = document.getElementsByTagName("a").length;
		var target = getParameter(window.location.href, 'target');
		if(target == undefined) target = getParameter(window.location.href, 'cmd');

		for(var i=1; i<countLinks; i++){
			if(getParameter(document.getElementsByTagName("a")[i].href, 'target') == target){
				document.getElementsByTagName("a")[i].style.backgroundColor = "#719AD1";
			}
		}
	
	
	}
	
	// Ermittelt aus einem String (URL) den Wert eines bestimmten Parameters.
	function getParameter(string, key){
	
		var vars = string.split('?')[1].split('&');
		var vals = new Array();
		for(var i=0; i<vars.length; i++){
			vals[i] = {name:vars[i].split('=')[0], value:vars[i].split('=')[1]};
		}
		for(var j=0; j<vals.length; j++){
			if(key == vals[j].name) return vals[j].value;
		}
		return;
	
	}
	
function dropdownOpen(id){
	document.getElementById(id).style.display = 'block';
}
function dropdownClose(id, name, groupname){
	document.getElementById(id).style.display = 'none';
	document.getElementById(id + '-activeItem').innerHTML = groupname;
	document.getElementById(name).value = groupname;
}
	
	
	
	
	
	
	
	
	
	
