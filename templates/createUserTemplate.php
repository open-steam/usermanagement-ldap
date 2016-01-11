<html>
<head>
    <title>bid-owl Benutzerverwaltung</title>
    <link rel="stylesheet" type="text/css" href="stylesheets/mainStyle.css">
    <link rel="stylesheet" media="all" type="text/css" href="stylesheets/dropdown.css" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <!--[if IE]>
        <style type="text/css">@import url(stylesheets/dropdown_ie.css);</style>
      <![endif]-->
    <script src="javascript/layoutFunctions.js" type="text/javascript"></script>
    <?php
        if (defined("CONF_CUSTOM_HEAD")) {
            echo CONF_CUSTOM_HEAD;
        }
    ?>
</head>
<body>
    <table class="mainTable" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="topFrame" colspan="3"><?php include_once 'topFrame.php'; ?></td>
        </tr>
        <tr>
            <td class="navFrame"><?php include_once 'navigationFrame.php'; ?></td>
            <td class="mainFrame">
                <table class="contentMainTable" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding-left:10px;padding-top:10px;vertical-align:top;">
                            <table cellpadding="0" cellspacing="0" border="0">
                            <tr><td>
                            <?php
                                boxStart('Benutzer anlegen', 'Geben Sie hier die Benutzerdaten ein, um einen neuen Benutzer anzulegen. Die mit einem Sternchen (*) gekennzeichneten Angaben m&uuml;ssen eingetragen werden.<br>&nbsp;<br>Es wird automatisch ein Benutzername generiert, welcher Ihnen anschlie&szlig;end angezeigt wird. Der Benutzername ist zugleich auch das initiale Kennwort.');
                                    echo '<form action=index.php name=userData style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Vorname:</span><span class=obligation>*</span></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=vorname></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Nachname:</span><span class=obligation>*</span></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=nachname></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>E-Mail:</span></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=email></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Rolle:</span><span class=obligation>*</span></td><td style="width:145px;text-align:right;">
                                            <select class=basicInput name=roleSelect size=1><option value=student>Sch&uuml;ler</option><option value=teacher>Lehrer</option><option value=groupAdmin>Gruppenadministrator</option><option value=schoolAdmin>Schuladministrator</option></select></td></tr>';

                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:middle;"><span class=text-bf>Mitgliedschaft:</span></td><td align=right style="width:145px;height:20px;">';
                                    createMenu('dropdown-manual', 'defaultGroup_1', $this->groupList, '', array('---'), 101);
                                    echo '</td></tr>';
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok')
                                            echo '<tr><td colspan=2><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        elseif($this->status == 'warning')
                                            echo '<tr><td colspan=2><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=create value=true onclick="javascript:document.userData.submit();">anlegen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=createUser>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                            </td></tr>
                            <tr><td><img src="img/one.gif" width=1 height=10></td></tr>
                            <tr><td>
                            <?php
                                boxStart('Benutzerliste &uuml;bertragen', 'Hier k&ouml;nnen Sie neue Benutzer &uuml;ber eine textuelle Liste anlegen. Sie k&ouml;nnen w&auml;hlen, ob Sie diese Liste in ein Textfeld eingeben oder als Text-Datei (*.txt) hochladen.<br>&nbsp;<br>
                                             Die Angaben sind nach folgender Konvention zu erstellen:<br>&nbsp;<br>Vorname,Nachname,Benutzerrolle,E-Mail;<br>&nbsp;<br>Sie k&ouml;nnen beliebig viele solcher Eintr&auml;ge hintereinander angeben, wobei die Angabe einer E-Mail Adresse optional ist. Anschlie&szlig;end werden Ihnen die erstellten Benutzernamen angezeigt. Auch hier ist der Benutzername wieder das initiale Kennwort.
                                        <br>&nbsp;<br>Falls Sie f&uuml;r einen Benutzer in Ihrer Liste keine Benutzerrolle abgeben, wird dem Benutzer automatisch die im Drop-Down-Men&uuml; "Rolle" gew&auml;hlte Benutzerrolle zugewiesen.
                                        <br>&nbsp;<br>Wenn die erstellten Benutzer automatisch einer Gruppe hinzugef&uuml;gt werden sollen, k&ouml;nnen Sie die gew&uuml;nschte Gruppe unter dem Punkt "Gruppe" ausw&auml;hlen.');
                                    echo '<form action=index.php name=createMultipleUsers style="margin:0px;padding:0px;" method="POST" enctype="multipart/form-data">';
                                        echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                        echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Eingabemodus:</span></td><td style="width:145px;text-align:right;">
                                            <select class=basicInput name=creationType size=1 onclick="javascript:setVisibleMode(document.createMultipleUsers.creationType.options[document.createMultipleUsers.creationType.selectedIndex].value);"><option value=useUpload>Datei hochladen</option><option value=useTextfield>Eingabefeld</option></select></td></tr>';
                                        echo '<tr><td colspan=2 class="seperator"><img src="img/one.gif" width="1" height="10"></td></tr>';

                                        echo '<tr><td colspan=2 style="width:290px;text-align:left;"><div id=usercreationTextfield style="display:none;"><textarea name=creationText style="width:290px;height:145px;font-family:verdana;font-size:12px;"></textarea></div></td></tr>';
                                        echo '<tr><td colspan=2><div id=usercreationUpload><input style="width:290px;margin:0px;padding:0px;" type="file" name="uploadFile"></div></td></tr>';

                                        echo '<tr><td colspan=2 class="seperator"><img src="img/one.gif" width="1" height="10"></td></tr>';

                                        echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Rolle:</span></td><td style="width:145px;text-align:right;">
                                            <select class=basicInput name=defaultRole size=1><option value=student>Sch&uuml;ler</option><option value=teacher>Lehrer</option><option value=groupAdmin>Gruppenadministrator</option><option value=schoolAdmin>Schuladministrator</option></select></td></tr>';
                                        //echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Default-Gruppe:</span></td><td style="width:145px;text-align:right;">
                                        //	<select class=basicInput name=group size=1><option value="---">---</option><option value=gruppea>GruppeD</option><option value=b>GruppeC</option><option value=v>GruppeB</option></select></td></tr>';
                                        echo '<tr><td style="width:145px;text-align:left;vertical-align:middle;"><span class=text-bf>Gruppe:</span></td><td align=right style="width:145px;height:20px;">';
                                        createMenu('dropdown-textbased', 'defaultGroup_2', $this->groupList, '', array(0 => '---'), 100);
                                        echo '</td></tr>';

                                        echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=multipleCreation value=true onclick="javascript:document.createMultipleUsers.submit();">anlegen</button></td></tr>';

                                        echo '</table>';
                                    echo '<input type=hidden name=cmd value=createUser>';
                                    echo '</form>';
                                    //foreach($this->debug AS $key => $val) echo $key.': '.$val.'<br>';
                                boxEnd();
                            ?>
                            </td></tr>
                            </table>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                if ($this->userCreated != null) {
                                    boxStart('Erstellte Benutzer', 'Hier werden Ihnen die erstellten Benutzer und deren Login-Namen aufgelistet. Der Login-Name ist zugleich das initiale Kennwort.');
                                    echo '<form action=index.php name=creationResults style="margin:0px;padding:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                    echo '<tr><td colspan=3><div class=description>Folgende Benutzer wurden erfolgreich erstellt:</td></tr>';

                                    $counter = 1;
                                    foreach ($this->userCreated as $data) {
                                        if($counter%2 == 0) echo '<tr><td style="width:140px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=# onclick="javascript:document.creationResults.userSelect.value=\''.$data['login'].'\';document.creationResults.submit();">'.$data['login'].'</a></div></td><td class=text style="width:150px;background-color:#ffffff;">'.$data['name'].'</td></tr>';
                                        else echo '<tr><td style="width:140px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=# onclick="javascript:document.creationResults.userSelect.value=\''.$data['login'].'\';document.creationResults.submit();">'.$data['login'].'</a></div></td><td class=text style="width:150px;background-color:#d7d7ff;">'.$data['name'].'</td></tr>';
                                        $counter++;
                                    }
                                    unset($counter);

                                    echo '</table>';
                                    echo '<input type=hidden name=userSelect>';
                                    echo '<input type=hidden name=cmd value=showUserdata>';
                                    echo '</form>';
                                    boxEnd();
                                }

                            ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="rightFrame"></td>
        </tr>
        <tr>
            <td class="bottomFrame" colspan="3" valign="bottom"><?php include_once 'bottomFrame.php'; ?></td>
        </tr>
    </table>
</body>
</html>
