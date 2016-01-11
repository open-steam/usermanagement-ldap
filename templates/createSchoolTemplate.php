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
                        <td style="padding-left:10px;padding-top:10px;vertical-align:top;width:310px;">
                            <?php
                                boxStart('Schule anlegen', 'Um eine neue Schule anzulegen, geben Sie einen Schulnamen an, und w&auml;hlen Sie den Kreis aus, welchem die Schule angeh&ouml;ren soll.<br>&nbsp;<br>Zus&auml;tzlich k&ouml;nnen Sie einen Schuladministrator anlegen. F&uuml;llen Sie dazu einfach die entsprechenden Felder aus.');
                                    echo '<form name=createSchoolForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Schulname:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=schoolName></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Kreisauswahl:</div></td><td style="width:145px;text-align:right;"><select class=basicInput name=districtName size=1>';
                                        if ($this->districtList != null) {
                                            foreach ($this->districtList as $districtName) {
                                                echo '<option value='.$districtName.'>'.$districtName.'</option>';
                                            }
                                        }
                                    echo '</select></td></tr>';

                                    echo '<tr><td colspan=2 style="width:290px;text-align:left;padding-top:20px;padding-bottom:10px;"><div class=text>Angaben f&uuml;r einen initialen Schuladministrator:</div></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Vorname:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=vorname></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Nachname:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=nachname></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>E-Mail:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=text name=email></td></tr>';

                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok') echo '<tr><td colspan=2><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning') echo '<tr><td colspan=2><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=create value=true onclick="javascript:document.createSchoolForm.submit();">anlegen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=createSchool>';
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
