<?php 
//TIMING AND DEFAULTS
  $mtime = microtime();
  $mtime = explode(" ",$mtime);
  $mtime = $mtime[1] + $mtime[0];
  $starttime = $mtime; 
  $numrooms = 9;
  $numhours = 10;
  $monthstofetch = $_GET['stats'] === "all" ? 12 + intval(date("n")) : 1;
  $stats = $_GET['stats'];
  $offset = $_GET['hours'];
  $dayoffset = $_GET['days'];
  $happycolour = '#21D838';
  $sadcolour = '#EE4D39';
  $totrequests = 0;
  date_default_timezone_set('Europe/Dublin');
  //$refresh = (3600 - ((date("i") * 60) + date("s")));   
  function nicetime($date){
    if(empty($date)) return "No date provided";
    $periods = array("sec", "min", "hr", "day", "wk", "mth", "yr", "dc");
    $lengths = array("60","60","24","7","4.35","12","10");$now 
    = time(); $unix_date = strtotime($date);
    if(empty($unix_date)) return "Bad date";    if($now > $unix_date) {$difference = $now - $unix_date;$tense = "ago";   
    } else {$difference = $unix_date - $now;$tense  = "from now";}
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++)$difference /= $lengths[$j];
    $difference = round($difference);    if($difference != 1) $periods[$j].= "s";
    return "$difference $periods[$j] {$tense}";
  }   
  $extraparamsdays = (isset($_GET['days']) && $_GET['days'] !== 0 && $_GET['days'] !== "" ? '&days='.$_GET['days'] : '');
  $extraparamsfull = ($_GET['full'] == 1 ? "&full=1" : "");
?>

<html><head>
<!--<META HTTP-EQUIV="Refresh" CONTENT="<?php echo $refresh; ?>">-->
<meta property="fb:admins" content="1434685963"/>
<meta property="og:site_name" content="Glass Rooms"/>
<meta property="og:title" content="Glass Rooms Timetable"/>
<meta property="og:type" content="website"/>
<meta property="og:url" content="http://zachd.netsoc.ie"/>
<meta property="og:image" content="http://zachd.netsoc.ie/calendar.png" />
<link rel="shortcut icon" href="cal.ico" type="image/x-icon" />
<title>Glass Rooms Timetable</title>
<style type="text/css">
body { text-align:center; font-family: Helvetica, Arial, sans-serif;}
a:link {text-decoration:none; color: #0000FF;}
a:visited {text-decoration:none; color: #0000FF;}
a:hover {text-decoration:underline; color: #0000FF;}
a:active {text-decoration:underline; color: #0000FF;}
ol {font-weight: bold;}
ol li span.label {font-weight:normal;}
tr, th { overflow: hidden; height: 40px; }
/*th.single {white-space:nowrap;}*/
/* IPHONE 5 */
@media only screen and (min-device-width : 320px) and (max-device-width : 568px) and (orientation : landscape) { 
.portrait {display:none !important;}
tr { height: 35px !important; }
.landscape { display:inline !important;}
}
@media only screen and (min-device-width : 320px) and (max-device-width : 568px) and (orientation : portrait) {
.portrait {display:inline !important;}
tr { height: inherit !important; }
.landscape {display:none !important;}
}

/* IPHONE 2G - 4S */
@media only screen and (min-device-width : 320px) and (max-device-width : 480px) and (orientation : landscape) {
.portrait {display:none !important;}
tr { height: 35px !important; }
.landscape { display:inline !important;}
}
@media only screen and (min-device-width : 320px) and (max-device-width : 480px) and (orientation : portrait) {
.portrait {display:inline !important;}
tr { height: inherit !important; }
.landscape {display:none !important;}
}

</style>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript">
<!--
    function toggle_visibility_leader(id) {
       var e = document.getElementById(id);
       var lshow = document.getElementById("leaderboardslinkshow");
       var lhide = document.getElementById("leaderboardslinkhide");
       if(e.style.display == 'block'){
          lshow.style.display = 'block';
          lhide.style.display = 'none';
          e.style.display = 'none';
       } else {
          lshow.style.display = 'none';
          lhide.style.display = 'block';
          e.style.display = 'block';
       }
    }
    function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == '')
          e.style.display = 'none';
       else
          e.style.display = '';
    }
//-->
</script>
<script type="text/javascript">
(function countdown(remaining) {
    if (remaining === -1) {
        var date = new Date();
        remaining = 3600 - (date.getMinutes() * 60) - date.getSeconds() - 299;
    }
    if (remaining <= 0)
       // location.reload(true);
    setTimeout(function () {
        countdown(remaining - 1);
    }, 1000);
})(-1);
</script>
<meta name="viewport" content="width=480; initial-scale=0.6666; maximum-scale=1.0; minimum-scale=0.6666" />

</head><body>
<a name="top"></a>
<h1><img src="cal.png" style="vertical-align:text-bottom;" />  Glass Rooms Timetable</h1>

<?php
echo (function_exists('curl_version') ? "" : "<center><b><font color=\"red\">Sorry, CURL is not installed, therefore this webpage is not currently functional.</font></b></center>");

/* gets the data from a URL */
function get_source($url, $month, $year){ 
global $totrequests;
$totrequests++;
$ch = curl_init();$timeout = 5;curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "Month=".$month."&Year=".$year."&Type=Small+Table");
curl_setopt($ch,CURLOPT_URL,$url); curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); $data = curl_exec($ch);
curl_close($ch); return iconv("UTF-8","UTF-8//IGNORE", $data);
}
$tablestartcode = '<table border="1" bordercolor="#FFFFFF" style="border-color:#FFFFFF; background-color:#FFFFCC; table-layout: fixed; width: 100%; text-align:center;" cellpadding="3" cellspacing="0">';

