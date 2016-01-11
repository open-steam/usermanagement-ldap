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
Zus&auml;tzlich wird Ihnen &uuml;ber das Drop-Down-Men&uuml; eine Auswahl an Schulnamen angeboten, von denen Benutzer in den Pool verschoben wurden, und sich immer noch dort befinden. Dadurch k&ouml;nnen Sie Ihre Suche noch genauer eingrenzen.');
                                    echo '<form action=index.php name=chooseUser style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Namensfilter:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=namefilter ';
                                        if($this->namefilter != null) echo 'value='.$this->namefilter;
                                        echo '></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Vorherige Schule:</div></td><td style="width:145px;text-align:right;"><select class=basicInput name=schoolSelect size=1>';
                                        if ($this->groupList != null) {
                                            foreach ($this->groupList as $groupName) {
                                                if($this->schoolSelect != null AND $this->schoolSelect == $groupName)
                                                    echo '<option selected>'.$groupName.'</option>';
                                                else
                                                    echo '<option>'.$groupName.'</option>';
                                            }
                                        }
                                    echo '</select></td></tr>';
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=userSearch value=true onclick="javascript:document.chooseUser.submit();">suchen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=getFromPool>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                boxStart('Ergebnisse der Suche', 'Hier werden die Suchergebnisse aufgelistet. Markieren Sie einfach die gew&uuml;nschten Benutzer und Best&auml;tigen Sie ihre Auswahl durch den Button.');
                                if ($this->schoolSelect != null AND $this->results != null) {
                                    echo '<form name=deleteForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                    echo '<tr><td colspan=3><div class=description>Die Suche im <span class=text-bf>"Pool"</span> lieferte folgende Ergebnisse:</div></td></tr>';

                                    $counter = 1;
                                    foreach ($this->results as $key => $value) {
                                        if($counter%2 == 0) echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#ffffff;padding:0px;margin:0px;"><input class=basicCheckbox type=checkbox id=user_'.$counter.' name=name'.$counter.' value='.$key.'></td><td style="width:120px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#ffffff;">'.$value.'</td></tr>';
                                        else echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#d7d7ff;padding:0px;margin:0px;"><input class=basicCheckbox type=checkbox id=user_'.$counter.' name=name'.$counter.' value='.$key.'></td><td style="width:120px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#d7d7ff;">'.$value.'</td></tr>';
                                        $counter++;
                                    }
                                    unset($counter);
                                    echo '<tr><td colspan=3 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                    echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;"><input class=basicCheckbox type=checkbox id=mainMarker onclick="javascript:markAll('.count($this->results).', \'user\');"></td><td style="text-align:left;"><div class=searchResultItem>alle markieren</div></td>';
                                    echo '<tr><td colspan=3 style=""><img src="img/one.gif" width="1" height="10"></td></tr>';

                                    echo '<tr><td colspan=2 style="text-align:left;"><div class=text-bf>Rolle:</div></td><td style="text-align:right;">
                                            <select class=basicInput name=roleSelect size=1><option value=student>Sch&uuml;ler</option><option value=teacher>Lehrer</option><option value=groupAdmin>Gruppenadministrator</option><option value=schoolAdmin>Schuladministrator</option></select></td></tr>';

                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok') echo '<tr><td colspan=3><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning') echo '<tr><td colspan=3><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=3 style="text-align:right"><button class=basicButton name=getUser value=true onclick="javascript:document.deleteForm.submit();">holen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=getFromPool>';
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
