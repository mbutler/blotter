<?php


$usr = ""; // database user
$pwd = ""; // database password
$db = ""; // database name
$host = ""; // usually localhost

$con = mysql_connect($host, $usr, $pwd);

if (!$con)
  {
  	die('Could not connect: ' . mysql_error());
  }

mysql_select_db($db, $con);


$url = "http://www.iowa-city.org/icgov/apps/police/blotter.asp";
$raw = file_get_contents($url);
$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
$content = str_replace($newlines, "", html_entity_decode($raw));
$start = strpos($content,'<table cellpadding="3" cellspacing="1" style="background-color: #333;" class="full">"');
$end = strpos($content,'</table>',$start) + 8;
$table = substr($content,$start,$end-$start);

preg_match_all("|<tr(.*)</tr>|U",$table,$rows);


foreach ($rows[0] as $row){

    if ((strpos($row,'<th')===false)){
    
        preg_match_all("|<td(.*)</td>|U",$row,$cells);
		
		preg_match_all("|<strong(.*)</strong>|U",$row,$names);
		
		preg_match_all("|<br(.*)</td>|U",$row,$addy);
		
		preg_match_all("|<strong(.*)</strong>|U",$row,$arrest_date);
		
		preg_match_all("|<strong(.*)</strong>|U",$row,$cop);
		
		preg_match_all("|<br />(.*)</td>|U",$row,$dob);
		
		$name = strip_tags($names[0][0]);
		
		$home_addy = strip_tags($addy[0][0]);
        
        $offense_date = strip_tags($arrest_date[0][1]);
		
		$offense_time = substr($offense_date, -5);
		
		$offense_date = substr($offense_date, 0, -7);
		
		$offense_date = dateconvert($offense_date);
		
		$birthday = strip_tags($dob[0][1]);
		
		$birthday = substr($birthday, 6);
		
		$birthday = dateconvert($birthday);
        
        $arrest_loc = strip_tags($cells[0][2]);
		
		$officer = strip_tags($cop[0][2]);
		
		$incident_num = strip_tags($cells[0][3]);
		
		$ca = strip_tags($cells[0][4]);
		
		$arrest_type = strip_tags($cells[0][5]);
		
		$charges = preg_split("/[0-9]\)/", $arrest_type);
		
		$show_charges = '';
		
		
		
						
		$sql="INSERT INTO arrests (name, address, dob, location, arrest_date, arrest_time, officers, ca, incident ) VALUES('$name', '$home_addy', '$birthday', '$arrest_loc', '$offense_date', '$offense_time', '$officer', '$ca', '$incident_num')";
		$result = mysql_query($sql);
		unset($charges[0]);
		
		
			
		foreach ($charges as $charge)
		{
			
			$show_charges .= $charge;
			//$show_charges .= "<br />";
			$sql3="INSERT INTO charges (charge, incident) VALUES('$show_charges', '$incident_num')";
			$result3 = mysql_query($sql3);
			$show_charges = '';
			
		}
		


        //echo "<strong>Name:</strong> " . $name . "<br /> " . "<strong>Birth date:</strong> " . $birthday . "<br />" . "<strong>Home address:</strong> " . $home_addy . "<br />" . "<strong>Arrest location:</strong> " . $arrest_loc . "<br />" . "<strong>Arrest date:</strong> " . $offense_date . "<br />" . "<strong>Arrest time:</strong> " . $offense_time . "<br />" .  "<strong>Officer(s): </strong>" . $officer . "<br />" . "<strong>Charges:</strong> " . $show_charges . "<br /><br />";
		
    
    }

}

//echo "Congratulations!  The ICPD arrest blotter for today has been added to your private database, sir.";

function dateconvert($olddate) 
{
 	$newdate = explode("/", $olddate);
	
	$year = $newdate[2];
	$month = $newdate[0];
	$day = $newdate[1];
	$zero = "0";
	
	if (strlen($month) == 1)
	{
		$month = $zero . $month;
	}
	
	if (strlen($day) == 1)
	{
		$day = $zero . $day;
	}
	
	$isodate = $year . "-" . $month . "-" . $day;
	
		
	return $isodate;	
	     
		 
}

?>
