<html>
<head>
    <title>bid-owl Benutzerverwaltung</title>
    <link rel="stylesheet" type="text/css" href="stylesheets/mainStyle.css">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <script src="javascript/layoutFunctions.js" type="text/javascript"></script>
    <?php
        if (defined("CONF_CUSTOM_HEAD")) {
            echo CONF_CUSTOM_HEAD;
        }
    ?>
</head>

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
                                boxStart('Gruppe ausw�hlen', 'W�hlen Sie hier die Kriterien, nach denen gesucht werden soll.');
                                    echo '<form action=index.php name=chooseGroupForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Gruppenauswahl:</div></td><td style="width:145px;text-align:right;"><select class=basicInput name=groupSelect size=1>';
                                        if ($this->groupList != null) {
                                            foreach ($this->groupList as $groupName) {
                                                if($this->groupSelect != null AND $this->groupSelect == $groupName)
                                                    echo '<option selected>'.$groupName.'</option>';
                                                else
                                                    echo '<option>'.$groupName.'</option>';
                                            }
                                        }
                                    echo '</select></td></tr>';
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=chooseGroup value=true onclick="javascript:document.chooseGroupForm.submit();">suchen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=showGroupdata>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                            </td></tr>
                            <tr><td><img src="img/one.gif" width=1 height=10></td></tr>
                            <tr><td>
                            <?php
                                boxStart('Gruppendaten', 'Falls Sie den genauen Benutzernamen nicht kennen, k�nnen Sie hier eine Suche starten.');
                                if ($this->groupSelect != null) {
                                    if ($this->groupdata != null) {
                                        echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Gruppenname:</div></td><td class=text style="width:145px;">'.$this->groupdata['name'].'</td></tr>';
                                        echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                        echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Besitzer:</div></td><td class=text style="width:145px;">'.$this->groupdata['owner'].'</td></tr>';
                                        if ($this->groupdata['description'] != '') {
                                            echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                            echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Bescreibung:</div></td><td class=text style="width:145px;">'.$this->groupdata['description'].'</td></tr>';
                                        }
                                        if ($this->groupdata['parent'] != '') {
                                            echo '<tr><td colspan=2 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                            echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Untergruppe von:</div></td><td class=text style="width:145px;">'.$this->groupdata['parent'].'</td></tr>';
                                        }
                                        if ($this->accessLevel != null) {
                                            echo '<tr><td colspan=2 style="text-align:right;">';
                                            echo '<form action=index.php name=gotoDataChange style="margin:0px;padding:0px;">';
                                            echo '<button class=basicButton name=change value=true onclick="javascript:document.gotoDataChange.submit();">Daten bearbeiten</button>';
                                            echo '<input type=hidden name=cmd value=changeGroupdata>';
                                            echo '<input type=hidden name=groupSelect value='.$this->groupdata['name'].'>';
                                            echo '</form>';
                                            echo '</td></tr>';
                                        }
                                        echo '</table>';
                                    }
                                } else {
                                    echo 'Sie haben keine Gruppe ausgew�hlt!';
                                }
                                boxEnd();
                            ?>
                            </td></tr>
                            </table>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                boxStart('Mitgliederliste dieser Gruppe', 'Hier k�nnen Sie die Informationen �ber den Benutzer einsehen!');
                                if ($this->groupSelect != null AND $this->memberlist != false) {
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                    $counter = 1;
                                    foreach ($this->memberlist as $key => $value) {
                                        if($counter%2 == 0) echo '<tr><td style="width:140px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#ffffff;">'.$value.'</td></tr>';
                                        else echo '<tr><td style="width:140px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#d7d7ff;">'.$value.'</td></tr>';
                                        $counter++;
                                    }
                                    unset($counter);

                                    echo '</table>';
                                } else {
                                    echo '<div class=text>keine Gruppe ausgew�hlt</div>';
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
</html>
