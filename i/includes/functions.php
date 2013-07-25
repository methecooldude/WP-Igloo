<?php
/*
** ===================================================================================== **
** This page contains the code and functions for handling bot related tasks, sorting 
** pages and users based on wiki data, and misc. other bot functions.
\* ===================================================================================== */

function score_user ( $user, $me ) {
	$lookup_user = rawurlencode ( $user );
	$user_details = get_user_info ( $lookup_user );
	if ( $user_details === false ) return false;
	
	if ( ( strpos ( $user_details['groups'], 'bureaucrat' ) !== false ) || ( strpos ( $user_details['groups'], 'sysop' ) !== false ) || ( strpos ( $user_details['groups'], 'bot' ) !== false ) ) {
		mark_user ( $user, 0, $me );
		
	} elseif ( ( strpos ( $user_details['groups'], 'rollbacker' ) !== false ) || ( $user_details['editcount'] >= 1000 ) ) {
		mark_user ( $user, 0.1, $me );
		
	} elseif ( $user_details['editcount'] >= 250 ) {
		mark_user ( $user, 0.2, $me );
		
	} elseif ( $user_details['editcount'] >= 100 ) {
		mark_user ( $user, 0.3, $me );
		
	} 
	
	return $user_details;
}

function get_user_info ( $user ) {
	if ( preg_match ( '/[0-9]+\.[0-9]+\.[0-9]+\.?[0-9]*/', $user ) == 1 ) {
		return false; // ip
	}
	else
	{
		$data = file_get_contents ( 'http://en.wikipedia.org/w/api.php?format=php&action=query&list=users&usprop=blockinfo|groups|editcount&ususers=' . $user );
		$data = unserialize ( $data );
		
		$r['name'] 																= $data['query']['users']['0']['name'];
		$r['type']																= 'user';
		$r['editcount'] 														= $data['query']['users']['0']['editcount'];
		if (isset($data['query']['users']['0']['blockedby'])) { $r['blocked'] 	= true; } else { $r['blocked'] = false; }
		
		$r['groups'] = '';
		if (isset($data['query']['users']['0']['groups'])) {
			foreach ($data['query']['users']['0']['groups'] as $group) {
				$r['groups'] .= $group.',';
			}
		}
		
		/*if (strpos($tasks, 'warnings') !== false) {
			// NB - regex only detects date if sign-format is used on same line (i.e. with no linebreaks between warning and date) as warning.
			$reg		= '/<!-- *(?:template:)?uw-([a-zA-Z]+?)([0-9]+?) *(?:.{0,100})-->(?:.*([0-9]{2}:[0-9]{2}, ?[0-9]{1,2} ?(?:january|february|march|april|may|june|july|august|september|october|november|december) *(?:[0-9]{1,4})))?/i';
			
			preg_match_all($reg, $r['usertalk'], $warnings, PREG_SET_ORDER);
			
			$r['warnings']['count'] 																	= count($warnings);
			if (isset($warnings[count($warnings) - 1])) { $r['warnings']['mostrecent']['type'] 			= $warnings[count($warnings) - 1][1]; } else 
										{ $r['warnings']['mostrecent']['type'] = ''; }
			if (isset($warnings[count($warnings) - 1])) { $r['warnings']['mostrecent']['sev'] 			= $warnings[count($warnings) - 1][2]; } else 
										{ $r['warnings']['mostrecent']['sev'] = 0; }
			if (isset($warnings[count($warnings) - 1][2])) { $r['warnings']['mostrecent']['time'] 		= $warnings[count($warnings) - 1][3]; } else 
										{ $r['warnings']['mostrecent']['time'] = ''; }
			if (isset($warnings[count($warnings) - 1][2])) { $r['warnings']['mostrecent']['timesince'] 	= mktime() - strtotime($warnings[count($warnings) - 1][3]); } else
										{ $r['warnings']['mostrecent']['timesince'] = 0; }
		}
		else
		{
			$r['warnings'] = '';
		}*/
	}
	
	return $r;
}

function mark_user ( $user, $score, $me ) {
	$q = mysql_query ( "SELECT * FROM users WHERE title='$user' AND type='u'" ) or die ( 'MySQL Error func 1' );
	$n = mysql_num_rows( $q );
	$now = mktime ();
	
	if ( $n === 0 ) {
		$q = mysql_query ( "INSERT INTO users (title, type, details_score, lastupdate, lasttouched, expires) VALUES('$user', 'u', '$score', '$now', '$me', 0)" ) or die ( 'MySQL Error func 2' );
	} else {
		$q = mysql_query ( "UPDATE users SET details_score='$score', lastupdate='$now', lasttouched='$me', expires=0 WHERE title='$user'" ) or die ( 'MySQL Error func 3' );
	}
	
	return $user;
}

?>