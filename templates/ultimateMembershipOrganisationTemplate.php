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
                                boxStart('Gruppe ausw&auml;hlen', 'W&auml;hlen Sie hier diejenige Gruppe aus, der Sie neue Benutzer hinzuf&uuml;gen wollen.');
                                    echo '<form method=get action=index.php name=chooseGroupForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:middle;"><span class=text-bf>Gruppenauswahl:</span></td><td align=right style="width:145px;height:20px;">';
                                    if($this->groupSelect != null) $activeGroup = $this->groupSelect; else $activeGroup = '';
                                    createMenu('dropdown-search', 'groupSelect', $this->groupList, $activeGroup, array('Gruppenlos'), 101);
                                    echo '</td></tr>';
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=chooseGroup value=true onclick="javascript:document.chooseGroupForm.submit();">ausw&auml;hlen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=ultimateMembershipOrganisation>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                            </td></tr>
                            <tr><td><img src="img/one.gif" width=1 height=10></td></tr>
                            <tr><td>
                            <?php
                                boxStart('Benutzer hinzuf&uuml;gen', 'Um einen Benutzer der gew&auml;hlten Gruppe hinzuzuf&uuml;gen, geben Sie hier den entsprechenden Benutzernamen ein, und best&auml;tigen Sie ihre Eingabe.');
                                    if ($this->groupSelect != null) {
                                    echo '<form name=changeDataForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Benutzername:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=username></td></tr>';
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok_2') echo '<tr><td colspan=2><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning_2') echo '<tr><td colspan=2><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=addUser value=true onclick="javascript:document.changeDataForm.submit();">hinzuf&uuml;gen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=ultimateMembershipOrganisation>';
                                    echo '</form>';
                                    } else echo 'Keine Gruppe gew&auml;hlt!';
                                boxEnd();
                            ?>
                            </td></tr>
                            </table>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                boxStart('Mitglieder der Gruppe', 'Hier werden Ihnen die Mitglieder der Gruppe angezeigt. Zudem k&ouml;nnen Sie hier auch Benutzer aus der Gruppe wieder entfernen.');

                                if ($this->groupSelect != null AND is_array($this->memberlist) == true) {
                                    if (count($this->memberlist) >= 1) {
                                    echo '<form name=memberOrgForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                    echo '<tr><td colspan=3><div class=description>Folgende Benutzer sind Mitglieder dieser Gruppe:</div></td></tr>';

                                    $counter = 1;
                                    foreach ($this->memberlist as $key => $value) {
                                        if($counter%2 == 0) echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#ffffff;padding:0px;margin:0px;"><input class=basicCheckbox id=user_'.$counter.' type=checkbox name=name'.$counter.' value='.$key.'></td><td style="width:100px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:170px;background-color:#ffffff;">'.$value.'</td></tr>';
                                        else echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#d7d7ff;padding:0px;margin:0px;"><input class=basicCheckbox id=user_'.$counter.' type=checkbox name=name'.$counter.' value='.$key.'></td><td style="width:100px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:170px;background-color:#d7d7ff;">'.$value.'</td></tr>';
                                        $counter++;
                                    }
                                    unset($counter);

                                    echo '<tr><td colspan=3 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                    echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;"><input class=basicCheckbox type=checkbox id=mainMarker onclick="javascript:markAll('.count($this->memberlist).', \'user\');"></td><td style="text-align:left;"><div class=searchResultItem>alle markieren</div></td>';
                                    echo '<tr><td colspan=3><img src="img/one.gif" width="1" height="10"></td></tr>';

                                    echo '<tr><td colspan=3 style="text-align:right"><button class=basicButton name=remove value=true onclick="javascript:document.memberOrgForm.submit();">entfernen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=ultimateMembershipOrganisation>';
                                    echo '</form>';
                                    } else {
                                        echo '<div class=text>Diese Gruppe besitzt zur Zeit keine Mitglieder!</div>';
                                    }
                                } else {
                                    echo '<div class=text>keine Gruppe ausgew&auml;hlt</div>';
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
