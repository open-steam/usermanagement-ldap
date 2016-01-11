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
                <div class=text style="padding:10px;">Willkommen in der Benutzerverwaltung!</div>
                <div class=text style="padding:10px;"><noscript><span class=text-bf>ACHTUNG: Sie haben kein Javascript aktiviert!<br></span>
                    Bitte aktivieren Sie in den Einstellungen ihres Browsers Javascript, um dieses Programm vollst&auml;ndig nutzen zu k&ouml;nnen!</noscript></div>
                <div class=text style="padding:10px;"><?php if($this->message!=null) echo $this->message; ?></div>
            </td>
            <td class="rightFrame"></td>
        </tr>
        <tr>
            <td class="bottomFrame" colspan="3" valign="bottom"><?php include_once 'bottomFrame.php'; ?></td>
        </tr>
    </table>
</html>