$array = array();
$totalbookings = 0;
// Counters
$studentlist = array();
$studentlistnum = 0;
$courselist = array();
$courselistnum = 0;
$datelist = array();
$datelistnum = 0;
$roomlist = array();
$roomlistnum = 0;
$reasonlist = array();
$reasonlistppl = array();
$reasonlistnum = 0;

for($x = 1; $x <= $monthstofetch; $x++){
  for($i = 1; $i <= $numrooms; $i++){  
    $urlrooms = "https://www.scss.tcd.ie/cgi-bin/webcal/sgmr/sgmr".$i.".pl";
    if($monthstofetch == 1)
      $sourcerooms = get_source($urlrooms, date("m"), date("Y"));
    else{
      $monthtoget = ($x % 12 == 0 ? 12 : $x % 12);
      $yeartoget = (2013 + ($x % 12 !== 0 ? floor($x / 12) : floor(($x / 12) - 1)));
      //echo "Getting ".$monthtoget.", ".$yeartoget."<br />";
      $sourcerooms = get_source($urlrooms, sprintf('%08d', $monthtoget), $yeartoget);
    }
    $array[$i] = array();
    
    // Match Dates
    $expression = '<STRONG><[^=]*="#000000">([^<]*) \([^\)]*\):</[^>]*></[^>]*></[^>]*></TR><TR><[^=]*="#ccccff"><[^=]*="#0000ff">(.*?)</FONT></TD></TR>(<TR><[^=]*="#ffffff">|</TABLE>)';
    preg_match_all('@'.$expression.'@i', $sourcerooms, $arraydates, PREG_SET_ORDER);
    $repyears = array(
    '1' => '(1st Year)',
    '2' => '(2nd Year)',
    '3' => '(3rd Year)',
    '4' => '(4th Year)',
    '5' => '(Msc)'
  );  
    $replacements = array(
    'ba' => 'CS ',
    'csl' => 'CSL ',
    'csll' => 'CSLL ',
    'bacsb' => 'CSB ',
    'msiss' => 'MSISS ',
    'babc' => 'B&C ',
    'bai' => 'BAI ',
    'bscis' => 'IS ',
    'dipis' => 'IS (Dip) ',
    'mschi' => 'HI 5',
    'mscmis' => 'MSISS 5'
  );
    for($j = 0; $j < count($arraydates); $j++){
      $array[$i][$arraydates[$j][1]] = array();
      
      // Match Times
      $expressioninner = '(\d\d:\d\d)-(\d\d:\d\d) ([^\[]*)\[([^\]]*)\] ([^<]*)<?B?R?>?';
      preg_match_all('@'.$expressioninner.'@i', $arraydates[$j][0], $arraytimes, PREG_SET_ORDER);
      for($k = 0; $k < count($arraytimes); $k++){
        $currentname = preg_replace("~([^ -]*) [\-a-zA-Z]{3,}( .*)~i", "$1$2", substr($arraytimes[$k][3], 0, -1));
        //if($currentname == "Ruth Lehane")$currentname = "Ruth Number 2";
        $course_with_name = preg_replace('/([a-z]+)/e', '$replacements["$1"]', $arraytimes[$k][4]);
        $course_name_edit = $course_with_name == "" ? strtoupper(substr($arraytimes[$k][4], 3)) . 
          (substr($arraytimes[$k][4], 0, 3) === "msc" ?" 5" : "") : $course_with_name;
        $course_name_year = strlen($course_name_edit) <= 1 ? "" : preg_replace('/([0-9]+)/e', '$repyears["$1"]', $course_name_edit);
        $currentcourse = $course_name_year == "" ? $arraytimes[$k][4] : $course_name_year;
        $array[$i][$arraydates[$j][1]][$arraytimes[$k][1].'-'.$arraytimes[$k][2]][0] = $currentname;
        $array[$i][$arraydates[$j][1]][$arraytimes[$k][1].'-'.$arraytimes[$k][2]][1] = "[".preg_replace("~[\(\)]*~i", "", $currentcourse)."] ".htmlspecialchars($arraytimes[$k][5]);
        $tempdate = $arraydates[$j][1];
        $datelist[$datelistnum++] = date('l', strtotime(preg_replace("~(\d*) (\w*) (\d*)~i", "$2 $1, $3", $tempdate)));
        $roomlist[$roomlistnum++] = "Room " . $i;
        //$courselist[$courselistnum++] = $arraytimes[$k][4];
        $courselist[$courselistnum++] = $currentcourse;
        $studentlist[$studentlistnum++] = $currentname."[".$currentcourse."]";
        $reasonlistppl[$reasonlistnum] = $currentname."[".$currentcourse."]";
        $reasonlist[$reasonlistnum++] = preg_replace("~(.*) $~i", "$1", htmlspecialchars($arraytimes[$k][5]))."[".preg_replace("~(\d*) (\w*) (\d*)~i", "$2 $1, $3", $tempdate)."[".$arraytimes[$k][1].'-'.$arraytimes[$k][2]."]]";
        $totalbookings++;
      }
    }
  }
}

