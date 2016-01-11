<?php
	$helperID = 1;

	/*
	 * Startet die Ausgabe des HTML-Codes einer Dialogbox.
	 * 
	 * $title:		Titel der Dialogbox.
	 * $helperText:	Text des Hilfetextes. Darf HTML-Tags beinhalten!
	 */
	function boxStart($title, $helperText){
		global $helperID;
		$helperID ++;
		$id = 'boxHelper_'.$helperID;
		
		echo '<table cellpadding=0 cellspacing=0 border=0>';
		echo '<tr>	
					<td class=boxHeadlineL></td>
					<td class=boxHeadline>'.$title."</td>
					<td class=boxHelperSign><a href=# onclick=\"javascript: void openHelper('".$id."');\">?</a></td>	
				</tr>";
		echo '<div class=boxHelper id='.$id.'>';
			echo '<div class=helpTextHeadline>Hinweis</div>';
			echo '<div class=helpText>'.$helperText.'</div>';	
			echo "<div style=\"text-align:right;\"><a href=# onclick=\"javascript: void closeHelper('".$id."');\">[schlie&szlig;en]</a></div>";
			
		echo '</div>';
		echo '<tr><td colspan=3><img src="img/one.gif" width="1" height="1"></td></tr>';
		echo '<tr><td colspan=3>';
			echo '<table cellpadding=0 cellspacing=0 border=0>';
				echo '<tr><td class=boxCornerTL></td><td class=boxBorderT></td><td class=boxCornerTR></td></tr>';
				echo '<tr><td class=boxBorderL></td><td class=boxContent>';
		
	}
	
	/*
	 * Beendet eine Dialogbox.
	 */
	function boxEnd(){
				echo '</td><td class=boxBorderR></td></tr>';
				echo '<tr><td class=boxCornerBL></td><td class=boxBorderB></td><td class=boxCornerBR></td></tr>';
			echo '</table>';
		echo '</td></tr></table>';
	}
	
	function contactForm(){
		echo '<div id=contactForm style="width:320px;display:none;">';
		echo '<table cellpadding=0 cellspacing=0 border=0>';
		echo '<tr><td style="border-left:2px solid #666666;border-top:1px solid #666666;padding-left:10px;">';
		echo '<table cellpadding=0 cellspacing=0 border=0>';
		echo '<tr><td style="width:110px;font-family:verdana;font-size:9px;height:12px;">eMail</td></tr>';
		echo '<tr><td style="padding-right:10px;"><input type=text style="width:100px;height:18px;border:none;font-size:10px;"></td></tr>';
		echo '<tr><td style="font-family:verdana;font-size:9px;height:12px;">Betreff</td></tr>';
		echo '<tr><td style="padding-right:10px;"><input type=text style="width:100px;height:18px;border:none;font-size:10px;"></td></tr>';
		echo '</table>';
		echo '</td><td valign=bottom style="border-top:1px solid #666666;"><textarea wrap="hard" rows=4 style="width:200px;height:58px;border:none;font-size:11px;"></textarea></td></tr>';
		echo '</table>';
		echo '</div>';
	}
	
	function infoForm(){
		echo '<div id=infoForm style="width:320px;height:60px;display:none;">';
		echo '<table cellpadding=0 cellspacing=0 border=0>';
		echo '<tr><td style="border-left:2px solid #666666;border-top:1px solid #666666;padding-left:10px;font-family:verdana;font-size:10px;font-weight:bold;">Version:</td><td style="font-family:verdana;font-size:10px;padding-left:10px;border-top:1px solid #666666;">0</td></tr>';
		echo '<tr><td style="border-left:2px solid #666666;padding-left:10px;font-family:verdana;font-size:10px;font-weight:bold;">Release:</td><td style="font-family:verdana;font-size:10px;padding-left:10px;">1</td></tr>';
		echo '<tr><td style="border-left:2px solid #666666;padding-left:10px;font-family:verdana;font-size:10px;font-weight:bold;">Status:</td><td style="font-family:verdana;font-size:10px;padding-left:10px;">in progress...</td></tr>';
		echo '<tr><td style="border-left:2px solid #666666;padding-left:10px;font-family:verdana;font-size:10px;font-weight:bold;">Developer:</td><td style="font-family:verdana;font-size:10px;padding-left:10px;">dalucks</td></tr>';
		echo '</table>';
		echo '</div>';
	}
	
