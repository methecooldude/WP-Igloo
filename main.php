<?php
/* ======================================================== *\
** 			igloo backend manager - main
** 		
** 	The igloo manager handles the requests from the JavaScript
** 	 program. It looks up a user in the database, returns
** 	 their levels, and modifies where necessary.
\* ======================================================== */

// Script access point
define('igloo', true);

// Perform general imports.
	require 'i/includes/db.php';
	require 'i/includes/settings.php';
	require 'i/includes/functions.php';
	require 'i/includes/tracker.php'; // tracking disabled
	//require 'includes/dbFunctions.php';

// Spoof useragent for API calls
	$useragent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8';
	ini_set	( 'user_agent', $useragent );
	
// Limit caching in most browsers.
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Determine module to load.
switch ( $_GET['action'] ) {
	case 'connect':
		require 'i/modules/security.php';
		break;
		
	case 'verify':
		require 'i/modules/verify.php';
		break;

	case 'retrieve':
		require 'i/modules/retrieve.php';
		break;
		
	case 'settings':
		require 'i/modules/settings.php';
		break;
		
	case 'markgood': case 'markbad': case 'mark': case 'investigate':
		require 'i/modules/mark.php';
		break;
		
	default:
		$unexpected_request = true;
		require_once 'i/includes/tracker.php';
		exit ();
		break;
}

?>