$roomdate = date("j M Y", strtotime(($dayoffset > 0 ? "+" : "-").abs($dayoffset)." day"));
$datenow = date("j M Y");
$timenow = date("H:").'00-'.date("H:", strtotime ("+1 hour")).'00';

$countavailable = 0;
for($j = 1; $j <= $numrooms; $j++){
  $roomtime = date("H:").'00-'.date("H:", strtotime ("+1 hour")).'00';
  $roomtimefirsthalf = substr($roomtime, 0, -5).date("H:", strtotime ("+2 hour")).'00';
  $roomtimesecondhalf = date("H:", strtotime ("-1 hour")).'00-'.substr($roomtime, 6);
  if(!empty($array[$j][$roomdate][$roomtime]))
    echo '';
  else if(!empty($array[$j][$roomdate][$roomtimefirsthalf]))
    echo '';
  else if(!empty($array[$j][$roomdate][$roomtimesecondhalf]))
    echo '';
  else {
    $roomavailable = $j;
    $countavailable++;
  }
}
if($_GET['full'] == 1){
  $numhours = 24;
  $offset = date("H") * -1;
}

echo '<h2 style="color:'.($countavailable == 0 ? $sadcolour : $happycolour).';">'.($countavailable == 1 ? 'Room '.$roomavailable.' is' : (($countavailable == 0 ? 'No' : ($countavailable == $numrooms ? 'All' : $countavailable)).' rooms')).' available from '.$timenow, ($countavailable == 0 ? ' :(' : '');

if(isset($_GET['hours']) || (isset($_GET['days']) && $_GET['days'] !== 0 && $_GET['days'] !== ""))
  echo (isset($_GET['days']) ? ' on '.$roomdate : '').'</h2>';
else
  echo '</h2>';

