<?php
/**
 * This file is called on header.php and checks the database to see
 * if it up to date with the current software version.
 *
 * In case you are updating from an old one, the new values, columns
 * and rows will be created, and a message will appear under the menu
 * one time only.
 *
 * @package		ProjectSend
 * @subpackage	Updates
 */

$allowed_update = array(9,8,7);
if (in_session_or_cookies($allowed_update)) {

	/** Remove "r" from version */
	$current_version = substr(CURRENT_VERSION, 1);
	$updates_made = 0;
	$updates_errors = 0;
	$updates_error_messages = array();
	
	/**
	 * Check for updates only if the option exists.
	 */
	if (defined('VERSION_LAST_CHECK')) {
		/**
		 * Compare the date for the last checked with
		 * today's. Checks are done only once per day.
		 */
		 $today = date('d-m-Y');
		 $today_timestamp = strtotime($today);
		 if (VERSION_LAST_CHECK != $today) {
			if (VERSION_NEW_FOUND == '0') {
				/**
				 * Compare against the online value.
				 */
				$feed = simplexml_load_file(UPDATES_FEED_URI);
				$v = 0;
				$max_items = 1;
				foreach ($feed->channel->item as $item) {
					while ($v < $max_items) {
						$namespaces = $item->getNameSpaces(true);
						$release = $item->children($namespaces['release']);
						$diff = $item->children($namespaces['diff']);
						$online_version = substr($release->version, 1);

						 if ($online_version > $current_version) {
							/**
							 * The values are set here since they didn't
							 * come from the database.
							 */
							define('VERSION_NEW_NUMBER',$online_version);
							define('VERSION_NEW_URL',$item->link);
							define('VERSION_NEW_CHLOG',$release->changelog);
							define('VERSION_NEW_SECURITY',$diff->security);
							define('VERSION_NEW_FEATURES',$diff->features);
							define('VERSION_NEW_IMPORTANT',$diff->important);
							/**
							 * Save the information from the new release
							 * to the database.
							 */
							$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :version WHERE name='version_new_number'");		$statement->bindParam(':version', $release->version); $statement->execute();
							$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :link WHERE name='version_new_url'");				$statement->bindParam(':link', $item->link); $statement->execute();
							$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :changelog WHERE name='version_new_chlog'");		$statement->bindParam(':changelog', $release->changelog); $statement->execute();
							$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :security WHERE name='version_new_security'");		$statement->bindParam(':security', $diff->security); $statement->execute();
							$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :features WHERE name='version_new_features'");		$statement->bindParam(':features', $diff->features); $statement->execute();
							$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :important WHERE name='version_new_important'");	$statement->bindParam(':important', $diff->important); $statement->execute();
							$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value ='1' WHERE name='version_new_found'");
						 }
						 else {
							 reset_update_status();
						 }

						/**
						 * Change the date and versions values on the
						 * database so it's not checked again today.
						 */
						$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :today WHERE name='version_last_check'");
						$statement->bindParam(':today', $today);
						$statement->execute();

						/** Stop the foreach loop */
						$v++;
					}
				}
			 }
		 }
	}

	/**
	 * r264 updates
	 * Save the value of the last update on the database, to prevent
	 * running all this queries everytime a page is loaded.
	 * Done on top for convenience.
	 */
	$statement = $dbh->prepare("SELECT value FROM " . TABLE_OPTIONS . " WHERE name = 'last_update'");
	$statement->execute();
	if ( $statement->rowCount() == 0 ) {
		$dbh->query( "INSERT INTO " . TABLE_OPTIONS . " (name, value) VALUES ('last_update', '264')" );
		$updates_made++;
	}
	else {
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		while ( $row = $statement->fetch() ) {
			$last_update = $row['value'];
		}
	}
	
	if ($last_update < $current_version || !isset($last_update)) {

		/**
		 * r92 updates
		 * The logo file name is now stored on the database.
		 * If the row doesn't exist, create it and add the default value.
		 */
		if ($last_update < 92) {
			$new_database_values = array(
											'logo_filename' => 'logo.png'
										);
			
			foreach ($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r94 updates
		 * A new column was added on the clients table, to store the value of the
		 * user that created it.
		 * If the column doesn't exist, create it.
		 */
		 /** DEPRECATED
		 	table tbl_clients doesn't exist anymore
		if ($last_update < 94) {
			$statement = $dbh->prepare("SELECT created_by FROM tbl_clients");
			$statement->execute();
	
			if( $statement->rowCount() == 0 ) {
				$statement = $dbh->query("ALTER TABLE tbl_clients ADD created_by VARCHAR(".MAX_USER_CHARS.") NOT NULL");
				$updates_made++;
			}
		}
		*/

		/**
		 * DEPRECATED
		 * r102 updates
		 * A function was added to hide or show uploaded files from the clients lists.
		 * If the "hidden" column on the files table doesn't exist, create it.
		 */
		/*
		$statement = $dbh->query("SELECT hidden FROM " . TABLE_FILES);
		if ( $statement->rowCount() == 0 ) {
			$statement = $dbh->query("ALTER TABLE " . TABLE_FILES . " ADD hidden INT(1) NOT NULL");
			$updates_made++;
		}
		*/

		/**
		 * r135 updates
		 * The e-mail address used for notifications to new users, clients and files
		 * can now be defined on the options page. When installing or updating, it
		 * will default to the primary admin user's e-mail.
		 */
		if ($last_update < 135) {
			$statement = $dbh->query("SELECT * FROM " . TABLE_USERS . " WHERE id = '1'");
	
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			while ( $row = $statement->fetch() ) {
				$set_admin_email = $row['email'];
			}
		
			$new_database_values = array(
											'admin_email_address' => $set_admin_email
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r183 updates
		 * A new column was added on the clients table, to store the value of the
		 * account active status.
		 * If the column doesn't exist, create it. Also, mark every existing
		 * client as active (1).
		 */
		if ($last_update < 183) {
		 /** DEPRECATED
		 	table tbl_clients doesn't exist anymore

			$q = $database->query("SELECT active FROM tbl_clients");
			if (!$q) {
				mysql_query("ALTER TABLE tbl_clients ADD active tinyint(1) NOT NULL");
				$sql = $database->query('SELECT * FROM tbl_clients');
				while($row = mysql_fetch_array($sql)) {
					$database->query('UPDATE tbl_clients SET active = 1');
				}
				$updates_made++;
			}
		*/		
			/**
			 * Add the "users can register" value to the options table.
			 * Defaults to 0, since this is a new feature.
			 * */
			$new_database_values = array(
											'clients_can_register' => '0'
										);
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r189 updates
		 * Move every uploaded file to a neutral location
		 */
		if ($last_update < 189) {
			$work_folder = ROOT_DIR.'/upload/';
			$folders = glob($work_folder."*", GLOB_NOSORT);
		
			foreach ($folders as $folder) {
				if(is_dir($folder) && !stristr($folder,'/temp') && !stristr($folder,'/files')) {
					$files = glob($folder.'/*', GLOB_NOSORT);
					foreach ($files as $file) {
						if(is_file($file) && !stristr($file,'index.php')) {
							$filename = basename($file);
							$mark_for_moving[$filename] = $file;
						}
					}
				}
			}
			$work_folder = UPLOADED_FILES_FOLDER;
			if (!empty($mark_for_moving)) {
				foreach ($mark_for_moving as $filename => $path) {
					$new = UPLOADED_FILES_FOLDER.'/'.$filename;
					$try_moving = rename($path, $new);
					chmod($new, 0644);
				}
			}
		}

		/**
		 * r202 updates
		 * Combine clients and users on the same table.
		 */
		if ($last_update < 202) {
			try {
				$statement = $dbh->query("SELECT created_by FROM " . TABLE_USERS);
			} catch( PDOException $e ) {
				/* Mark existing users as active */
				$dbh->query("ALTER TABLE " . TABLE_USERS . " ADD address TEXT NULL, ADD phone varchar(32) NULL, ADD notify TINYINT(1) NOT NULL default='0', ADD contact TEXT NULL, ADD created_by varchar(32) NULL, ADD active TINYINT(1) NOT NULL default='1'");
				$dbh->query("INSERT INTO " . TABLE_USERS
										." (user, password, name, email, timestamp, address, phone, notify, contact, created_by, active, level)"
										." SELECT client_user, password, name, email, timestamp, address, phone, notify, contact, created_by, active, '0' FROM tbl_clients");
				$dbh->query("UPDATE " . TABLE_USERS . " SET active = 1");
				$updates_made++;
			}
		}

		/**
		 * r210 updates
		 * A new database table was added, that allows the creation of clients groups.
		 */
		if ($last_update < 210) {
			if ( !tableExists( TABLE_GROUPS ) ) {
				/** Create the GROUPS table */
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_GROUPS."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `created_by` varchar(32) NOT NULL,
				  `name` varchar(32) NOT NULL,
				  `description` text NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
		
				/**
				 * r215 updates
				 * Change the engine of every table to InnoDB, to use foreign keys on the 
				 * groups feature.
				 * Included inside the previous update since that is not an officially
				 * released version.
				 */
				foreach ($current_tables as $working_table) {
					$statement = $dbh->prepare("ALTER TABLE $working_table ENGINE = InnoDB");
					$statement->execute();

					$updates_made++;
				}
			}
		}

		/**
		 * r219 updates
		 * A new database table was added.
		 * Folders are related to clients or groups.
		 */
		if ($last_update < 219) {
			if ( !tableExists( TABLE_FOLDERS ) ) {
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_FOLDERS."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `parent` int(11) DEFAULT NULL,
				  `name` varchar(32) NOT NULL,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `client_id` int(11) DEFAULT NULL,
				  `group_id` int(11) DEFAULT NULL,
				  FOREIGN KEY (`parent`) REFERENCES ".TABLE_FOLDERS."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`client_id`) REFERENCES ".TABLE_USERS."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`group_id`) REFERENCES ".TABLE_GROUPS."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}

		/**
		 * r217 updates (after previous so the folder column can be created)
		 * A new database table was added, to facilitate the relation of files
		 * with clients and groups.
		 */
		if ($last_update < 217) {
			if ( !tableExists( TABLE_FILES_RELATIONS ) ) {
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_FILES_RELATIONS."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `file_id` int(11) NOT NULL,
				  `client_id` int(11) DEFAULT NULL,
				  `group_id` int(11) DEFAULT NULL,
				  `folder_id` int(11) DEFAULT NULL,
				  `hidden` int(1) NOT NULL,
				  `download_count` int(16) NOT NULL default '0',
				  FOREIGN KEY (`file_id`) REFERENCES ".TABLE_FILES."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`client_id`) REFERENCES ".TABLE_USERS."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`group_id`) REFERENCES ".TABLE_GROUPS."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`folder_id`) REFERENCES ".TABLE_FOLDERS."(`id`) ON UPDATE CASCADE,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}

		/**
		 * r241 updates
		 * A new database table was added, that stores users and clients actions.
		 */
		if ($last_update < 241) {
			if ( !tableExists( TABLE_LOG ) ) {
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_LOG."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `action` int(2) NOT NULL,
				  `owner_id` int(11) NOT NULL,
				  `owner_user` text DEFAULT NULL,
				  `affected_file` int(11) DEFAULT NULL,
				  `affected_account` int(11) DEFAULT NULL,
				  `affected_file_name` text DEFAULT NULL,
				  `affected_account_name` text DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}
		
		/**
		 * r266 updates
		 * Set timestamp columns as real timestamp data, instead of INT
		 */
		if ($last_update < 266) {
			$statement = $dbh->query("ALTER TABLE `" . TABLE_USERS . "` ADD COLUMN `timestamp2` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()");
			$statement = $dbh->query("UPDATE `" . TABLE_USERS . "` SET `timestamp2` = FROM_UNIXTIME(`timestamp`)");
			$statement = $dbh->query("ALTER TABLE `" . TABLE_USERS . "` DROP COLUMN `timestamp`");
			$statement = $dbh->query("ALTER TABLE `" . TABLE_USERS . "` CHANGE `timestamp2` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()");

			$updates_made++;
		}

		/**
		 * r275 updates
		 * A new database table was added.
		 * It stores the new files-to clients relations to be
		 * used on notifications.
		 */
		if ($last_update < 275) {
			if ( !tableExists( TABLE_NOTIFICATIONS ) ) {
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_NOTIFICATIONS."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `file_id` int(11) NOT NULL,
				  `client_id` int(11) NOT NULL,
				  `upload_type` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}

		/**
		 * r278 updates
		 * Set timestamp columns as real timestamp data, instead of INT
		 */
		if ($last_update < 278) {
			$statement = $dbh->query("ALTER TABLE `" . TABLE_FILES . "` ADD COLUMN `timestamp2` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()");
			$statement = $dbh->query("UPDATE `" . TABLE_FILES . "` SET `timestamp2` = FROM_UNIXTIME(`timestamp`)");
			$statement = $dbh->query("ALTER TABLE `" . TABLE_FILES . "` DROP COLUMN `timestamp`");
			$statement = $dbh->query("ALTER TABLE `" . TABLE_FILES . "` CHANGE `timestamp2` `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP()");

			$updates_made++;
		}


		/**
		 * r282 updates
		 * Add new options to select the handler for sending emails.
		 */
		if ($last_update < 282) {
			$new_database_values = array(
											'mail_system_use' => 'mail',
											'mail_smtp_host' => '',
											'mail_smtp_port' => '',
											'mail_smtp_user' => '',
											'mail_smtp_pass' => '',
											'mail_from_name' => THIS_INSTALL_SET_TITLE
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r338 updates
		 * The Members table wasn't being created on existing installations.
		 */
		if ($last_update < 338) {
			if ( !tableExists( TABLE_MEMBERS ) ) {
				/** Create the MEMBERS table */
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_MEMBERS."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `added_by` varchar(32) NOT NULL,
				  `client_id` int(11) NOT NULL,
				  `group_id` int(11) NOT NULL,
				  PRIMARY KEY (`id`),
				  FOREIGN KEY (`client_id`) REFERENCES ".TABLE_USERS."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`group_id`) REFERENCES ".TABLE_GROUPS."(`id`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}

		/**
		 * r346 updates
		 * chmod the cache folder and main files of timthumb to 775
		 */
		if ($last_update < 346) {
			update_chmod_timthumb();
		}

		/**
		 * r348 updates
		 * chmod the emails folder and files to 777
		 */
		if ($last_update < 348) {
			update_chmod_emails();
		}

		/**
		 * r352 updates
		 * chmod the main system files to 644
		 */
		if ($last_update < 352) {
			chmod_main_files();
		}

		/**
		 * r353 updates
		 * Create a new option to let the user decide wheter to
		 * use the relative or absolute file url when generating
		 * thumbnails with timthumb.php
		 */
		if ($last_update < 353) {
			$new_database_values = array(
											'thumbnails_use_absolute' => '0'
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r354 updates
		 * Import the files relations (up until r335 it was
		 * only one-to-one with clients) into the new database
		 * table. This should have been done before r335 release.
		 * Sorry :(
		 */
		if ($last_update < 354) {
			import_files_relations();
		}


		/**
		 * r358 updates
		 * New columns where added to the notifications table, to
		 * store values about the state of it.
		 * If the columns don't exist, create them.
		 */
		if ($last_update < 358) {
			try {
				$statement = $dbh->query("SELECT sent_status FROM " . TABLE_NOTIFICATIONS);
			} catch( PDOException $e ) {
				$statement = $dbh->query("ALTER TABLE " . TABLE_NOTIFICATIONS . " ADD sent_status INT(2) NOT NULL");
				$statement = $dbh->query("ALTER TABLE " . TABLE_NOTIFICATIONS . " ADD times_failed INT(11) NOT NULL");
				$updates_made++;
			}
		}


		/**
		 * r364 updates
		 * Add new options to send copies of notifications emails
		 * to the specified addresses.
		 */
		if ($last_update < 364) {
			$new_database_values = array(
											'mail_copy_user_upload' => '',
											'mail_copy_client_upload' => '',
											'mail_copy_main_user' => '',
											'mail_copy_addresses' => ''
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/** Update the database */
		$statement = $dbh->prepare("UPDATE " . TABLE_OPTIONS . " SET value = :version WHERE name='last_update'");
		$statement->bindParam(':version', $current_version);
		$statement->execute();

		/** Record the action log */
		$new_log_action = new LogActions();
		$log_action_args = array(
								'action' => 30,
								'owner_id' => $global_id,
								'affected_account_name' => $current_version
							);
		$new_record_action = $new_log_action->log_action_save($log_action_args);


		/**
		 * r377 updates
		 * Add new options to store the last time the system checked
		 * for a new version.
		 */
		$today = date('d-m-Y');
		if ($last_update < 377) {
			$new_database_values = array(
											'version_last_check'	=> $today,
											'version_new_found'		=> '0',
											'version_new_number'	=> '',
											'version_new_url'		=> '',
											'version_new_chlog'		=> '',
											'version_new_security'	=> '',
											'version_new_features'	=> '',
											'version_new_important'	=> ''
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}


		/**
		 * r386 / r412 updates
		 * Add new options to handle actions related to clients
		 * self registrations.
		 */
		if ($last_update < 412) {
			$new_database_values = array(
											'clients_auto_approve'	=> '0',
											'clients_auto_group'	=> '0',
											'clients_can_upload'	=> '1'
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}


		/**
		 * r419 updates
		 * Add new options to customize the emails sent by the system.
		 */
		if ($last_update < 419) {
			$new_database_values = array(
										/**
										 * On or Off fields
										 * Each one corresponding to a type of email
										 */
											'email_new_file_by_user_customize'		=> '0',
											'email_new_file_by_client_customize'	=> '0',
											'email_new_client_by_user_customize'	=> '0',
											'email_new_client_by_self_customize'	=> '0',
											'email_new_user_customize'				=> '0',
										/**
										 * Text fields
										 * Each one corresponding to a type of email
										 */
											'email_new_file_by_user_text'			=> '',
											'email_new_file_by_client_text'			=> '',
											'email_new_client_by_user_text'			=> '',
											'email_new_client_by_self_text'			=> '',
											'email_new_user_text'					=> ''
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r426 updates
		 * Add new options to customize the header and footer of emails.
		 */
		if ($last_update < 426) {
			$new_database_values = array(
										'email_header_footer_customize'		=> '0',
										'email_header_text'					=> '',
										'email_footer_text'					=> '',
									);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r442 updates
		 * Add new options to customize the header and footer of emails.
		 */
		if ($last_update < 442) {
			$new_database_values = array(
										'email_pass_reset_customize'		=> '0',
										'email_pass_reset_text'				=> '',
									);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r464 updates
		 * New columns where added to the files table, to
		 * set expiry dates and download limit.
		 * Also, set a new option to hide or show expired
		 * files to clients.
		 */
		if ($last_update < 464) {
			try {
				$statement = $dbh->query("SELECT expires FROM " . TABLE_FILES);
			} catch( PDOException $e ) {
				$statement = $dbh->query("ALTER TABLE " . TABLE_FILES . " ADD expires INT(1) NOT NULL default '0'");
				$statement = $dbh->query("ALTER TABLE " . TABLE_FILES . " ADD expiry_date TIMESTAMP NOT NULL");
				$updates_made++;
			}

			$new_database_values = array(
										'expired_files_hide'		=> '1',
									);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}


		/**
		 * r474 updates
		 * A new database table was added.
		 * Each download will now be saved here, to distinguish
		 * individual downloads even if the origin is a group.
		 */
		if ($last_update < 474 ) {
			if ( !tableExists( TABLE_DOWNLOADS ) ) {
				$query = "
				CREATE TABLE IF NOT EXISTS `" . TABLE_DOWNLOADS . "` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user_id` int(11) DEFAULT NULL,
				  `file_id` int(11) NOT NULL,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  FOREIGN KEY (`user_id`) REFERENCES " . TABLE_USERS . "(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`file_id`) REFERENCES " . TABLE_FILES . "(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}


		/**
		 * r475 updates
		 * New columns where added to the files table, to
		 * allow public downloads via a token.
		 */
		if ($last_update < 475) {
			try {
				$statement = $dbh->query("SELECT public_allow FROM " . TABLE_FILES);
			} catch( PDOException $e ) {
				$sql1 = $dbh->query("ALTER TABLE " . TABLE_FILES . " ADD public_allow INT(1) NOT NULL default '0'");
				$sql2 = $dbh->query("ALTER TABLE " . TABLE_FILES . " ADD public_token varchar(32) NULL");
				$updates_made++;
			}
		}


		/**
		 * r487 updates
		 * Add new options to limit the retries of notifications emails
		 * and also set an expiry date.
		 */
		if ($last_update < 487) {
			$new_database_values = array(
											'notifications_max_tries'	=> '2',
											'notifications_max_days'	=> '15',
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}


		/**
		 * r490 updates
		 * Set foreign keys to update the notifications table automatically.
		 * Rows that references deleted users or files will be deleted
		 * before adding the keys.
		 */
		if ($last_update < 490) {
			$statement = $dbh->query("DELETE FROM " . TABLE_NOTIFICATIONS . " WHERE file_id NOT IN (SELECT id FROM " . TABLE_FILES . ")");
			$statement = $dbh->query("DELETE FROM " . TABLE_NOTIFICATIONS . " WHERE client_id NOT IN (SELECT id FROM " . TABLE_USERS . ")");
			$statement = $dbh->query("ALTER TABLE " . TABLE_NOTIFICATIONS . " ADD FOREIGN KEY (`file_id`) REFERENCES " . TABLE_FILES . "(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
			$statement = $dbh->query("ALTER TABLE " . TABLE_NOTIFICATIONS . " ADD FOREIGN KEY (`client_id`) REFERENCES " . TABLE_USERS . "(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
			$updates_made++;
		}


		/**
		 * r501 updates
		 * Migrate the download count on each client to the new table.
		 */
		if ($last_update < 501) {
			$statement = $dbh->query("SELECT * FROM " . TABLE_FILES_RELATIONS . " WHERE client_id IS NOT NULL AND download_count > 0");
			if( $statement->rowCount() > 0 ) {
				$downloads = $statement->fetchAll(PDO::FETCH_ASSOC);
				foreach ( $downloads as $key => $row ) {
					$download_count	= $row['download_count'];
					$client_id		= $row['client_id'];
					$file_id		= $row['file_id'];

					for ($i = 0; $i < $download_count; $i++) {
						$statement = $dbh->prepare("INSERT INTO " . TABLE_DOWNLOADS . " (file_id, user_id) VALUES (:file_id, :client_id)");
						$statement->bindParam(':file_id', $file_id, PDO::PARAM_INT);
						$statement->bindParam(':client_id', $client_id, PDO::PARAM_INT);
						$statement->execute();
					}
				}
				$updates_made++;
			}
		}


		/**
		 * r528 updates
		 * Add new options for email security, file types limits and
		 * requirements for passwords.
		 * and also set an expiry date.
		 */
		if ($last_update < 528) {
			$new_database_values = array(
											'file_types_limit_to'	=> 'all',
											'pass_require_upper'	=> '0',
											'pass_require_lower'	=> '0',
											'pass_require_number'	=> '0',
											'pass_require_special'	=> '0',
											'mail_smtp_auth'		=> 'none'
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}



		/**
		 * r557 updates
		 * Change the database collations
		 */
		if ($last_update < 557) {
			$alter = array();			
			$statement = $dbh->exec('ALTER DATABASE ' . DB_NAME . ' CHARACTER SET utf8 COLLATE utf8_general_ci');
			$statement = $dbh->query('SET foreign_key_checks = 0');
			$statement = $dbh->query('SHOW TABLES');
			$tables = $statement->fetchAll(PDO::FETCH_COLUMN);
			foreach ( $tables as $key => $table ) {
				$alter[$key] = $table;
			}
			foreach ( $alter as $key => $value ) {
				$statement = $dbh->prepare("ALTER TABLE $value DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
				$statement->execute();
			}
			$statement = $dbh->query('SET foreign_key_checks = 1');

			$updates_made++;
		}



		/**
		 * r572 updates
		 * No DB changes
		 */
		if ($last_update < 572) {
			$updates_made++;
		}

		/**
		 * r582 updates
		 * No DB changes
		 */
		if ($last_update < 582) {
			$updates_made++;
		}


		/**
		 * r645 updates
		 * Added an option to use the browser language instead of
		 * the one on the config file.
		 */
		if ($last_update < 645) {
			$new_database_values = array(
											'use_browser_lang'	=> '0',
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r672 updates
		 * Added an option to allow clients to delete their own uploads
		 */
		if ($last_update < 672) {
			$new_database_values = array(
											'clients_can_delete_own_files'	=> '0',
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}

		/**
		 * r674 updates
		 * Add the Google Sign in options to the database
		 */
		if ($last_update < 674) {
			$new_database_values = array(
											'google_client_id'		=> '',
											'google_client_secret'	=> '',
											'google_signin_enabled'	=> '0',
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}


		/**
		 * r678 updates
		 * A new database table was added.
		 * Files categories.
		 */
		if ($last_update < 678) {
			if ( !tableExists( TABLE_CATEGORIES ) ) {
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_CATEGORIES."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(32) NOT NULL,
				  `parent` int(11) DEFAULT NULL,
				  `description` text NULL,
				  `created_by` varchar(".MAX_USER_CHARS.") NULL,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  FOREIGN KEY (`parent`) REFERENCES ".TABLE_CATEGORIES."(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}

		/**
		 * r680 updates
		 * A new database table was added.
		 * Relates files categories to files.
		 */
		if ($last_update < 680) {
			if ( !tableExists( TABLE_CATEGORIES_RELATIONS ) ) {
				$query = "
				CREATE TABLE IF NOT EXISTS `".TABLE_CATEGORIES_RELATIONS."` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
				  `file_id` int(11) NOT NULL,
				  `cat_id` int(11) NOT NULL,
				  FOREIGN KEY (`file_id`) REFERENCES ".TABLE_FILES."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  FOREIGN KEY (`cat_id`) REFERENCES ".TABLE_CATEGORIES."(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
				";
				$statement = $dbh->prepare($query);
				$statement->execute();

				$updates_made++;
			}
		}


		/**
		 * r737 updates
		 * Add the reCAPTCHA options to the database
		 */
		if ($last_update < 737) {
			$new_database_values = array(
											'recaptcha_enabled'		=> '0',
											'recaptcha_site_key'	=> '',
											'recaptcha_secret_key'	=> '',
										);
			
			foreach($new_database_values as $row => $value) {
				if ( add_option_if_not_exists($row, $value) ) {
					$updates_made++;
				}
			}
		}


		/**
		 * r738 updates
		 * New columns where added to the downloads table, to
		 * store the ip and hostname of the user, and a boolean
		 * fieled set to true for anonymous downloads (public files)
		 */
		if ($last_update < 738) {
			try {
				$statement = $dbh->query("SELECT remote_ip FROM " . TABLE_DOWNLOADS);
			} catch( PDOException $e ) {
				$statement = $dbh->query("ALTER TABLE " . TABLE_DOWNLOADS . " ADD remote_ip varchar(45) NULL");
				$statement = $dbh->query("ALTER TABLE " . TABLE_DOWNLOADS . " ADD remote_host text NULL");
				$statement = $dbh->query("ALTER TABLE " . TABLE_DOWNLOADS . " ADD anonymous tinyint(1) NULL");
				$updates_made++;
			}
		}

	}
}	
?>