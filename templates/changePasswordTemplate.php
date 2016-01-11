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
                                boxStart('Nach Benutzernamen suchen', 'Falls Sie den genauen Benutzernamen nicht kennen, k&ouml;nnen Sie hier eine Suche starten.');
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
                                    echo '<input type=hidden name=cmd value=changePassword>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                            </td></tr>
                            <tr><td><img src="img/one.gif" width=1 height=10></td></tr>
                            <tr><td>
                            <?php
                                // boxStart('Fremdes Kennwort &auml;ndern', 'Geben Sie hier den Benutzer an, dessen Kennwort ge&auml;ndert werden soll. Geben Sie anschlie&szlig;end das neue Kennwort an und best&auml;tigen dies.<br>&nbsp;<br>Sie k&ouml;nnen auch ein zuf&auml;lliges Kennwort generieren lassen, jedoch funktioniert dies nur, wenn der Benutzer eine E-Mail Adresse angegeben hat.');
                                boxStart('Fremdes Kennwort &auml;ndern', 'Geben Sie hier den Benutzer an, dessen Kennwort ge&auml;ndert werden soll. Geben Sie anschlie&szlig;end das neue Kennwort an und best&auml;tigen dies.');
                                    echo '<form action=index.php name=changePWForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Benutzername:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=name></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Kennwort (neu):</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=password name=passwordNew id=passwordNew></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Kennwort best&auml;tigen:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=password name=passwordRetype id=passwordRetype></td></tr>';
                                    echo '<tr><td colspan=2><img src="img/one.gif" width="1" height="10"></td></tr>';
                                    // echo '<tr><td class=text colspan=2 style="vertical-align:middle;"><input class=basicCheckbox style="display:inline;" type=checkbox onclick="javascript:setMailCheckbox();showRandomPassword();" name=randomPW id=randomPW value=true><div class=text style="padding:2px;display:inline;">zuf&auml;lliges Kennwort generieren</div></td></tr>';
                                    // echo '<tr><td class=text colspan=2 style="vertical-align:middle;"><input class=basicCheckbox style="display:inline;" type=checkbox onclick="javascript:setMailCheckbox();" name=mail value=true ><div class=text style="padding:2px;display:inline;">neues Kennwort per Mail an Benutzer senden</div></td></tr>';
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok') echo '<tr><td colspan=2><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning') echo '<tr><td colspan=2><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=changePW value=true onclick="javascript:document.changePWForm.submit();">&auml;ndern</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=changePassword>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                            </td></tr>
                            </table>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                boxStart('Ergebnise der Suche', 'Hier werden die Suchergebnisse aufgelistet. Klicken Sie auf den gew&uuml;nschten Login-Namen, und dieser wird in das entsprechende Feld automatisch eingetragen.');
                                if ($this->groupSelect != null) {
                                    if (count($this->results) >= 1) {
                                        echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                        echo '<tr><td colspan=2><div class=description>Die Suche in <span class=text-bf>"'.$this->groupSelect.'"</span> lieferte folgende Ergebnisse:</div></td></tr>';

                                        $counter = 1;
                                        foreach ($this->results as $key => $value) {
                                            if($counter%2 == 0) echo '<tr><td style="width:140px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=# onclick="javascript:document.changePWForm.name.value=\''.$key.'\';">'.$key.'</a></div></td><td class=text style="width:150px;background-color:#ffffff;">'.$value.'</td></tr>';
                                            else echo '<tr><td style="width:140px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=# onclick="javascript:document.changePWForm.name.value=\''.$key.'\';">'.$key.'</a></div></td><td class=text style="width:150px;background-color:#d7d7ff;">'.$value.'</td></tr>';
                                            $counter++;
                                        }
                                        unset($counter);
                                        echo '</table>';
                                    } else {
                                        echo '<div class=text>Die Suche lieferte keine Ergebnisse!</div>';
                                    }
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