echo $tablestartcode;
echo '<tr>';
echo '<td style="width:15px;font-weight:bold;" rowspan="'.($numhours + 1).'"><a href="?days='.($_GET['days'] - 1).($_GET['full'] == 1 ? "&full=1" : "").'" title="Show '.(!isset($_GET['days']) || $_GET['days'] == 0 ? 'Yesterday' : date("j M Y", strtotime("-1 day", strtotime($roomdate)))).'"><</a></td>';
for($j = 0; $j <= $numrooms; $j++){
    if($j == 0)
      echo '<td><i><b><span class="portrait" style="display:none">'.substr($roomdate, 0, -5).'</span><span class="landscape">'.$roomdate.'</span></b></i></td>';
    else
     echo '<td><b><span class="portrait" style="display:none">'.$j.'</span><span class="landscape">Room '.$j.'</span></b></td>';
}
echo '<td style="width:15px;font-weight:bold;" rowspan="'.($numhours + 1).'"><a href="?days='.($_GET['days'] + 1).($_GET['full'] == 1 ? "&full=1" : "").'" title="Show '.(!isset($_GET['days']) || $_GET['days'] == 0 ? 'Tomorrow' : date("j M Y", strtotime("+1 day", strtotime($roomdate)))).'">></a></td>';
echo '</tr>';
for($i = (0 + $offset); $i < ($numhours + $offset); $i++){
  for($j = 0; $j <= $numrooms; $j++){
    $roomtime = date("H:", strtotime ("+".$i." hour")).'00-'.date("H:", strtotime ("+".($i + 1)." hour")).'00';
    $roomtimefirsthalf = substr($roomtime, 0, -5).date("H:", strtotime ("+".($i + 2)." hour")).'00';
    $roomtimesecondhalf = date("H:", strtotime (($i == 0 ? "-1 hour" : "+".($i - 1)." hour"))).'00-'.substr($roomtime, 6);
    if($j == 0){
      echo '<td'.($roomtime == $timenow && $roomdate == $datenow ? ' style="font-weight:bold;"' : '').'><span class="portrait" style="display:none">'.substr($roomtime, 0, -6).'</span><span class="landscape">'.$roomtime.'</span></td>';
    } else {
      if(!empty($array[$j][$roomdate][$roomtime])) {
        echo '<th class="single" style="background-color:'.$sadcolour.'" title="'.$array[$j][$roomdate][$roomtime][1].'"><span><span class="portrait" style="display:none">&#10008;</span><span class="landscape">'.$array[$j][$roomdate][$roomtime][0].'</span></span></th>';
      } else if(!empty($array[$j][$roomdate][$roomtimefirsthalf])) {
        echo '<th class="double" rowspan="2" style="background-color:'.$sadcolour.'" title="'.$array[$j][$roomdate][$roomtimefirsthalf][1].'"><span><span class="portrait" style="display:none">&#10008;</span><span class="landscape">'.$array[$j][$roomdate][$roomtimefirsthalf][0].'</span></span></th>';
      } else if(!empty($array[$j][$roomdate][$roomtimesecondhalf])) {
        if($i == (0 + $offset)){
          echo '<th class="single" style="background-color:'.$sadcolour.'" title="'.$array[$j][$roomdate][$roomtimesecondhalf][1].'"><span><span class="portrait" style="display:none">&#10008;</span><span class="landscape">'.$array[$j][$roomdate][$roomtimesecondhalf][0].'</span></span></th>';
        }
      } else {
        echo '<td class="single" style="'.($roomtime == $timenow && $roomdate == $datenow ? 'background-color:'.$happycolour.'; font-weight:bold;' : '').'"><a href="https://www.scss.tcd.ie/cgi-bin/webcal/sgmr/sgmr'.$j.'.request.pl" target="_blank" title="Room '.$j.' - '.$roomtime.'" style="'.($roomtime == $timenow && $roomdate == $datenow ? 'color:white;' : '').'"><span class="portrait" style="display:none">&#10004;</span><span class="landscape">Available</span></a></td>';
      }
    }
  }
	echo '</tr>';
}

echo '</table>';

if(isset($_GET['hours']) || (isset($_GET['days']) && $_GET['days'] !== 0 && $_GET['days'] !== "")) {
  echo '<br /><a href="?'.$extraparamsfull.'">Click here for the current time.</a><br />Currently displaying'.(isset($_GET['days']) ? ' '.$roomdate : ' today,'), (isset($_GET['hours']) ? ($offset > 0 ? " +" : " -").abs($offset).' hours' : '').'.<br />';
}

echo '<br /><a name="leaderboards"></a><a href="#leaderboards" id="leaderboardslinkshow" onclick="toggle_visibility_leader(\'leaderboardsdiv\');"><b>v</b> Show '.($monthstofetch == 1 ? date("F") : "2013-".date("Y")).' Statistics ('.number_format($totalbookings).' Bookings)</a><a href="#top" id="leaderboardslinkhide" style="display:none;" onclick="toggle_visibility_leader(\'leaderboardsdiv\');"><b>^</b> Hide Statistics</a>';


