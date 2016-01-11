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
                                boxStart('Nach Benutzernamen suchen', 'Hier k&ouml;nnen Sie eine Suche nach Benutzernamen starten. Dazu k&ouml;nnen Sie den Namen, oder einen Teil des Namens angeben, sowie die zu durchsuchende Gruppe.
Falls Sie alle Gruppen durchsuchen wollen, w&auml;hlen Sie einfach \'Alle_Gruppen\', und um nach Benutzern zu suchen, welche keiner Gruppe angeh&ouml;ren, w&auml;hlen Sie \'Gruppenlos\'');
                                    echo '<form action=index.php name=chooseUser style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Namensfilter:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=namefilter ';
                                        if($this->namefilter != null) echo 'value='.$this->namefilter;
                                        echo '></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:middle;"><span class=text-bf>Gruppenfilter:</span></td><td align=right style="width:145px;height:20px;">';
                                    if($this->groupSelect != null) $activeGroup = $this->groupSelect; else $activeGroup = '';
                                    createMenu('dropdown-search', 'groupSelect', $this->groupList, $activeGroup, array(0 => 'Gruppenlos', 1 => 'Alle_Gruppen'), 100);
                                    echo '</td></tr>';
                                    /*
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Gruppenfilter:</div></td><td style="width:145px;text-align:right;"><select class=basicInput name=groupSelect size=1>';
                                        if ($this->groupList != null) {
                                            foreach ($this->groupList as $groupName) {
                                                if($this->groupSelect != null AND $this->groupSelect == $groupName)
                                                    echo '<option selected>'.$groupName.'</option>';
                                                else
                                                    echo '<option>'.$groupName.'</option>';
                                            }
                                        }
                                    echo '</select></td></tr>';
                                    */
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=userSearch value=true onclick="javascript:document.chooseUser.submit();">suchen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=showUserdata>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                            </td></tr>
                            <tr><td><img src="img/one.gif" width=1 height=10></td></tr>
                            <tr><td>
                            <?php
                                boxStart('Benutzerdaten', 'Hier werden Ihnen die Informationen &uuml;ber den gew&auml;hlten Benutzer angezeigt.<br>&nbsp;<br>Falls Sie entsprechende Rechte besitzen, wird ein Button angezeigt, &uuml;ber welchen Sie zu einem Bildschirm gelangen, in dem Sie diese Daten bearbeiten k&ouml;nnen.');
                                    if ($this->userdata != null AND is_array($this->userdata) == true) {
                                        echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Login:</div></td><td class=text style="width:145px;">'.$this->userdata['uid'].'</td></tr>';
                                        echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Vorname:</div></td><td class=text style="width:145px;">'.$this->userdata['givenname'].'</td></tr>';
                                        echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Nachname:</div></td><td class=text style="width:145px;">'.$this->userdata['surname'].'</td></tr>';
                                        echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>E-Mail:</div></td><td class=text style="width:145px;">'.$this->userdata['email'].'</td></tr>';
                                        echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Rolle:</div></td><td class=text style="width:145px;">';
                                        switch ($this->userdata['role']) {
                                            case 'student': echo 'Sch&uuml;ler';break;
                                            case 'teacher': echo 'Lehrer';break;
                                            case 'groupAdmin': echo 'Gruppenadministrator';break;
                                            case 'schoolAdmin': echo 'Schuladministrator';break;
                                        }
                                        echo '</td></tr>';
                                        echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Schule:</div></td><td class=text style="width:145px;">'.$this->userdata['school'].'</td></tr>';
                                        if ($this->accessLevel != null AND $this->accessLevel >= 3) {
                                            echo '<tr><td colspan=2 style="text-align:right;">';
                                            echo '<form action=index.php name=gotoDataChange style="margin:0px;padding:0px;">';
                                            echo '<button class=basicButton name=change value=true onclick="javascript:document.gotoDataChange.submit();">Daten bearbeiten</button>';
                                            echo '<input type=hidden name=cmd value=changeUserdata>';
                                            echo '<input type=hidden name=userSelect value='.$this->userdata['uid'].'>';
                                            echo '</form>';
                                            echo '</td></tr>';
                                        }
                                        echo '</table>';
                                    } elseif ($this->warning != null) {
                                    echo 'Sie haben keinen Benutzernamen ausgew&auml;hlt!';
                                }
                                boxEnd();
                            ?>
                            </td></tr>
                            </table>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                boxStart('Ergebnisse der Suche', 'Hier werden Ihnen die Ergebnisse der Suche aufgelistet. Klicken Sie einfach auf den gew&uuml;nschten Benutzernamen, und die Informationen zu diesem Benutzer werden angezeigt.');
                                if ($this->groupSelect != null AND $this->results != null AND is_array($this->results)) {
                                    echo '<form action=index.php name=searchResults style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                    echo '<tr><td colspan=3><div class=description>Die Suche in <span class=text-bf>"'.$this->groupSelect.'"</span> lieferte folgende Ergebnisse:</div></td></tr>';

                                    $counter = 1;
                                    foreach ($this->results as $key => $value) {
                                        if($counter%2 == 0) echo '<tr><td style="width:140px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=# onclick="javascript:document.searchResults.userSelect.value=\''.$key.'\';document.searchResults.submit();">'.$key.'</a></div></td><td class=text style="width:150px;background-color:#ffffff;">'.$value.'</td></tr>';
                                        else echo '<tr><td style="width:140px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=# onclick="javascript:document.searchResults.userSelect.value=\''.$key.'\';document.searchResults.submit();">'.$key.'</a></div></td><td class=text style="width:150px;background-color:#d7d7ff;">'.$value.'</td></tr>';
                                        $counter++;
                                    }
                                    unset($counter);

                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=showUserdata>';
                                    echo '<input type=hidden name=userSelect>';
                                    echo '</form>';
                                } else {
                                    echo '<div class=text>keine Suche gestartet!</div>';
                                }
                                boxEnd();
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
