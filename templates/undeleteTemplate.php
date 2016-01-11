<html>
<head>
    <title>bid-owl Benutzerverwaltung</title>
    <link rel="stylesheet" type="text/css" href="stylesheets/mainStyle.css">
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
                            <?php
                                boxStart('Nach Benutzernamen suchen', 'Hier k&ouml;nnen Sie eine Suche nach Benutzernamen starten. Dazu k&ouml;nnen Sie den Namen, oder einen Teil des Namens angeben.
Sie k&ouml;nnen zus&auml;tzlich eine Zeitspanne angeben, die sich die Benutzer schon mindestens im Papierkorb befinden sollen.');
                                    echo '<form action=index.php name=chooseUser style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Namensfilter:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=namefilter ';
                                        if($this->namefilter != null) echo 'value='.$this->namefilter;
                                        echo '></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Zeitspanne:</div></td><td style="width:145px;text-align:right;"><select class=basicInput name=timespan size=1>';
                                    if($this->timespan == 'noLimit') echo '<option value=noLimit selected>Keine Einschr&auml;nkung</option>';
                                    else echo '<option value=noLimit>Keine Einschr&auml;nkung</option>';
                                    if($this->timespan == '1w') echo '<option value=1w selected>mind. 1 Woche</option>';
                                    else echo '<option value=1w>mind. 1 Woche</option>';
                                    if($this->timespan == '1m') echo '<option value=1m selected>mind. 1 Monat</option>';
                                    else echo '<option value=1m>mind. 1 Monat</option>';
                                    if($this->timespan == '3m') echo '<option value=3m selected>mind. 3 Monate</option>';
                                    else echo '<option value=3m>mind. 3 Monate</option>';
                                    if($this->timespan == '6m') echo '<option value=6m selected>mind. 6 Monate</option>';
                                    else echo '<option value=6m>mind. 6 Monate</option>';
                                    echo '</select></td></tr>';
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=userSearch value=true onclick="javascript:document.chooseUser.submit();">suchen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=undelete>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                boxStart('Ergebnisse der Suche', 'Hier werden die Suchergebnisse aufgelistet. Markieren Sie die gew&uuml;nschten Benutzer und best&auml;tigen Sie Ihre Auswahl durch Dr&uuml;cken des Buttons.!');
                                if ($this->timespan != null) {
                                    echo '<form name=undeleteForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                    if (count($this->results) == 0) {
                                        echo '<tr><td colspan=3><div class=description>Die Suche in <span class=text-bf>"Papierkorb"</span> war leider erfolglos!</div></td></tr>';
                                    } else {
                                        echo '<tr><td colspan=3><div class=description>Die Suche in <span class=text-bf>"Papierkorb"</span> lieferte folgende Ergebnisse:</div></td></tr>';
                                    }
                                    $counter = 1;
                                    foreach ($this->results as $key => $value) {
                                        if($counter%2 == 0) echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#ffffff;padding:0px;margin:0px;"><input class=basicCheckbox type=checkbox id=user_'.$counter.' name=name'.$counter.' value='.$key.'></td><td style="width:120px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#ffffff;">'.$value.'</td></tr>';
                                        else echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#d7d7ff;padding:0px;margin:0px;"><input class=basicCheckbox type=checkbox id=user_'.$counter.' name=name'.$counter.' value='.$key.'></td><td style="width:120px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#d7d7ff;">'.$value.'</td></tr>';
                                        $counter++;
                                    }
                                    unset($counter);
                                    echo '<tr><td colspan=3 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                    if(count($this->results) != 0) echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;"><input class=basicCheckbox type=checkbox id=mainMarker onclick="javascript:markAll('.count($this->results).', \'user\');"></td><td style="text-align:left;"><div class=searchResultItem>alle markieren</div></td>';
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok') echo '<tr><td colspan=3><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning') echo '<tr><td colspan=3><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    if(count($this->results) != 0) echo '<tr><td colspan=3 style="text-align:right"><button class=basicButton name=undeleteUser value=true onclick="javascript:document.undeleteForm.submit();">wiederherstellen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=undelete>';
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
