<?php
/* ======================================================== *\
** 			igloo backend manager - retrieve
** 		
** 	The igloo retrieve module looks up, on request, the scores
** 	 and details of a user account. In the event that the user
** 	 is not present in the database, they are looked up and 
**	 added to the database with relevant scores based on their
**	 account history.
\* ======================================================== */

// Not an access point
if ( ! defined ( 'igloo' ) ) {
	include '../includes/tracker.php';
	exit ();
}

// Verify GET request.
	// Check for user actions.
if ( ( isset($_GET['session']  )) && ( isset ( $_GET['me'] ) ) ) {
	$session = $_GET['session'];
	$user = $_GET['me'];
} else {
	echo ( 'Missing required parameters.' );
	exit ();
}

// Verify the session
	$checkSession 	= $session;
	$checkUser 		= $user;
	require_once	( 'verify.php' );
	
	header ( 'Content-type: application/x-javascript' );
	if ( $verified == false ) {
		echo ( 'igloo.iglooNet.retrieveMain (\'failed\');' );
		exit ();
	}
	
// Begin main actions.
	// get the last update made for this session
	$q = mysql_query("SELECT lastrequest FROM sessions WHERE session_key='$session'") or die(mysql_error());
	$d = mysql_fetch_row($q);
	$lastreq = $d[0];
	
	// set the current time for the update
	$now = mktime();
	$q = mysql_query("UPDATE sessions SET lastrequest='$now' WHERE session_key='$session'") or die(mysql_error());
	
	// we'll need to output a Javascript object as an associative array
	echo ('var iglooNetScores = new Object();');
	
	// database handling
	if (isset($_GET['cachebypass'])) if ($_GET['cachebypass'] == 'true') { $lastreq = -1; echo('/* request bypassed cache */'); }
		$q = mysql_query("SELECT * FROM users WHERE lastupdate > $lastreq") or die('MySQL Error r1');
	
	$outlim = 3000;
	$outcount = 0;
	while ($d = mysql_fetch_array($q)) {
		if ( ($outcount >= $outlim) && ($_GET['dev'] == 'true') ) break;
		$title = addslashes($d['title']);
		
		if ($d['type'] == 'u') {
			echo ('iglooNetScores[\''.$title.'\'] = [\'u\', '.$d['details_score'].'];'."\r\n");
		} else if ( ($d['type'] == 'p') && ($d['details_flags'] != '') ) {
			echo ('iglooNetScores[\''.$title.'\'] = [\'p\', \''.$d['details_flags'].'\'];'."\r\n");
		}
		
		$outcount ++;
	}
	
	// finished output
	echo ( 'igloo.iglooNet.retrieveMain (\'ok\');' );

?>