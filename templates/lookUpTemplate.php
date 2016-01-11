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
                            <?php
                                boxStart('Gruppenauswahl', 'N/A');
                                    echo '<form action=index.php name=selectGroup style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    //echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Gruppenfilter:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=groupfilter ';
                                    //	if($this->namefilter != null) echo 'value='.$this->groupfilter;
                                    //	echo '></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:middle;"><span class=text-bf>Direktauswahl</span></td><td align=right style="width:145px;height:20px;">';
                                    if($this->groupSelect != null) $activeGroup = $this->groupSelect; else $activeGroup = '';
                                    createMenu('dropdown-search', 'directSelect', $this->groupList, $activeGroup, '', 100);
                                    echo '</td></tr>';
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok')
                                            echo '<tr><td colspan=3><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        elseif($this->status == 'warning')
                                            echo '<tr><td colspan=3><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=lookUp value=true onclick="javascript:document.selectGroup.submit();">lookUp</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=lookUp>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                /*
                                boxStart('Ergebnisse der Suche', 'Hier werden die Suchergebnisse aufgelistet. Markieren Sie die gew&uuml;nschten Benutzer und Best&auml;tigen Sie die Auswahl durch Dr&uuml;cken des Verschieben-Buttons.');
                                if ($this->groupSelect != null) {
                                    if (count($this->results) >= 1) {
                                        echo '<form name=deleteForm style="padding:0px;margin:0px;">';
                                        echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';
                                        echo '<tr><td colspan=3><div class=description>Die Suche in <span class=text-bf>"'.$this->groupSelect.'"</span> lieferte folgende Ergebnisse:</div></td></tr>';

                                        $counter = 1;
                                        foreach ($this->results as $key => $value) {
                                            if($counter%2 == 0) echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#ffffff;padding:0px;margin:0px;"><input class=basicCheckbox type=checkbox id=user_'.$counter.' name=name'.$counter.' value='.$key.'></td><td style="width:120px;text-align:left;background-color:#ffffff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#ffffff;">'.$value.'</td></tr>';
                                            else echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;background-color:#d7d7ff;padding:0px;margin:0px;"><input class=basicCheckbox type=checkbox id=user_'.$counter.' name=name'.$counter.' value='.$key.'></td><td style="width:120px;text-align:left;background-color:#d7d7ff;"><div class=searchResultItem><a href=#>'.$key.'</a></div></td><td class=text style="width:150px;background-color:#d7d7ff;">'.$value.'</td></tr>';
                                            $counter++;
                                        }
                                        unset($counter);
                                        echo '<tr><td colspan=3 style=""><img src="img/one.gif" width="1" height="5"></td></tr>';
                                        echo '<tr><td style="width:20px;vertical-align:middle;text-align:center;"><input class=basicCheckbox type=checkbox id=mainMarker onclick="javascript:markAll('.count($this->results).', \'user\');"></td><td style="text-align:left;"><div class=searchResultItem>alle markieren</div></td>';
                                        if ($this->status != null AND $this->statusMsg != null) {
                                            if($this->status == 'ok') echo '<tr><td colspan=3><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                            if($this->status == 'warning') echo '<tr><td colspan=3><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                        }
                                        echo '<tr><td colspan=3 style="text-align:right"><button class=basicButton name=moveUser value=true onclick="javascript:document.deleteForm.submit();">verschieben</button></td></tr>';
                                        echo '</table>';
                                        echo '<input type=hidden name=cmd value=moveToPool>';
                                        echo '</form>';
                                    } else {
                                        echo '<div class=text>Die Suche lieferte keine Ergebnisse!</div>';
                                    }
                                } else {
                                    echo '<div class=text>keine Suche gestartet!</div>';
                                }
                                boxEnd();
                            */
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
