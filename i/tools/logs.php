<?php
/* ======================================================== *\
** 			igloo backend manager - logs
** 		
** 	The igloo logs tool allows you to browse the access logs
** for the server. 
\* ======================================================== */

// Check for permission
if ( (isset ($_GET['thekey'])) && ($_GET['thekey'] == 'This is an IGLOO key! 87651234') && ($_SERVER['REMOTE_ADDR'] == '128.232.130.223') ) { //'82.43.146.127') ) {
	usleep(1000000);
	ini_set ( 'date.timezone', 'GMT' );
	
	// database settings
	$dbHostname		= 'localhost';
	$dbUsername		= 'alejrb_logview';
	$dbPassword		= 's7BaELw8BV4$U)ixNPU>Qxbi';
	$link = mysql_connect($dbHostname, $dbUsername, $dbPassword) or die('e');
	mysql_select_db('alejrb_logs');
	
	if (isset($_GET['limit'])) { $limit = ' LIMIT '.$_GET['limit']; } else { $limit = ' LIMIT 10'; }
	
	$q = mysql_query("SELECT * FROM logs ORDER BY id DESC".$limit) or die(mysql_error());
	echo ('<table>');
		while ($d = mysql_fetch_array($q)) {
			echo('<tr><td width="20px" valign="top" style="border-bottom: 1px solid #000; border-right: 1px solid #000;">'.$d['id'].'</td><td width="150px" valign="top" style="border-bottom: 1px solid #000;">'.date('h:i:sa d/m/Y', $d['timestamp']).'</td><td width="300px" valign="top" style="border-bottom: 1px solid #000;">'.$d['server_vars'].'</td><td width="300px" valign="top" style="border-bottom: 1px solid #000;">'.$d['request_vars'].'</td></tr>');
		}
	echo ('</table>');
	
	echo ('<div style="width: 100px; text-align: center; margin: auto; margin-top: 20px;">');
		if (!isset($_GET['limit'])) echo ('<a href="logs.php?thekey='.$_GET['thekey'].'&limit=10,10">next</a>');
		if (isset($_GET['limit'])) {
			if (strpos($_GET['limit'], ',') > 0) {
				$l = substr($_GET['limit'], 0, strpos($_GET['limit'], ','));
				
				if ($l > 0) echo ('<a href="logs.php?thekey='.$_GET['thekey'].'&limit='.($l-10).',10">prev</a> | ');
				echo ('<a href="logs.php?thekey='.$_GET['thekey'].'&limit='.($l+10).',10">next</a>');
			} else {
				echo ('<a href="logs.php?thekey='.$_GET['thekey'].'&limit='.($_GET['limit']-10).',10">prev</a> | <a href="logs.php?thekey='.$_GET['thekey'].'&limit='.($_GET['limit']+10).',10">next</a>');
			}
		}
	echo ('</div>');
} else {
	include '../includes/tracker.php';
	exit ();
}
	
// Exit
	echo('e');
	exit();

?>