function leaderboard($name, $array, $count, $table, $totalbookings){
  global $reasonlist, $reasonlistppl;
  $array = array_count_values($array);
  arsort($array);
  echo $table;
  $headertext = '<b>'.$name.' Stats</b> (Avg: '.round($count / count($array), 2).')';
?>
  <tr><td class="portrait" colspan="2" style="display:none;text-align:center;"><?php echo $headertext; ?></td><td class="landscape" colspan="<?php echo ($name == "Student" ? 4 : 3); ?>" style="text-align:center;"><?php echo $headertext; ?></td></tr>
  <?php $i = 1;
  foreach ($array as $key => $value) {
      echo '<tr'.($name === "Student" ? " class=\"studenthover\"" : "").'><td class="landscape" style="width:25px;"><b>'.$i.'.</b></td><td'.($name == "Student" ? ' title="'.preg_replace("~.*\[(.*)\]~i", "$1", $key).'"' : '').'><b>'.preg_replace("~\[.*\]~i", "", $key).'</b></td><td title="'.round(($value / $totalbookings) * 100, 2).'%"><i><span class="portrait" style="display:none;">'.$value.'</span><span class="landscape">'.number_format($value).' Booking'.($value == 1 ? '' : 's').'</span></i> '.($i == 1 ? /*'<i><font color="#ff0000"> - Congratulations! You\'re a prick!</font></i>'*/'' : '').'</td>';
      if($name === "Student"){
        $lowername = strtolower(str_replace(" ", "", preg_replace("~\[.*\]~i", "", $key)));
        echo '<td onclick="toggle_visibility(\''.$lowername.'-list\');">v</td>';
        echo '</tr>';
        echo "<tr id=\"".$lowername."-list\" style=\"display:none;\"><td colspan=\"4\"><ol>";
        foreach ($reasonlist as $person => $reason){
            if($reasonlistppl[$person] == $key)
                echo "<li>".$reason."</li>";
            }
        echo "</ol></td></tr>";
      }
      if($i == 20 && count($array) > 20)
        echo '<tr onclick="toggle_visibility(\''.$name.'div\');this.style.display=\'none\';" onmouseover="this.style.textDecoration=\'underline\'" style="color:blue;" class="button" ><td class="landscape" colspan="3">Show All '.count($array).' '.$name.'s</td><td colspan="2" class="portrait" style="display:none;">Show All '.count($array).' '.$name.'s</td></tr></table><span id="'.$name.'div" style="display:none;">'.$table;
      if($i == count($array))
        echo '</span>';
      $i++;
  }
  if($name == "Room")
    echo '<tr><td colspan="3" class="landscape" style="background-color:#FFFFFF;"></td><td colspan="2" class="portrait" style="display:none;background-color:#FFFFFF;"></td></tr>';
  echo "</table>";
}
$tablestartleader =  '<table border="1" bordercolor="#FFFFFF" style="border-color:#FFFFFF; background-color:#FFFFCC; width: 100%; text-align:center;" cellpadding="3" cellspacing="0">';
echo '<div id="leaderboardsdiv" style="display:none;width:100%;overflow:hidden;">';
echo '<div style="float:left;width:37%;">';
leaderboard("Student", $studentlist, $studentlistnum, $tablestartleader, $totalbookings);
echo '</div><div style="float:left;width:30%;">';
leaderboard("Course", $courselist, $courselistnum, $tablestartleader, $totalbookings);
echo '</div><div style="float:left;width:33%;">';
leaderboard("Room", $roomlist, $roomlistnum, $tablestartleader, $totalbookings);
leaderboard("Date", $datelist, $datelistnum, $tablestartleader, $totalbookings);
echo '</div></div>';

/* Reasons
echo "
<!-- 
";$arrayreasons = array_count_values(array_map('strtolower', $reasonlist));
arsort($arrayreasons);
print_r($arrayreasons);
echo " -->
";
 End Reasons*/

//TIMING
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
//TIMING
echo '<span style="margin-top:15px;display:block;text-align:center;"><i>Site is not official. Results live.<br />
More Options: '.(!isset($_GET['full'])?' <a href="?full=1'.$extraparamsdays.'" title="Show All Bookings for '.
date("M jS").'">Full Day</a>':'<a href="?'.$extraparamsdays.'" title="Show Bookings Now">Show Now</a>').' | '.
(!isset($_GET['stats'])?' <a href="?stats=all'.$extraparamsdays.$extraparamsfull.'" title="Show Full Stats for 2013-'.
date("Y").'">Full Stats</a>':'<a href="?'.$extraparamsdays.$extraparamsfull.'" title="Show Today\'s Stats">Today\'s Stats</a>').'</i></span>
<span title="Last edited: '.nicetime(date ("y-m-d H:i", filemtime("index.php"))).'" >&copy; <a href="http://zach.ie/" target="_blank">Zachary Diebold</a> - '.number_format($totrequests).' requests in '.round(($endtime - $starttime), 1).'s. <span id="countdown"></span></span>';

echo "</body></html>";
?>
