<?php
/**
 * GIT DEPLOYMENT SCRIPT
 * Used for automatically deploying websites via github or bitbucket, more deets here:
 *		https://gist.github.com/1809044
 */

global $ip_address;
$ip_address = FALSE;

function ip_address()
{
	global $ip_address;
	
	if ($ip_address !== FALSE)
	{
		return $ip_address;
	}
	
	if (empty($REMOTE_ADDR)) {
		if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
			$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		}
		else if (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
			$REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
		}
		else if (@getenv('REMOTE_ADDR')) {
			$REMOTE_ADDR = getenv('REMOTE_ADDR');
		}
	}
	if (empty($HTTP_X_FORWARDED_FOR)) {
		if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
			$HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
		}
		else if (@getenv('HTTP_X_FORWARDED_FOR')) {
			$HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');
		}
	}
	if (empty($HTTP_X_FORWARDED)) {
		if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
			$HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
		}
		else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
			$HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
		}
		else if (@getenv('HTTP_X_FORWARDED')) {
			$HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');
		}
	}
	if (empty($HTTP_FORWARDED_FOR)) {
		if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
			$HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
		}
		else if (@getenv('HTTP_FORWARDED_FOR')) {
			$HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');
		}
	}
	if (empty($HTTP_FORWARDED)) {
		if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
			$HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
		}
		else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
			$HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
		}
		else if (@getenv('HTTP_FORWARDED')) {
			$HTTP_FORWARDED = getenv('HTTP_FORWARDED');
		}
	}
	if (empty($HTTP_VIA)) {
		if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
			$HTTP_VIA = $_SERVER['HTTP_VIA'];
		}
		else if (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
			$HTTP_VIA = $_ENV['HTTP_VIA'];
		}
		else if (@getenv('HTTP_VIA')) {
			$HTTP_VIA = getenv('HTTP_VIA');
		}
	}
	if (empty($HTTP_X_COMING_FROM)) {
		if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
			$HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
		}
		else if (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
			$HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
		}
		else if (@getenv('HTTP_X_COMING_FROM')) {
			$HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');
		}
	}
	if (empty($HTTP_COMING_FROM)) {
		if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
			$HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
		}
		else if (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
			$HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
		}
		else if (@getenv('HTTP_COMING_FROM')) {
			$HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');
		}
	}

	// Gets the default ip sent by the user //

	if (!empty($REMOTE_ADDR)) {
		$direct_ip = $REMOTE_ADDR;
	}

	// Gets the proxy ip sent by the user //

	$proxy_ip     = '';
	if (!empty($HTTP_X_FORWARDED_FOR)) {
		$proxy_ip = $HTTP_X_FORWARDED_FOR;
	} else if (!empty($HTTP_X_FORWARDED)) {
		$proxy_ip = $HTTP_X_FORWARDED;
	} else if (!empty($HTTP_FORWARDED_FOR)) {
		$proxy_ip = $HTTP_FORWARDED_FOR;
	} else if (!empty($HTTP_FORWARDED)) {
		$proxy_ip = $HTTP_FORWARDED;
	} else if (!empty($HTTP_VIA)) {
		$proxy_ip = $HTTP_VIA;
	} else if (!empty($HTTP_X_COMING_FROM)) {
		$proxy_ip = $HTTP_X_COMING_FROM;
	} else if (!empty($HTTP_COMING_FROM)) {
		$proxy_ip = $HTTP_COMING_FROM;
	}

	// Returns the true IP if it has been found, else FALSE //

	if (empty($proxy_ip)) {
		// True IP without proxy
		$return_ip = $direct_ip;
	} else {
		$is_ip = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $proxy_ip, $regs);
		if ($is_ip && (count($regs) > 0)) {
			// True IP behind a proxy
			$return_ip = $regs[0];
		} else {
			// Can't define IP: there is a proxy but we don't have
			// information about the true IP
			$return_ip = FALSE;
		}
	}
	
	return ( $ip_address = $return_ip );
}

function send_mail($from, $to, $subject, $body)
{
	if(empty($to)) return ;
	
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/plain; charset=utf-8';
	$headers[] = 'Content-Transfer-Encoding: 7bit';
	$headers[] = "From: {$from}";
	$headers[] = 'Reply-To: no-reply@glonly.tw';
	$headers[] = 'X-Mailer: PHP/' . phpversion();
	$headers[] = 'X-Originating-IP: ' . $_SERVER['SERVER_ADDR'];
	
	$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
	
	@mail($to, $subject, $body, implode("\r\n", $headers));
}

require('ip_in_range.php');

ip_address();
$ip_valid = FALSE;
$valid_ip_ranges = array(
	'207.97.227.253/32',
	'50.57.128.197/32',
	'108.171.174.178/32',
	'50.57.231.61/32',
	'204.232.175.64/27',
	'192.30.252.0/22');

foreach( $valid_ip_ranges as $index => $ip_rangs ){
	if( ip_in_range($ip_address, $ip_rangs) ){
		$ip_valid = TRUE;
		break;
	}
}

if( $ip_valid === FALSE ){
	exit();
}

// The commands
$commands = array(
	'echo $PWD',
	'whoami'
);

$payload = json_decode($_POST['payload']);
$ref = explode('/', $payload->ref);
$branch = end($ref);

switch($branch){
	case 'stage':
		$commands[] = '/var/www/scripts/stage_pull.sh 2>&1';
		break;
	case 'production':
		$commands[] = '/var/www/scripts/production_pull.sh 2>&1';
		break;
	default:
		exit();
		break;
}
$branch = strtoupper($branch);

// Run the commands for output
$output = '';
foreach($commands AS $index => $command){
	// Run it
	$tmp = shell_exec($command);
	$tmp = trim($tmp);
	// Output
	if($index < 2){
		$output .= "\$ {$command}: {$tmp}\r\n---------\r\n";
	}else{
		$output .= "\$ {$command}:\r\n{$tmp}\r\n---------\r\n";
	}
}

send_mail('GLonly Auto Deploy <auto-deploy@glonly.tw>', $_SERVER['SERVER_ADMIN'], "[GLonly][{$branch}] Auto deploy info.", $output);