<?php

/* ======================================================== *\

** 			igloo backend manager - settings

** 		

** 	The igloo settings file contains general server settings. 

\* ======================================================== */



// Not an access point

if ( ! defined ('igloo') ) {

	include 'tracker.php';

	exit ();

}



	$trackingEnabled 		= false; // not currently relevant

	$iglooEnabled			= true;

	$refererSecurity		= array('wikipedia', 'localhost');

	$timeout				= 10;

	

	$iglooAbuseLimit		= 0.1;

	

	$iglooAuthMode			= 'blacklist';

	

	



?>