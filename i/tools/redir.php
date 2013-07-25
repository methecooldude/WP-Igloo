<?php
/* ======================================================== *\
** 			igloo backend manager - redir
** 		
** 	The redir tool redirects requests to my home computer,
** but only if it's me. This protects my IP address and so 
** on.
\* ======================================================== */

// Verify the session
	if ( isset ( $_GET['key'] ) ) { $key = $_GET['key']; } else { $key = ''; }
	if ( ( $key != 'sknrejfgcsbkjhref' ) || ( $_SERVER['REMOTE_ADDR'] != '82.43.146.127' ) ) { echo ( 'Missing required parameters.' ); exit (); }
	
// Begin main actions.
	header ( 'Cache-Control: no-cache, must-revalidate' ); // HTTP/1.1
	header ( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
	header ( 'Content-type: application/x-javascript' );
		$text = file_get_contents ( 'http://82.43.146.127/Wikipedia/igloo/client/iglooMain.js' );
		echo ( $text );
		exit ();

?>