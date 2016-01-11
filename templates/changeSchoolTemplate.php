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
                                boxStart('Kreis w&auml;hlen', 'W&auml;hlen Sie aus dem Drop-Down-Men&uuml; einen Kreis aus und best&auml;tigen Sie Ihre Auswahl. Anschlie&szlig;end werden Ihnen alle Schulen dieses Kreises aufgelistet.');
                                    echo '<form action=index.php name=chooseDistrict style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Kreisauswahl:</div></td><td style="width:145px;text-align:right;"><select class=basicInput name=districtSelect size=1>';
                                        if ($this->districtList != null) {
                                            foreach ($this->districtList as $districtName) {
                                                if($this->districtSelect != null AND $this->districtSelect == $districtName)
                                                    echo '<option selected>'.$districtName.'</option>';
                                                else
                                                    echo '<option>'.$districtName.'</option>';
                                            }
                                        }
                                    echo '</select></td></tr>';
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=changeDistrict value=true onclick="javascript:document.chooseDistrict.submit();">w&auml;hlen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=changeSchool>';
                                    echo '</form>';
                                boxEnd();
                            ?>
                        </td>
                        <td class="seperator"><img src="img/one.gif" width="10" height="1"></td>
                        <td style="padding-right:10px;padding-top:10px;vertical-align:top;">
                            <?php
                                boxStart('Schulauswahl', 'Klicken Sie einfach auf den gew&uuml;nschten Schulname und schon wechseln Sie zu dieser Schule.<br>&nbsp;<br>In der oberen linken Ecke dieses Programms wird Ihnen jederzeit der Name der aktuell gew&auml;hlten Schule angezeigt.');
                                if ($this->schoolList != null) {
                                    echo '<form name=schoolChangeForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;padding:0px;margin:0px;">';

                                    $counter = 1;
                                    foreach ($this->schoolList as $schoolname) {
                                        if($counter%2 == 0) echo '<tr><td style="width:290px;vertical-align:middle;text-align:left;background-color:#ffffff;padding:0px;margin:0px;"><div class=searchResultItem><a href=# onclick="javascript:document.schoolChangeForm.schoolSelect.value=\''.$schoolname.'\';document.schoolChangeForm.submit();">'.$schoolname.'</a></div></td></tr>';
                                        else echo '<tr><td style="width:290px;vertical-align:middle;text-align:left;background-color:#d7d7ff;padding:0px;margin:0px;"><div class=searchResultItem><a href=# onclick="javascript:document.schoolChangeForm.schoolSelect.value=\''.$schoolname.'\';document.schoolChangeForm.submit();">'.$schoolname.'</a></div></td></tr>';
                                        $counter++;
                                    }
                                    unset($counter);

                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=changeSchool>';
                                    echo '<input type=hidden name=changeSchool value=true>';
                                    echo '<input type=hidden name=schoolSelect>';
                                    echo '<input type=hidden name=districtSelect value='.$this->districtSelect.'>';
                                    echo '</form>';
                                } else {
                                    echo '<div class=text>Kein Kreis gew&auml;hlt!</div>';
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
