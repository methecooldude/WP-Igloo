<?php
/* ======================================================== *\
** 			igloo backend manager - settings
** 		
** 	The igloo settings module retrieves the settings for
** igloo users, and allows them to be streamed to the 
** local client.
**
**	It also allows the updating of settings, provided that
** a valid session key is present.
\* ======================================================== */

// Not an access point
if ( ! defined ( 'igloo' ) ) {
	echo ( 'Not a script access point &lt;sett&gt;.' );
	exit ();
}

// Verify GET request.
	// Check for user actions.
if ( ( isset ( $_GET['session'] ) ) && ( isset ( $_GET['me'] ) ) ) {
	$session = $_GET['session'];
	$user = mysql_real_escape_string ( $_GET['me'] ); $neuser = $_GET['me'];
	if ( isset ( $_GET['do'] ) ) { $action = $_GET['do']; } else { $action = 'default'; }
} else {
	echo ( 'Missing required parameters.' );
	exit ();
}

// Verify the session
	$checkSession 	= $session;
	$checkUser 		= $neuser;
	require_once	( 'verify.php' );
	
	header ( 'Content-type: application/x-javascript' );
	if ( $verified === false ) {
		echo ( 'var iglooSettingsDone = \'failed\';' );
		exit ();
	}

// Session validated - send settings
	switch ( $action ) {
		case 'default': default: case 'get':
			$q = @mysql_query ( "SELECT * FROM settings WHERE user='$user'" ) or die ( 'MySQL Error settings 1' );
			$n = mysql_num_rows ( $q );
			if ( $n > 0 ) {
				$set = mysql_fetch_assoc ( $q );
				$o = 'var iglooNetSettings = {};';
				
				// first, output all settings the user has stored in the database
				foreach ( $set as $k => $i ) {					
					switch ( $k ) {
						case 'displayLastConnect':
							if ( $i !== NULL ) break;
							$q = @mysql_query ( "SELECT * FROM sessions WHERE username='$user' ORDER BY id DESC LIMIT 1,1" ) or die ( 'MySQL Error settings 2' );
							$d = mysql_fetch_assoc ( $q );
							$q = @mysql_query ( "SELECT * FROM sessions WHERE username='$user'" ) or die ( 'MySQL Error settings 2a' );
							$n = mysql_num_rows ( $q );
							
							$currenttime = mktime ();
							$lasttime = $d ['timestamp_touched'];
							$timediff = $currenttime - $lasttime;
							$days = floor ( $timediff / 86400 ); $hours = floor ( ( $timediff % 86400 ) / 3600 );
							
							$o .= 'iglooNetSettings[\'lastConnectIp\'] = \'' . $d ['ip'] . '\';';
							$o .= 'iglooNetSettings[\'lastConnectTime\'] = \'' . $days . ' days, ' . $hours . ' hours ago\';';
							$o .= 'iglooNetSettings[\'totalSessions\'] = ' . $n . ';';
							
							break;
						
						
						default:
							if ( $i == NULL ) break;
						
							if ( ( is_numeric ( $i ) ) || ( $i === 'true' ) || ( $i === 'false' ) ) {
								$o .= 'iglooNetSettings[\'' . $k . '\'] = ' . $i . ';';
							} else {
								$o .= 'iglooNetSettings[\'' . $k . '\'] = \'' . $i . '\';';
							}
							break;
					}
				}
				
				// second, output any user data that we need to send to the client.
				$q = @mysql_query ( "SELECT * FROM permissions WHERE username='$user'" ) or die ( 'MySQL Error settings 3' );
				$n = mysql_num_rows ( $q );
				if ( $n > 0 ) {
					$d = mysql_fetch_array ( $q );
					$o .= 'iglooNetSettings[\'iglooFlags\'] = \'' . $d['flags'] . '\';';
				}
				
				// third, send the relevant filters to the user
				$q = @mysql_query ( "SELECT * FROM filters WHERE username='$user' OR username='default'" ) or die ( 'MySQL Error settings 8' );
				$i = 0;
				
				while ( ( $d = mysql_fetch_array ( $q ) ) !== false ) {
					$d['filter'] = str_replace ( "\r\n", '\n', $d['filter'] );
					$d['filter'] = str_replace ( "\n", '\n', $d['filter'] );
					
					if ( $set['disabledFilters'] === NULL ) $set['disabledFilters'] = '';
					if ( strpos ( $set['disabledFilters'], $d['code'] ) === false ) { $enabled = 'true'; } else { $enabled = 'false'; }
					
					$o .= 'iglooSettings [\'filterList\']['.$i.'] = [];';
					$o .= 'iglooSettings [\'filterList\']['.$i.'][0] = \''.$d['code'].'\';'; // code
					$o .= 'iglooSettings [\'filterList\']['.$i.'][1] = '.$enabled.';'; // enabled
					$o .= 'iglooSettings [\'filterList\']['.$i.'][2] = "'.$d['username'].'";'; // whose?
					$o .= 'iglooSettings [\'filterList\']['.$i.'][3] = "'.$d['filter'].'";'; // filtertext
					
					$i ++;
				}
				
				
				// finally, close the connection
				$o .= 'var iglooNetSettingsDone = \'ok\';';
				echo ( $o );
			} else {
				$q = @mysql_query ( "INSERT INTO settings ( user ) VALUES ( '$user' )" ) or die ( 'MySQL Error settings 4' );
				$o = 'var iglooNetSettings = {}; var iglooNetSettingsDone = \'ok\';';
				echo ( $o );
			}
			break;
			
		case 'set':
			if ( ( ! isset ( $_GET['setting'] ) ) || ( ! isset ( $_GET['value'] ) ) ) { echo ( 'throw \'igloo: miscommunication with igloo server, could not alter user settings\';' ); break; }
			$setting = mysql_real_escape_string ( $_GET ['setting'] ); $value = mysql_real_escape_string ( $_GET ['value'] );
			$q = mysql_query ( "SELECT * FROM settings WHERE user='$user'" ) or die ( 'MySQL Error settings 5' );
			$d = mysql_fetch_array ( $q );
			$n = mysql_num_rows ( $q );
			
			switch ( $setting ) {
				case 'disablefilter':
					$t = $d['disabledFilters'];
					if ( $t === NULL ) $t = '';
					$t = split ( '::', $t );
					if ( in_array ( $value, $t ) === true ) return false;
					
					for ( $i = 0; $i < count ( $t ); $i ++ ) {
						if ( $t[$i] == false ) array_splice ( $t, $i, 1 );
					}
					
					$t[] = $value;
					
					$s = '::';
					for ( $i = 0; $i < count ( $t ); $i ++ ) {
						if ( $t[$i] == false ) continue;
						$s .= $t[$i] . '::';
					}
					$q = @mysql_query ( "UPDATE settings SET disabledFilters='$s' WHERE user='$user' LIMIT 1" ) or die ( 'MySQL Error settings 8' );
					
					break;
					
				case 'enablefilter':
					$t = $d['disabledFilters'];
					if ( $t === NULL ) $t = '';
					$t = split ( '::', $t );
					if ( in_array ( $value, $t ) === false ) return false;
					
					for ( $i = 0; $i < count ( $t ); $i ++ ) {
						if ( $t[$i] == false ) array_splice ( $t, $i, 1 );
					}
					
					$c = array_search ( $value, $t );
					array_splice ( $t, $c, 1 );
					
					$s = '::';
					for ( $i = 0; $i < count ( $t ); $i ++ ) {
						if ( $t[$i] == false ) continue;
						$s .= $t[$i] . '::';
					}
					$q = @mysql_query ( "UPDATE settings SET disabledFilters='$s' WHERE user='$user' LIMIT 1" ) or die ( 'MySQL Error settings 9' );
					
					break;
					
				case 'editfilter':
					$id = mysql_real_escape_string ( $_GET ['id'] );
					$q = @mysql_query ( "SELECT * FROM filters WHERE code='$id'" ) or die ( 'MySQL Error settings 11' );
					$n = mysql_num_rows ( $q );
					if ( $n > 0 ) {
						$d = mysql_fetch_array ( $q );
						if ( $d['username'] == $user ) {
							$q = @mysql_query ( "UPDATE filters SET filter='$value' WHERE code='$id' LIMIT 1" ) or die ( 'MySQL Error settings 11' );
						}
					} else {
						// new filter
						$q = @mysql_query ( "INSERT INTO filters (code, username, filter) VALUES ('$id','$user','$value')" ) or die ( 'MySQL Error settings 12' );
					}
					
					break;
					
				case 'deletefilter':
					$q = @mysql_query ( "SELECT * FROM filters WHERE code='$value'" ) or die ( 'MySQL Error settings 10' );
					$n = mysql_num_rows ( $q );
					if ( $n > 0 ) {
						$d = mysql_fetch_array ( $q );
						if ( $d['username'] == $user ) {
							$q = @mysql_query ( "DELETE FROM filters WHERE code='$value' LIMIT 1" ) or die ( 'MySQL Error settings 11' );
						}
					}
					
					break;
				
				default:
					if ( ( $value == 'null' ) || ( $value == 'NULL' ) ) $value = NULL;
					if ( $n > 0 ) {
						$q = mysql_query ( "UPDATE settings SET $setting = '$value' WHERE user = '$user'" ) or die ( 'MySQL Error settings 6' );
					} else {
						$q = mysql_query ( "INSERT INTO settings ( user, $setting ) VALUES ( '$user', '$value' )" ) or die ( 'MySQL Error settings 7' );
					}
					echo ( 'igloo.iglooManageSettings.freeSettings ();' );
					break;
			}
		
			break;
	}

?>