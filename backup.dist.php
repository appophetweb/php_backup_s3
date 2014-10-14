<?php

/*

The following must be configured before running the script.

*/

define('awsAccessKey', ''); // required
define('awsSecretKey', ''); // required
define('awsBucket', ''); // required

// Will this script run "weekly", "daily", or "hourly"?
define('schedule',''); // required

// mailing defines
define('sendGridUser', '');
define('sendGridPassword', '');
define('mailAddress', ''); // mail address to send notification to
define('siteName', ''); // site name, used for mail subject

require_once('include/backup.inc.php');
require_once("sendgrid-php/sendgrid-php.php");

// You may place any number of .php files in the backups folder. They will be executed here.
foreach (glob(dirname(__FILE__) . "/backups/*.php") as $filename)
{
	include $filename;
}

/*

backupDB - hostname, username, password, databasename, prefix, [post backup query]

	hostname = hostname of your MySQL server
	username = username to access your MySQL server (make sure the user has SELECT privliges)
	password = your password
	databasename = your database name
	prefix = backup filenames will contain this prefix, this prevents overwriting other backups when you have more than one server backing up at once.
	post backup query = Optional: Any SQL statement you want to execute after the backups are completed. For example: PURGE BINARY LOGS BEFORE NOW() - INTERVAL 14 DAY;

*/
$successDB = backupDB('','','','','','');

/*

backupFiles - array of paths, [prefix]

	array of paths = An array of one or more file paths that you want backed up
	prefix = Optional: backup filenames will contain this prefix, this prevents overwriting other backups when you have more than one server backing up at once.

*/
$successFiles = backupFiles(array(''),'');

// if the backup is successful, send a mail via sendgrid
if ($successDB && $successFiles) {
	$sendgrid = new SendGrid(sendGridUser, sendGridPassword);
	$email = new SendGrid\Email();

	$email->addTo(mailAddress)->
		setFrom('')->
		setFromName(siteName . ' Mailer')->
		setSubject(siteName . ' - Weekly off-site backup successful')->
		setText('The ' . siteName . ' backup just ran successfully and has been uploaded to S3. You can rest easy. -- The Server')->
		setHtml('The ' . siteName . ' backup just ran successfully and has been uploaded to S3.<br><br>You can rest easy.<br><br>--<br>The Server');

	$sendgrid->send($email);
}
?>
