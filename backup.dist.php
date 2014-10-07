<?php

/*

The following must be configured before running the script.

*/

define('awsAccessKey', ''); // required
define('awsSecretKey', ''); // required
define('awsBucket', ''); // required

// Will this script run "weekly", "daily", or "hourly"?
define('schedule','daily'); // required

$mailAddress = ''; // mail address to send notification to
$siteName = ''; // site name, used for mail subject

require_once('include/backup.inc.php');

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
$succesDB = backupDB('localhost','username','password','databasename','my-database-backup','');

/*

backupFiles - array of paths, [prefix]

  array of paths = An array of one or more file paths that you want backed up
  prefix = Optional: backup filenames will contain this prefix, this prevents overwriting other backups when you have more than one server backing up at once.

*/
$successFiles = backupFiles(array('/home/myuser', '/etc'),'me');

if ($successDB && $successFiles) {
		 $to = mailAddress;
		 $subject = "[$siteName] - Weekly off-site backup successful";
		 $body = <<<BODY
The $siteName backup just ran successfully and has been uploaded to S3.

You can rest easy.

--
"The Server"
BODY;
		mail($to, $subject, $body);
	}
?>
