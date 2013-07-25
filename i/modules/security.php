<?php

/* ======================================================== *\

** 			igloo backend manager - security

** 		

** 	The igloo security module helps prevents invalid requests

** 	 from influencing the data set used to flag edits for 

** 	 reversion by a human editor. It utilises sessions, file

** 	 (monobook) checking and edit watching to ensure the user

**	 performing the request is online and using igloo.

**

**	If they are, security will allow the request to alter the 

**	 dataset. Otherwise, an error will be returned. These are

**	 deliberately vague, offering only a code that can be used

**	 for debugging.

\* ======================================================== */



// Not an access point

if ( ! defined ( 'igloo' ) ) {

	include '../includes/tracker.php';

	exit ();

}



// Verify $_GET request

	// Check for inclusion.

if ( isset ( $_GET['include'] ) ) { 

	$includeJs = $_GET['include'];

} else { $includeJs = false; }



// Check for inclusion requests.

if ( $includeJs != false ) {

	if ( $includeJs == 'sessionHandleDwa' ) {

		// Check for permission.

		$perm = false;

		foreach ( $refererSecurity as $ref ) {

			if ( strpos( $_SERVER['HTTP_REFERER'], $ref ) !== false ) { 

				$perm = true; break;

			}

		}

		if ( $perm == false ) {

			header ( 'Content-type: application/x-javascript' );

			echo ( 'var iglooServerError = \'token-mismatch: your request token could not be verified by the server\'; var iglooJavascriptLoadComplete = true;' );

			exit (); 

		}

		

		// If successful, provide access to the second level of session generation functions.

		header ( 'Content-type: application/x-javascript' );

		

$output = <<<OUTFUNC

			/* function takes provided server key, appends username, alpha shifts everything, returns new key */

			function addOwnHash ( text ) {

				//var newtext = text.split ( '' );

				

				return text;

			}

			

			function genSessionDwa ( serverKey ) {

				var addUser = addOwnHash ( wgUserName );

				

				var myKey = "sert4fdiwu45t3" + addUser + serverKey;

				var alpha = new Array ( "O","a","Y","b","H","_","I","c","R","B","d","P","e","f","D","g","J","h","i","L","E","A","j","k","l","m","W","Z","n","X","U","o","p","Q"," ","V","q","F","K","r","N","s","t","u","C","v","w","x","y","z","M","0","1","2","3","G","4","5","T","6","7","S","8","9" );

				

				var newKey = '';

				for ( var i = 0; i < myKey.length; i ++ ) {

					var tempStr = myKey.substr ( i, 1 );

					for ( var j = 0; j < alpha.length; j ++ ) {

						if ( alpha [j] == tempStr ) {

							tempStr = alpha [ ( ( j + 19 ) % alpha.length ) ];

							break;

						}

					}

					newKey += tempStr;

				}

				

				return newKey;

			}

			

			/* assert that the JavaScript has loaded from the server */

			var iglooJavascriptLoadComplete = true;

OUTFUNC;

	echo ( $output );

	}

	exit ();

}



// Verify GET request.

	// Check for user actions.

if ( ! isset ( $_GET['user'] ) ) { 

	echo ('Missing required parameters.');

	exit ();

} else { $user = mysql_real_escape_string ( $_GET['user'] ); $neuser = $_GET['user']; }



if ( ! isset ( $_GET['session'] ) ) { 

	$existingSession = false;

} else { $existingSession = mysql_real_escape_string ( $_GET['session'] ); }	



// Determine where in the handshaking procedure we currently are.

