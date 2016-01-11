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
                                boxStart('Gruppe l&ouml;schen', 'W&auml;hlen Sie einfach den Namen der zu l&ouml;schenden Gruppe aus und klicken Sie anschlie&szlig;end auf L&ouml;schen.');
                                    echo '<form action=index.php name=deleteForm style="padding:0px;margin:0px;">';
                                    echo '<table cellpadding=0 cellspacing=0 border=0 style="width:290px;">';
                                    echo '<tr><td style="width:145px;text-align:left;vertical-align:middle;"><span class=text-bf>Gruppenauswahl:</span></td><td align=right style="width:145px;height:20px;">';
                                    if($this->groupSelect != null) $activeGroup = $this->groupSelect; else $activeGroup = '';
                                    createMenu('dropdown-search', 'groupSelect', $this->groupList, $activeGroup, '', 100);
                                    echo '</td></tr>';
                                    /*
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
                                    */
                                    if ($this->status != null AND $this->statusMsg != null) {
                                        if($this->status == 'ok') echo '<tr><td colspan=2><div class=apply>'.$this->statusMsg.'</div></td></tr>';
                                        if($this->status == 'warning') echo '<tr><td colspan=2><div class=warning>'.$this->statusMsg.'</div></td></tr>';
                                    }
                                    if($this->acknowledgeRequest == null) echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton onclick="javascript:document.deleteForm.submit();">l&ouml;schen</button></td></tr>';
                                    if($this->acknowledgeRequest != null) echo '<tr><td colspan=2 style="text-align:right;"><button class=basicButton style="display:inline;" onclick="javascript:document.deleteForm.deleteGroup.value=false;document.deleteForm.submit();">abbrechen</button><button class=basicButton style="display:inline;" name=acknowledgeResponse value=true onclick="javascript:document.deleteForm.submit();">l&ouml;schen</button></td></tr>';
                                    echo '</table>';
                                    echo '<input type=hidden name=cmd value=deleteGroup>';
                                    echo '<input type=hidden name=deleteGroup value=true>';
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
