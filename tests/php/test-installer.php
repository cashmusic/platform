<?php
/**
 * CASH Music Test Installer
 *
 * Takes the CASH DIY repo and sets it up  as a working platform instance so
 * tests can be run against it.
 *
 * USAGE:
 * php tests/php/test-installer.php
 * 
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
*/

if(!defined('STDIN')) { // force CLI, the browser is *so* 2007...
	echo "Please run installer from the command line. usage:<br / >&gt; php tests/php/test-installer.php";
} else {
	require_once(dirname(__FILE__) .'/../../tests/php/functions.php');
	
	function findReplaceInFile($filename,$find,$replace) {
		if (is_file($filename)) {
			$file = file_get_contents($filename);
			$file = str_replace($find, $replace, $file);
			if (file_put_contents($filename, $file)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	$success = false;

 echo "\n                       \n"
    . "                        WELCOME TO R'LYEH\n"
   . "MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMMMMMMWKO0KXO0KKOKKXWMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMMN0doxcoK0xkO0OKXMWkONMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMNKdoxollok:,ol::clod;kKMX;xNMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMKo,;lOl.,llco,,..:o,c;,:dMMNll0MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMKx::dOoc',.,Okokl'...'::l:;oWXONWdXMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMXKdlxo:c.;'. .'dooc;o,;..:::;kWl,:kk:KMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMXlccc..',.:.'.. .;,okxxddll.,:c, ..;'x.cdOKNMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMNOxllx::,...  .;.....:ooldOdko;..''..kxoXloc:oONMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMNo:c,oko:,:..l;c':';'.,c:xkodxkd,;,c,xMcxN0KOXd:cKMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMWO::.;';co',okkc,,'odOkdc,.';odo,xkcdk'xNlck;..,dcklklXMMMMMMMMMMMMMMMMMM\n"
   . "MMMMNkc:'l:l,:;.c.;c..'dxldKkxx:'..dl:oo:dlxkl..:d0XK0xocxXMMMMMMMMMMMMMMMMMMM\n"
   . "MMMWo;';:',::,. ;o. .'OMMMMKc;'::' ..'.:ool,;;..'oWMMMW:.';okMMMMMMMMMMXddNMMM\n"
   . "MMM0;c.:.'c;,,'',:; .'0MMMMMMNO:;'....  .....  'dWMMMMM;';:dWMMMMMMWWKol;dNMMM\n"
   . "MMO.;;'.,;,''';:cl:,,;'l0XWMMMWd'...  ....  ...'cNMMMM0.''olMMMMMWOllo:oKMMMMM\n"
   . "MMkx;''...,.,c.c:;,,dooxc,;,,. .:,. .;..  ;:. 'dxX0o''oxXMMMMXdxO:ckMMMMMMMMMM\n"
   . "MMMMWX0OWO;.'.;;,,:'.'''  . .  .;,; ,,..  o0.   .lxc:llXMMMMN;kl.OMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMKo',co:... .....  ,;;,. lc;'.'dK. ...,,ollNMMMMMOc0.lWMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMW0c,,cc.:'  .. .;'', ,OXc, cko ;l.;xx:.NMMMMMMXcOcXMMMMMMMMMMMMM\n"
   . "MMMMMMMMWkx0NMMMMMk l,'l. c; :xc:;.oW0o.:ok'cX0:K0x dMMMMMMMX'o:kMMMMMMMMMMMMM\n"
   . "MMMMMM0dlcolldxNMNl ';.:l.cx'clll,;xNx 'cx.cXN'dcoK.xMMMMMMMM:,olMMMMMMMMMMMMM\n"
   . "MMMMXd0kc.:dc,'';:;..x :l; c''lOWc'0X' ,o,;KW:.K'.kd,NMMMMMMMo.0;NMMMMMMMMMMMM\n"
   . "MMMMdoXx.kMMMNOlc:xd:d..dl..l;oKMx.OX..:l.cK0.,Xl .:c.xWMMMMN':d KMMMMMMMMMMMM\n"
   . "MMMMl:x0.oWMMMMMKk:,l:'.;c:.cc,kMK.:Nc'cc.,lO.'co'. ,lcoxlc;'dO,cMMMMMMMMMMMMM\n"
   . "MMMMK'co:..dxOkc,'l:'.. .dc.lk'lkXo'kXl:',.:c,' ,odddo;ox0xoo:'0WMMMMMMMMMMMMM\n"
   . "MMMMMk.,kxl;;l;ldl;:':KX:l,.co.,xOX:.dX0;,,.,odddl,.:xXl.,..,xNMMMMMMMMMMMMMMM\n"
   . "MMMMMMMO,...,ccldk0KWMMN.l',oc.. cNx. :odo:okOc..  .  ,Oc0WKOOKWMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMNKKNMMMWKxol:c;;:,lc... 'OOl. . .  .:'.';    ;k.,xklxd0MMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMM0lc:';:lc:;,, .. l;..oKl. c, .. .''..,' .xo.NMMX;OdXMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMNOl:cc;.::okO0,.    ;,x'co,..,k;KNd'c0.  ,,cl,0MMMMx:d0MMMMMMMMMMMMM\n"
   . "MMMMMMMMM0:dxlxKNNMMMMWxx..:..c:c.cO;. .kloMX,.d:  'c.lXMMMMMco:KMMMMMMMMMMMMM\n"
   . "MMMMMMMMK:o,xWMMMMMMMK;oc'kN':,cKWoxc. .OcdMMo.dx .;.xWMMMMMxclOMMMMMMMMMMMMMM\n"
   . "MMMMMMMX:o.KMMMMMMMXl.,c.kN::ccNMWdl.  ,l.kMMk.c..: dMMMMMMX;o,NMMNMMMMMMMMMMM\n"
   . "MMMMMMMO:.'KMMMMM0;:od'xN0.ccxMMMKlc'  cl.XMN;': '; xMMMMMMX.l:':o'NMMMMMMMMMM\n"
   . "MMMMMMMNoc,cxkXWxcdc,;OMN:d;dMMMMddc'  kl:MMc,'cXkocXMMMMMMMNoxKxlOMMMMMMMMMMM\n"
   . "MMMMMMMMMWXKXNKc,,oXMMMk.d;cMMMMk:o;  ,k'NMk:c'NMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMX0OOd;..;XMMMMW';;:WMMM0:x;.. o:XMN':.0MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMNOcok0WMMMMMMMK.lcWMMM0loc.oW:ldMMX.;'WMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMo.,cMMWOlo' ,WN':xMMMo';XMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMM:',KWx;dl. :WMMO'dMMMMNMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMM:.'lc,l;.cOWMMMMNWMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMx.  :c..kWMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMM0..; :KMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMMMK,,OWMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMMMMNNMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
   . "MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMWMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n"
	. "                     C A S H  M U S I C\n"
	. "                   PLATFORM TEST INSTALLER\n";
	echo "\nPh'nglui mglw'nafh Cthulhu R'lyeh wgah'nagl fhtagn!\n\n";
	$db_engine = 'sqlite';
	$installer_root = dirname(__FILE__);

	if (getTestEnv("CASHMUSIC_DB_TYPE")) {
		$db_engine = getTestEnv("CASHMUSIC_DB_TYPE");
		if ($db_engine != 'mysql' && $db_engine != 'sqlite') {
			echo "\nOh. Shit. Something's wrong: first argument must be either 'sqlite' or 'mysql' \n\n";
			exit(1);
		}
		if ($db_engine == 'mysql') {
			$db_server    = getTestEnv("CASHMUSIC_DB_SERVER");
			$db_name      = getTestEnv("CASHMUSIC_DB_NAME");
			$db_username  = getTestEnv("CASHMUSIC_DB_USERNAME");
			$db_password  = getTestEnv("CASHMUSIC_DB_PASSWORD");
		}
	}

	if ($db_engine == 'mysql') {
		if (@new PDO()) {
			echo "\nOh. Shit. Something's wrong: PDO is required.\n\n";
			exit(1);
		}
		
		// set up database, add user / password
		$db_port = 3306;
		if (strpos($db_server,':') !== false) {
			$host_and_port = explode(':',$db_server);
			$db_address = $host_and_port[0];
			$db_port = $host_and_port[1];
		} else {
			$db_address = $db_server;
		}
		try {
			$pdo = new PDO ("mysql:host=$db_address;port=$db_port;dbname=$db_name",$db_username,$db_password);
			$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't connect to the database. $e\n\n";
			exit(1);
			break;
		}

		if ($pdo->query(file_get_contents(dirname(__FILE__) . '/../../framework/php/settings/sql/cashmusic_db.sql'))) {
			$success = true;
		} else {
			echo "\nOh. Shit. Something's wrong. Couldn't create database tables.\n\n";
			exit(1);
			break;
		}
	} else if ($db_engine == 'sqlite') {
		// if the file exists already, rename it as a backup
		if (file_exists($installer_root . '/../../framework/db/cashmusic_test.sqlite')) {
			rename($installer_root . '/../../framework/db/cashmusic_test.sqlite',$installer_root . '/../../framework/db/cashmusic_test.sqlite.pretest.bak');
		} else {
			// if the directory was never created then create it now
			if (!file_exists($installer_root . '/../../framework/db')) {
				mkdir($installer_root . '/../../framework/db');
			}
		}
		
		// connect to the new db...will create if not found
		try {
			$pdo = new PDO ('sqlite:' . $installer_root . '/../../framework/db/cashmusic_test.sqlite');
			$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			chmod($installer_root . '/../../framework/db/cashmusic_test.sqlite',0755);
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't connect to the database. $e\n\n";
			exit(1);
			break;
		}

		// push in all the tables
		// TO-DO:
		// what the fuck?
		// 
		// $pdo->query doesn't work here. fails and doesn't write to the DB. exec works like a charm
		// but for the MySQL version the behavior is reversed. should both (seemingly) work interchangeably
		// 
		// i don't fucking know.
		try {
			$pdo->exec(file_get_contents($installer_root . '/../../framework/php/settings/sql/cashmusic_db_sqlite.sql'));
			$success = true;
		} catch (PDOException $e) {
			echo "\nOh. Shit. Something's wrong: Couldn't write to the database. $e\n\n";
			exit(1);
			break;
		}
	}

	if ($success) {
		$user_email    = 'root@localhost';
		$system_salt   = md5($user_email . time());
		$user_password = 'hack_my_gibson';

		if (!defined('CRYPT_BLOWFISH')) define('CRYPT_BLOWFISH', 0);
		if (!defined('CRYPT_SHA512')) define('CRYPT_SHA512', 0);
		if (!defined('CRYPT_SHA256')) define('CRYPT_SHA256', 0);

		if (CRYPT_BLOWFISH + CRYPT_SHA512 + CRYPT_SHA256) {
			if (CRYPT_BLOWFISH == 1) {
				$password_hash = crypt(md5($user_password . $system_salt), '$2a$13$' . md5(time() . $system_salt) . '$');
			} else if (CRYPT_SHA512 == 1) {
				$password_hash = crypt(md5($user_password . $system_salt), '$6$rounds=6666$' . md5(time() . $system_salt) . '$');
			} else if (CRYPT_SHA256 == 1) {
				$password_hash = crypt(md5($user_password . $system_salt), '$5$rounds=6666$' . md5(time() . $system_salt) . '$');
			}
		} else {
			$key = time();
			$password_hash = $key . '$' . hash_hmac('sha256', md5($user_password . $system_salt), $key);
		}
	
		$data = array(
			'email_address' => $user_email,
			'password'      => $password_hash,
			'is_admin'      => true,
			'api_key'       => '42',
			'api_secret'    => '43',
			'creation_date' => time()
		);
		$query = "INSERT INTO people (email_address,password,is_admin,api_key,api_secret,creation_date) VALUES (:email_address,:password,:is_admin,:api_key,:api_secret,:creation_date)";
		
		try {  
			$q = $pdo->prepare($query);
			$success = $q->execute($data);
		} catch(PDOException $e) {  
			echo "\nOh. Shit. Something's wrong. Couldn't add the user to the database. $e\n\n";
			exit(1);
			break;
		}

		// modify settings files
		if (file_exists($installer_root . '/../../framework/php/settings/cashmusic.ini.php')) {
			rename($installer_root . '/../../framework/php/settings/cashmusic.ini.php',$installer_root . '/../../framework/php/settings/cashmusic.ini.pretest.bak');
		}
		if (
			!copy($installer_root.'/../../framework/php/settings/cashmusic_template.ini.php',$installer_root.'/../../framework/php/settings/cashmusic.ini.php')
		) {
			echo '\nOh. Shit. Something\'s wrong. Couldn\'t write the config file.\n\n'
			. 'the directory you specified for the framework.</p>';
			break;
		}

		// move source files into place
		$file_write_success = false;
		$test_url = getTestEnv("CASHMUSIC_TEST_URL");
		if (!$test_url) { $test_url = "http://dev.cashmusic.org:8080"; }
		if ($db_engine == 'sqlite') {
			if (
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','driver = "mysql','driver = "sqlite') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','debug = 0','debug = 1') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','database = "cashmusic','database = "cashmusic_test.sqlite') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','apilocation = "http://localhost:8888/interfaces/php/api/','apilocation = "'.$test_url.'/interfaces/php/api/') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $system_salt)
			) {
				$file_write_success = true;
			} 
		} else {
			if (
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','hostname = "127.0.0.1:8889','hostname = "' . $db_server) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','username = "root','username = "' . $db_username) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','password = "root','password = "' . $db_password) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','database = "cashmusic','database = "' . $db_name) &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','debug = 0','debug = 1') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','apilocation = "http://localhost:8888/interfaces/php/api/','apilocation = "'.$test_url.'/interfaces/php/api/') &&
				findReplaceInFile($installer_root.'/../../framework/php/settings/cashmusic.ini.php','salt = "I was born of sun beams; Warming up our limbs','salt = "' . $system_salt)
			) {
				$file_write_success = true;
			}
		}
		if (!$file_write_success) {
			echo "\nOh. Shit. Something's wrong. We had trouble editing a few files. Please try again.\n\n";
			break;
		} else {
			try {
				$pdo->exec(file_get_contents(dirname(__FILE__) . '/../../framework/php/settings/sql/cashmusic_demo_data.sql'));
				echo "\n" . strtoupper($db_engine) . " TEST DB DEPLOYED! Fear of testing is the mind-killer.\n";
			} catch (PDOException $e) { 
				echo "\nSOME SUCCESS, SOME FAILURE:\nEverything is set up properly, but there was an error writing demo data.\n$e\n";
			}
		}
	}
}
?>