function createMenu($id, $name, $items, $activeItem='', $additionalElements='', $zIndex=100, $width=42){
	

	if(strpos(getAgent(), 'Internet Explorer') === 0) $width_new = $width + 5;
	else $width_new = $width - 1;

	
	ksort($items);
	$length = count($items) - 1;
	$items_tmp = array_reverse($items, false);
	$openList = array();
	for($i=0; $i<=$length; $i++) $openList[$i] = 0;
	
	$lastLevel = 0;
	$items = array();
	
	foreach($items_tmp AS $dn => $cn){
		$dn = strstr($dn, 'cn=');
		$level = count(explode(',', $dn)) - 1;
		$img = array();
		
		if($level > $lastLevel) $openList[$lastLevel]++;
		
		
		if($level >= 1){
			for($i=1; $i<=$level; $i++){
				if($openList[$i] > 0 AND $i != $level) $img[] = '<img src="img/bar.jpg" style="border:none;">';
				elseif($openList[$i] === 0 AND $i != $level) $img[] = '<img src="img/blank.jpg" style="border:none;">';
				elseif($i == $level){
					if($level == $lastLevel OR $openList[$level] > 0) $img[] = '<img src="img/arrow2.jpg" style="border:none;">';
					else $img[] = '<img src="img/arrow.jpg" style="border:none;">';
				}
			}
		}
		
		if($level < $lastLevel AND $openList[$level] > 0) $openList[$level]--;
		$items[$cn] = $img;
		$lastLevel = $level;
	}
	
	$additionalElementsSize = 0;
	if(is_array($additionalElements) AND count($additionalElements) >= 1){
		foreach($additionalElements AS $key => $val){
			$items[$val] = array();
		}
		$additionalElementsSize = count($additionalElements);
	}
	$items = array_reverse($items, true);
	
	if(!array_key_exists($activeItem, $items)) $activeItem = getFirstKey($items);
	$length = count($items);
	
	echo '<div class="menu" style="z-index:'.$zIndex.';'.(($width!=42)?'width:'.$width_new.'px;margin-right:4px;':'').'">';
		echo '<ul'.(($width!=42)?' style="width:'.$width_new.'px;" ':'').'>';
			//echo '<li'.(($width!=42)?' style="width:'.$width_new.'px;" ':'').'><a class="hide"'.(($width!=42)?' style="width:'.$width_new.'px;" ':'').' id='.$id.'-activeItem style="vertical-align:middle;" href=# onclick="dropdownOpen(\''.$id.'\');">'.$activeItem.'</a>';
			echo '<li'.(($width!=42)?' style="width:'.$width_new.'px;" ':'').'><a class="hide"'.(($width!=42)?' style="width:'.$width_new.'px;" ':'').' id='.$id.'-activeItem style="vertical-align:middle;" href=# onclick="document.getElementById(\''.$id.'\').style.display=\'block\';">'.$activeItem.'</a>';
//  onclick="dropdownOpen(\''.$id.'\');"
				echo '<ul id='.$id.'>';
					$index = 1;
					foreach($items AS $cn => $img){
						if($additionalElementsSize == $index) $addStyle = 'style="background:#ffffff;border-bottom:1px dashed #6699cc;"';
						else $addStyle = '';
						if($index < $length) echo '<li><div class=defaultElement '.$addStyle.'>';
						else echo '<li><div class=bottomElement>';
						
						echo '<table cellpadding=0 cellspacing=0 border=0 style="background:#ffffff;"><tr>';
						foreach($img AS $key => $val)
							echo '<td style="width:10px;height:15px;">'.$val.'</td>';
						//echo '<td style="height:15px;vertical-align:middle;"><a href=# title="'.$cn.'" onclick="dropdownClose(\''.$id.'\', \''.$name.'\', \''.$cn.'\');" style="border:none;text-align:left;padding-left:0px;">'.$cn.'</a></td></tr></table></div>';
						echo '<td style="height:15px;vertical-align:middle;"><a href=# title="'.$cn.'" onclick="document.getElementById(\''.$id.'\').style.display=\'none\'; document.getElementById(\''.$id.'-activeItem\').innerHTML=\''.$cn.'\'; document.getElementById(\''.$name.'\').value=\''.$cn.'\';" style="border:none;text-align:left;padding-left:0px;">'.$cn.'</a></td></tr></table></div>';

						$index++;
					}
				echo '</ul>';
			echo '</li>';
		echo '</ul>';
	echo '</div>';
	echo '<input type=hidden id='.$name.' name='.$name.' value='.$activeItem.'>';
}
	
function getFirstKey($array){
	if(is_array($array)){
		$element = each($array);
		if($element != null) return $element['key'];
		else return false;
	}
	else return false;
}

function getagent()
{
  if (strstr($_SERVER['HTTP_USER_AGENT'],'Opera'))    {    
  
     $brows=ereg_replace(".+\(.+\) (Opera |v){0,1}([0-9,\.]+)[^0-9]*","Opera \\2",$_SERVER['HTTP_USER_AGENT']);
     if(ereg('^Opera/.*',$_SERVER['HTTP_USER_AGENT'])){
     $brows=ereg_replace("Opera/([0-9,\.]+).*","Opera \\1",$_SERVER['HTTP_USER_AGENT']);    }}
  elseif (strstr($_SERVER['HTTP_USER_AGENT'],'MSIE'))
     $brows=ereg_replace(".+\(.+MSIE ([0-9,\.]+).+","Internet Explorer \\1",$_SERVER['HTTP_USER_AGENT']);
  elseif (strstr($_SERVER['HTTP_USER_AGENT'],'Firefox'))
     $brows=ereg_replace(".+\(.+rv:.+\).+Firefox/(.*)","Firefox \\1",$_SERVER['HTTP_USER_AGENT']);
  elseif (strstr($_SERVER['HTTP_USER_AGENT'],'Mozilla'))
     $brows=ereg_replace(".+\(.+rv:([0-9,\.]+).+","Mozilla \\1",$_SERVER['HTTP_USER_AGENT']);
  else
     $brows=$_SERVER['HTTP_USER_AGENT'];
  return $brows;
} 
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
?>
