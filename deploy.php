<?php
/**
 * GIT DEPLOYMENT SCRIPT
 * Used for automatically deploying websites via github or bitbucket, more deets here:
 *		https://gist.github.com/1809044
 */

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
// The commands
$commands = array(
	'echo $PWD',
	'whoami',
	'/var/www/scripts/stage_pull.sh'
);

// Run the commands for output
$output = '';
foreach($commands AS $command){
	// Run it
	$tmp = shell_exec($command);
	$tmp = htmlentities(trim($tmp));
	// Output
	$output = "\${$command}:\r\n{$tmp}\r\n---------\r\n";
}

send_mail('auto-deploy@glonly.tw', $_SERVER['SERVER_ADMIN'], 'Git deploymeny info.', $output);