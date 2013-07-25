<?php
/* ======================================================== *\
** 			igloo backend manager - igloonet
** 		
** 	The igloonet tool allows external programs to influence
** the server based iglooNet. This means that bots can 
** communicate with the software.
\* ======================================================== */

// Verify GET request.
	// Check for user actions.
if ( isset($_GET['key']) ) {
	$key = $_GET['key'];
	$action = $_GET['action'];
	$target = $_GET['target'];
} else {
	echo ('Missing required parameters.');
	exit ();
}

// Verify the session
	if ( ($key != 'oq238bricwqugeiruqyb') || ($_SERVER['REMOTE_ADDR'] != '82.43.146.127') ) { echo ('Missing required parameters.'); exit (); }
	
// database settings
        $dbHostname             = 'radian.cluenet.org';
        $dbUsername             = 'igloo';
        $dbPassword             = 'phEqesTe6etheC';
        $link = mysql_connect($dbHostname, $dbUsername, $dbPassword) or die('e');
        mysql_set_charset('utf8');
        mysql_select_db('igloo');

//	$dbHostname		= 'localhost';
//	$dbUsername		= 'alejrb_igloo';
//	$dbPassword		= '!o2fZt47298od%xutbi#4';
//	$link = mysql_connect($dbHostname, $dbUsername, $dbPassword) or die('e');
//	mysql_set_charset('utf8');
//	mysql_select_db('alejrb_wikipedia');

	
// Begin main actions.
	$now = mktime();
	
	// work out the expiry time. The following rules generally hold true for iglooNet expiration:
	//	- beneficial or trusted editors will expire if they aren't seen by iglooNet for over 2 weeks. This helps save space in the cache.
	//	- vandal editors will expire if they aren't warned or reverted for 8 days.
	//	- blocked editos will expire 1 week after their block expires.
	//	- flagged pages will expire after 6 hours.
	//	- whitelisted pages will never expire.
	
	$q = mysql_query("DELETE FROM users WHERE expires < $now AND expires != 0") or die(mysql_error());

	switch ($action) {
		case 'whitelist':
			$q = mysql_query("SELECT * FROM users WHERE title='$target' AND type='p'") or die(mysql_error());
			if (mysql_num_rows($q) <= 0) {
				$q = mysql_query("INSERT INTO users (title, type, details_flags, lastupdate) VALUES('$target', 'p', 'w', '$now')") or die(mysql_error());
			}
			break;
			
		case 'group':
			$q = mysql_query("SELECT * FROM users WHERE title='$target' AND type='u'") or die(mysql_error());
			$n = mysql_num_rows($q);
			if ( ($_GET['group'] == 'bureaucrat') || ($_GET['group'] == 'administrator') || ($_GET['group'] == 'bot') ) { $score = 0; }
			elseif ($_GET['group'] == 'rollbacker') { $score = 0.1; }
			elseif ($_GET['group'] == 'supertrusted') { $score = 0.2; }
			elseif ($_GET['group'] == 'trusted') { $score = 0.3; }
						
			if ($n <= 0) {
				$q = mysql_query("INSERT INTO users (title, type, details_score, lastupdate, expires) VALUES('$target', 'u', '$score', '$now', 0)") or die(mysql_error());
			} else {
				$d = mysql_fetch_array($q);
				if ($d['details_score'] > $score) {
					$q = mysql_query("UPDATE users SET details_score='$score', lastupdate='$now', expires=0 WHERE title='$target'") or die(mysql_error());
				}
			}
			break;
			
		case 'warned':
			$expires = $now + (8 * 24 * 60 * 60);
		
			$q = mysql_query("SELECT * FROM users WHERE title='$target' AND type='u'") or die(mysql_error());
			$n = mysql_num_rows($q);
			if ($n > 0) {
				$d = mysql_fetch_array($q);
				
				if ($d['details_score'] <= 0.2) { exit(); } 
				elseif ($d['details_score'] <= 0.4) { $score = 0.7; }
				else { $score = 0.9; }
				
				$q = mysql_query("UPDATE users SET details_score='$score', lastupdate='$now', expires='$expires' WHERE title='$target'") or die(mysql_error());
			} else {
				$q = mysql_query("INSERT INTO users (title, type, details_score, lastupdate, expires) VALUES('$target', 'u', '0.9', '$now', '$expires')") or die(mysql_error());
			}
			
			break;
	}

?>
