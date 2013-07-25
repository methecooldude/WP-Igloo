<?php

/* ======================================================== *\

** 			igloo backend manager - tracker

** 		

** 	The igloo tracker handles monitoring and logging of 

** 	 connections, to help with debugging and counter-abuse.

\* ======================================================== */



// Not an access point

if ( (!defined ('igloo')) || ($unexpected_request == true) ) {

	usleep(5000000);

	echo ( 'Not a script access point.' );// Your attempt, and your IP address <strong>'.$_SERVER['REMOTE_ADDR'].'</strong>, have been logged.');

}

	$user_ip = 'IP discarded';//do not store $_SERVER['REMOTE_ADDR'];

/*
	if ($trackingEnabled == true) {

		mysql_select_db('alejrb_logs');

		$timestamp = mktime();

		

		$server_vars = '';

		$server_vars .= 'REMOTE_ADDR' . ' => ' . $user_ip . '; '."\n";

		$server_vars .= 'HTTP_USER_AGENT' . ' => ' . $_SERVER['HTTP_USER_AGENT'] . '; '."\n";

		$server_vars .= 'HTTP_REFERER' . ' => ' . $_SERVER['HTTP_REFERER'] . '; '."\n";

		

		$request_vars = '';

		while ( 1 ) {

			$current = current ( $_GET );

			if ( $current === false ) break;

			

			$request_vars .= key ( $_GET ) . ' => ' . $current . '; '."\n";

			next ( $_GET );

		}

		

		$q = mysql_query("INSERT INTO logs(timestamp, server_vars, request_vars) VALUES ('$timestamp','$server_vars','$request_vars')") or die('MySQL Error');

		mysql_select_db($dbDatabase);

	}*/

	

if (!defined ('igloo')) {

	exit();

}



?>