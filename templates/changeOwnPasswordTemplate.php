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
                                boxStart('Eigenes Kennwort &auml;ndern', 'Um Ihr eigenes Kennwort zu &auml;ndern, geben Sie Ihr altes und danach das neue Kennwort an. Aus Sicherheitsgr&uuml;nden best&auml;tigen Sie das neue Kennwort bitte.');
                                    echo '<form name=changePWForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Kennwort (alt):</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=password name=passwordOld></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Kennwort (neu):</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=password name=passwordNew></td></tr>';
                                    echo '<tr><td style="width:145px;text-align:left;"><div class=text-bf>Kennwort best&auml;tigen:</div></td><td style="width:145px;text-align:right;"><input class=basicInput type=password name=passwordRetype></td></tr>';
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok') echo '<tr><td colspan=2><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning') echo '<tr><td colspan=2><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton name=changePW value=true onclick="javascript:document.changePWForm.submit();">&auml;ndern</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=changeOwnPassword>';
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