if ( $existingSession == false ) { 

	// Initial request

	// Check for active sessions with that user, and terminate any that exist.

	$q = mysql_query ( "SELECT * FROM sessions WHERE username='$user' AND ( active='1' OR active='2' )" ) or die ( mysql_error () );

	$n = mysql_num_rows ( $q );

	if ($n > 0) {

		$q = mysql_query ( "UPDATE sessions SET active='0' WHERE username='$user' AND ( active='1' OR active='2' )" ) or die ( mysql_error () );

	}

	

	// Create a new session key.

	$currentTime = mktime ();

	$sessionClean = $currentTime . $user;

	$sessionDirty = mysql_real_escape_string ( md5 ( hash ( 'sha256', $sessionClean ) ) );

	

	// Insert values into database.

	$q = mysql_query ( "INSERT INTO sessions(username, ip, timestamp_created, timestamp_touched, session_key, active) VALUES('$user','$user_ip','$currentTime','$currentTime','$sessionDirty', '1')" ) or die ( mysql_error ( 'MySQL Error s3' ) );

	

	// Return session string [A] to the client. At this point, the session is still inactive and cannot be used. It will also be destroyed if another is created from the same

	// user.	

	header ( 'Content-type: application/x-javascript' );

	echo ( 'var iglooSessionRequest = \''.$sessionDirty.'\';' );

} else {

	// Second level request. The client has recived the session from the server, used the genSessionDwa code to create a new key, and sent it back with the username.

	// Retrieve the session we sent to the client from the database.

	header ( 'Content-type: application/x-javascript' );

	

	// Handle authentication

		/*

		The following authentication flags are in use at the current time.

		-- a = igloo admin (admins can assign moderator permissions from within the interface)

		-- m = igloo moderator (moderators can assign trusted/whitelisted status and suspend users from igloo)

		-- s = user suspended (suspended users cannot use igloo)

		-- t = trusted user (trusted users can mark edits as good - all trusted user accounts will do this in the background as they use igloo)

		-- d = developer (developers assign administrator permissions, can bypass a dev-only server lock, and can switch on require whitelist)

		-- w = whitelisted (whitelited users can use igloo even when the program is in whitelist only mode)

		*/

	if ( $iglooAuthMode == 'whitelist' ) {

		$q = mysql_query ( "SELECT * FROM whitelist WHERE username='$user'" ) or die ( 'MySQL Error s0' );

		$n = mysql_num_rows ( $q );

		if ( $n === 0 ) {

			$q = mysql_query ( "UPDATE sessions SET active='101',session_key='$newKey' WHERE username='$user' AND active='1'" ) or die ( mysql_error ( 'MySQL Error s8' ) );

			echo ( 'var iglooServerError = \'igloo-fail-auth: the server received your request but actively chose to refuse it, because your account is not on the whitelist. Contact User:Ale_jrb for assistance.\'; var iglooJavascriptSessionComplete = true;' );

			exit();

		}

	}/* elseif ( $iglooAuthMode == 'blacklist' ) {

		$q = mysql_query("SELECT * FROM blacklist WHERE username='$user'") or die('MySQL Error s0');

		$n = mysql_num_rows($q);

		if ($n > 0) {

			$q = mysql_query ( "UPDATE sessions SET active='102',session_key='$newKey' WHERE username='$user' AND active='1'" ) or die ( mysql_error ( 'MySQL Error s8' ) );

			echo ('var iglooServerError = \'igloo-fail-auth: the server received your request but actively chose to refuse it, because your account has been suspended. Contact User:Ale_jrb for assistance.\'; var iglooJavascriptSessionComplete = true;');

			exit();

		}

	}*/

		

	$q = mysql_query ( "SELECT * FROM sessions WHERE username='$user' AND active='1' LIMIT 1" ) or die ( 'MySQL Error s4' );

	$d = mysql_fetch_row ( $q );

	$sessionTimestampStart = $d [3];

	

	$regenSessionDirty = mysql_real_escape_string ( md5 ( hash ( 'sha256', $sessionTimestampStart . $user ) ) );

	$myKey = 'sert4fdiwu45t3' . $neuser . $regenSessionDirty;

	$alpha = array ( "O","a","Y","b","H","_","I","c","R","B","d","P","e","f","D","g","J","h","i","L","E","A","j","k","l","m","W","Z","n","X","U","o","p","Q"," ","V","q","F","K","r","N","s","t","u","C","v","w","x","y","z","M","0","1","2","3","G","4","5","T","6","7","S","8","9" );

	

	$sessionNewKey = '';

	for ( $i = 0; $i < strlen ( $myKey ); $i ++ ) {

		$tempStr = substr ( $myKey, $i, 1 );

		for ( $j = 0; $j < count ( $alpha ); $j ++ ) {

			if ( $alpha [$j] == $tempStr ) {

				$tempStr = $alpha [ ( $j + 19 ) % count ( $alpha ) ];

				break;

			}

		}

		$newKey .= $tempStr;

	}

	$newKey = mysql_real_escape_string ( $newKey );

	

	// Insert the user into the user table, so we can track their hunter score.

	$q = mysql_query ( "SELECT * FROM users WHERE title='$user'" ) or die('MySQL Error s5');

	$n = mysql_num_rows ( $q );

	if ( $n === 0 ) {

		$time = mktime ();

		$q = mysql_query ( "INSERT INTO users (title, lastupdate) VALUES ('$user','$time')" ) or die ( 'MySQL Error s6' );

	}

	

	if ( $newKey == $existingSession ) {

		if ( $iglooEnabled != true ) {

			echo ( 'var iglooServerError = \'igloo-dis: the server reports that igloo is disabled\'; var iglooJavascriptSessionComplete = true;' );

			exit ();

		}

	

		$q = mysql_query ( "UPDATE sessions SET active='2',session_key='$newKey' WHERE username='$user' AND active='1'" ) or die ( mysql_error ( 'MySQL Error s7' ) );

		echo ( 'var iglooSessionConfirmed = true; var iglooSessionRequest = \''.$newKey.'\'; var iglooJavascriptSessionComplete = true;' );

	} else {

		$q = mysql_query ( "UPDATE sessions SET active='100',session_key='$newKey' WHERE username='$user' AND active='1'" ) or die ( mysql_error ( 'MySQL Error s8' ) );

		echo ( 'var iglooServerError = \'ses-err: the server could not verify your session\'; var iglooJavascriptSessionComplete = true;' );

	}

}



?>