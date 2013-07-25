<?php

/* ======================================================== *\

** 			igloo backend manager - database

** 		

** 	The igloo manager handles the requests from the JavaScript

** 	 program. The db file handles only connection information.

\* ======================================================== */



// Not an access point

if ( ! defined ( 'igloo' ) ) {

	echo ( 'e' );

	exit ();

}



	$dbHostname		= 'radian.cluenet.org';

	$dbUsername		= 'igloo';

	$dbPassword		= 'phEqesTe6etheC';

	$dbDatabase		= 'igloo';

	

	$link = mysql_connect ( $dbHostname, $dbUsername, $dbPassword ) or die ( mysql_error () );

	mysql_select_db ( $dbDatabase );

	

	unset ( $dbHostname, $dbUsername, $dbPassword );



?>
