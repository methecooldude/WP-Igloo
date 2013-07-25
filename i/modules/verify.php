<?php

/* ======================================================== *\

** 			igloo backend manager - verify

** 		

** 	The igloo verify module helps to secure the program by

** enforcing an authenticated session. On a request, the 

** program will check the session in the database, ensure

** it exists and is valid, and if so, return that the request

** is valid. 

**

**	This module also has an internal mode, that is used to 

** verify that individual requests are attached to a valid

** session.

\* ======================================================== */



// Not an access point

if ( ! defined ( 'igloo' ) ) {

	echo ( 'Not a script access point.' );

	exit ();

}



// Verify GET request.

	// Check for user actions.

$internal = true;

if ( ( ( isset ( $checkSession ) == true ) || ( isset ( $_GET ['verify'] ) ) ) && ( ( isset ( $checkUser ) == true ) || ( isset ( $_GET ['user'] ) ) ) ) {

	if ( ! isset ( $checkSession ) ) {

		$checkSession = mysql_real_escape_string ( $_GET['verify'] );

		$internal = false;

	} else { $checkSession = mysql_real_escape_string ( $checkSession ); }

	if ( ! isset ( $checkUser ) ) {

		$checkUser = mysql_real_escape_string ( $_GET['user'] );

		$internal = false;

	} else { $checkUser = mysql_real_escape_string ( $checkUser ); }

	if ( isset ( $_GET ['keep-alive'] ) ) {

		$keepalive = $_GET ['keep-alive'];

	} else { $keepalive = 'false'; }

} else {

	echo ( 'Missing required parameters.' );

	exit ();

}



if ( ! $internal ) header ( 'Content-type: application/x-javascript' );



// Handle authentication

$q = mysql_query ( "SELECT * FROM permissions WHERE username='$checkUser'" ) or die ( 'MySQL Error verify 0' );

$n = mysql_num_rows ( $q );

if ( $n === 0 ) { $flags = ''; } else { $d = mysql_fetch_array ( $q ); $flags = $d['flags']; }



if ( $iglooAuthMode == 'whitelist' ) {

	$q = mysql_query( "SELECT * FROM whitelist WHERE username='$checkUser'" ) or die ( 'MySQL Error s0' );

	$n = mysql_num_rows($q);

	if ($n == 0) {

		if ( ! $internal ) echo ( 'var iglooSessionVerified = \'failed\';' );

		$verified = false;

		$skip = true;

	}

}/* elseif ( $iglooAuthMode == 'blacklist' ) {

	$q = mysql_query ( "SELECT * FROM blacklist WHERE username='$checkUser'" ) or die ( 'MySQL Error s0' );

	$n = mysql_num_rows ( $q );

	if ( $n > 0 ) {

		if ( ! $internal ) echo ( 'var iglooSessionVerified = \'failed\';' );

		$verified = false;

		$skip = true;

	}

}*/



// Look up the session key in the database.

if ( $skip != true )	 {

	$q = mysql_query("SELECT * FROM sessions WHERE session_key='$checkSession' AND username='$checkUser' AND active='2'") or die(mysql_error());

	$n = mysql_num_rows($q);

	

	if ($n > 0) {

		// check the timing

		$d = mysql_fetch_array($q);

		

		if ( ( ( mktime() - $d['timestamp_touched'] ) < ( $timeout * 60 ) ) || ( $keepalive == 'true' ) ) {

			if ( $keepalive == 'true' ) {

				$time = mktime ();

				$q = mysql_query( "UPDATE sessions SET timestamp_touched='$time' WHERE username='$checkUser' AND active='2'" );

			}

			

			if ( ! $internal ) echo ( 'var iglooSessionVerified = \'ok\';' );

			$verified = true;

		} else {

			$q = mysql_query( "UPDATE sessions SET active='0' WHERE session_key='$checkSession' AND username='$checkUser' AND active='2'" ) or die ( 'error' );

			if ( ! $internal ) echo ( 'var iglooSessionVerified = \'failed\';' );

			$verified = false;

		}

	} else {

		if (!$internal) echo ('var iglooSessionVerified = \'failed\';');

		$verified = false;

	}

}



?>