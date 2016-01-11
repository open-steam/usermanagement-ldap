<?php
$sessionRegistry = SessionRegistry::getInstance();
$school = $sessionRegistry->get('school');
$district = $sessionRegistry->get('district');
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td style="width:10px;height:10px;background-color:#efefef;"></td>
	<td colspan=2 style="width:780px;height:10px;background-image:url(img/balken-oben.gif);background-repeat:repeat-x;"></td>
	<td style="width:10px;height:10px;background-color:#efefef;"></td>
</tr>
<tr>
	<td style="width:10px;height:20px;background-color:#efefef;"></td>
	<td colspan=2 style="width:780px;height:10px;text-align:right;vertical-align:top;font-family:verdana;font-size:10px;background-color:#336699;"></td>
	<td style="width:10px;height:20px;background-color:#efefef;"></td>
</tr>
<tr>
	<td style="width:10px;height:70px;background-color:#efefef;display:block;"></td>
	<td valign="top" style="background-color: #336699;"><img src="img/logo.gif" width=300 height=50></td>
	<td valign="bottom" align="right" style="background-color: #336699;"><?php if($sessionRegistry->get('accessLevel') >= 4) echo '<div style="font-family:verdana;font-weight:bold;font-size:11px;padding-bottom:5px;padding-right:5px;color:#ADC6D6;">Aktuelle Schule: '.str_replace('ou=', '', $school).' ('.str_replace('ou=', '', $district).')</div>'; ?></td>
	<td style="width:10px;height:70px;background-color:#efefef;display:block;"></td>
</tr>
<tr>
	<td style="width:10px;height:1px;background-color:#efefef;"></td>
	<td colspan=2 style="width:780px;height:1px;background-color:#006699;"></td>
	<td style="width:10px;height:1px;background-color:#efefef;"></td>
</tr>
</table>