<?php

/* ======================================================== *\

** 			igloo backend manager - mark

** 		

** 	The igloo mark module stores actions taken by users to set

** people in the database as good or bad.

\* ======================================================== */



// Not an access point

if (!defined ('igloo')) {

	include '../includes/tracker.php';

	exit ();

}



// Verify GET request.

	// Check for user actions.

if ( (isset($_GET['user'])) && (isset($_GET['session'])) && (isset($_GET['me'])) ) {

	$user = mysql_real_escape_string($_GET['user']);

	$session = mysql_real_escape_string($_GET['session']);

	$me = mysql_real_escape_string($_GET['me']);

} else {

	echo ('Missing required parameters.');

	exit ();

}



// Verify the session

	$checkSession 	= $session;

	$checkUser 		= $me;

	require_once	( 'verify.php' );

	

	if ( $verified == false ) {

		exit ();

	}

	

// Begin main actions.

	if ( $user == $me ) exit();

	

	$now = mktime ();

	$expires = $now + ( 8 * 24 * 60 * 60 );

	

	// expire old users

	$q = mysql_query ( "DELETE FROM users WHERE expires < $now AND expires != 0" ) or die ( 'MySQL Error mark 1' );

		

	// investigate

	// igloo will automatically lookup unrecognied users when they are viewed by an editor. These users will be entered into the user database.

	if ( $_GET['action'] === 'investigate' ) {

		//if ( strpos ( $flags, 't' ) !== false ) {

			score_user ( $user, $me );

		//}

	}



	// What mark is this?

	elseif ($_GET['action'] == 'markbad') {

		// markbad

		

		// - get the vandal scores

		// vandal

		$q = mysql_query("SELECT * FROM users WHERE title='$user'");

		if (mysql_num_rows($q) <= 0) {

			$q = mysql_query("INSERT INTO users (title, lastupdate, lasttouched, expires) VALUES ('$user','$now','$me','$expires')");

			$q = mysql_query("SELECT * FROM users WHERE title='$user'");

		}

		$d = mysql_fetch_array($q);

		$vandal = $d['details_score'];

		

		// update vandal score

		if ($vandal <= 0.3) { exit(); }

		elseif ($vandal <= 0.4) { $vandalN = 0.6; }

		else { $vandalN = 0.9; }

		

		$q = mysql_query("UPDATE users SET details_score='$vandalN', lasttouched='$me', expires='$expires' WHERE title='$user'");

		

	} else {

		echo ('e');

		exit();

	}



?>