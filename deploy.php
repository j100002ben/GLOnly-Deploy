<?php
/**
 * GIT DEPLOYMENT SCRIPT
 * Used for automatically deploying websites via github or bitbucket, more deets here:
 *		https://gist.github.com/1809044
 */

/**
* Fetch the IP Address
* From Codeigniter 2.1.3
*
* @return	string
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

	$proxy_ips = config_item('proxy_ips');
	if ( ! empty($proxy_ips))
	{
		$proxy_ips = explode(',', str_replace(' ', '', $proxy_ips));
		foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header)
		{
			if (($spoof = $this->server($header)) !== FALSE)
			{
				// Some proxies typically list the whole chain of IP
				// addresses through which the client has reached us.
				// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
				if (strpos($spoof, ',') !== FALSE)
				{
					$spoof = explode(',', $spoof, 2);
					$spoof = $spoof[0];
				}

				if ( ! $this->valid_ip($spoof))
				{
					$spoof = FALSE;
				}
				else
				{
					break;
				}
			}
		}

		$ip_address = ($spoof !== FALSE && in_array($_SERVER['REMOTE_ADDR'], $proxy_ips, TRUE))
			? $spoof : $_SERVER['REMOTE_ADDR'];
	}
	else
	{
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}

	return $ip_address;
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
	'whoami',
	'/var/www/scripts/stage_pull.sh 2>&1'
);

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

if( !empty($_POST['payload']) ){
	$output .= $_POST['payload'];
}

send_mail('auto-deploy@glonly.tw', $_SERVER['SERVER_ADMIN'], 'Git deploymeny info.', $output);