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
                        <td style="padding-left:10px;padding-top:10px;vertical-align:top;width:310px;">
                            <?php
                                boxStart('Gruppe anlegen', 'Um eine neue Gruppe anzulegen, geben Sie hier die ben&ouml;tigten Daten ein. Alle mit einem Sternchen (*) gekennzeichneten Angaben m&uuml;ssen gemacht werden.<br>&nbsp;<br>Beachten Sie bei der Wahl des Gruppennamens bitte, keines der folgenden Symbole zu verwenden:<br>&nbsp;<br>/, \, @, Komma, Leerzeichen.');
                                    echo '<form name=groupDataForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    //echo '<tr><td colspan=2><div class=description style="width:290px;">Beachten Sie bei der Wahl des Gruppennamens bitte, dass Sie die Sonderzeichen . (Punkt), @ (At), / (Slash), \ (Backslash), , (Komma) und das Leerzeichen nicht verwenden d�rfen!</div></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Name:</span><span class=obligation>*</span></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=name></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><span class=text-bf>Administrator:</span></span><span class=obligation>*</span></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=owner value='.$this->standardOwner.'></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:top;"><div class=text-bf style="padding-top:5px;">Beschreibung:</div></td><td style="width:145px;text-align:right;"><textarea class=basicInput name=description style="height:60px;"></textarea></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:top;padding-top:3px;"><span class=text-bf>Gruppentyp:<span class=obligation>*</span></span></td><td class=text style="width:145px;text-align:left;vertical-align:middle;"><input type=radio name=groupType value=maingroup checked onclick="javascript:setVisibleMode(\'maingroupSelected\');">Hauptgruppe<br><input type=radio name=groupType value=subgroup onclick="javascript:setVisibleMode(\'subgroupSelected\');">Untergruppe</td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:middle;"><div id=subgroupLabel style="display:none;"><span class=text-bf>Untergruppe von:</span><span class=obligation>*</span></div></td><td align=right style="width:145px;height:20px;"><div id=subgroupSelector style="display:none;">';
                                    if($this->groupSelect != null) $activeGroup = $this->groupSelect; else $activeGroup = '';
                                    createMenu('dropdown-search', 'parent', $this->groupList, $activeGroup, array('---'), 100);
                                    echo '</div></td></tr>';
                                    /*
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf style="display:none;" id=subgroupLabel>Untergruppe von:<span class=obligation>*</span></div></td><td style="width:145px;text-align:right;"><div id=subgroupSelector style="display:none;"><select class=basicInput name=parent size=1>';
                                    if ($this->groupList != null) {
                                            foreach ($this->groupList as $groupName) {
                                                if($this->groupSelect != null AND $this->groupSelect == $groupName)
                                                    echo '<option selected>'.$groupName.'</option>';
                                                else
                                                    echo '<option>'.$groupName.'</option>';
                                            }
                                    }
                                    echo '</select></div></td></tr>';
                                    */
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok') echo '<tr><td colspan=2><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning') echo '<tr><td colspan=2><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=createGroup value=true onclick="javascript:document.groupDataForm.submit();">anlegen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=createGroup>';
                                    echo '</form>';
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
