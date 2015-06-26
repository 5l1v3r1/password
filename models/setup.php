<?php
	class setup_model extends model {
		/* Determine next step
		 */
		public function step_to_take() {
			if ($this->db->connected == false) {
				$db = new MySQLi_connection(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
			} else { 
				$db = $this->db;
			}

			if ($db->connected == false) {
				/* No database connection
				 */
				if ((DB_HOSTNAME == "localhost") && (DB_DATABASE == "banshee") && (DB_USERNAME == "banshee") && (DB_PASSWORD == "banshee")) {
					return "db_settings";
				}

				return "create_db";
			} else {
				/* Database connection
				 */
				$result = $db->execute("show tables like %s", "settings");
				if (count($result) == 0) {
					return "import_tables";
				}

				return "done";
			}
		}

		/* Remove datase related error messages
		 */
		public function remove_database_errors() {
			$errors = explode("\n", rtrim(ob_get_contents()));
			ob_clean();

			foreach ($errors as $error) {
				if (strtolower(substr($error, 0, 14)) != "mysqli_connect") {
					print $error;
				}
			}
		}

		/* Create the MySQL database
		 */
		public function create_database($username, $password) {
			$db = new MySQLi_connection(DB_HOSTNAME, "mysql", $username, $password);

			if ($db->connected == false) {
				$this->output->add_message("Error connecting to database.");
				return false;
			}

			$db->query("begin");

			/* Create database
			 */
			$query = "create database if not exists %S character set utf8";
			if ($db->query($query, DB_DATABASE) == false) {
				$db->query("rollback");
				$this->output->add_message("Error creating database.");
				return false;
			}

			/* Create user
			 */
			$query = "select count(*) as count from user where User=%s";
			if (($users = $db->execute($query, DB_USERNAME)) === false) {
				$db->query("rollback");
				$this->output->add_message("Error checking for user.");
				return false;
			}

			if ($users[0]["count"] == 0) {
				$query = "create user %s@%s identified by %s";
				if ($db->query($query, DB_USERNAME, DB_HOSTNAME, DB_PASSWORD) == false) {
					$db->query("rollback");
					$this->output->add_message("Error creating user.");
					return false;
				}
			} else {
				$login_test = new MySQLi_connection(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
				if ($login_test->connected == false) {
					$db->query("rollback");
					$this->output->add_message("Invalid credentials in settings/website.conf.");
					return false;
				}
			}

			/* Set access rights
			 */
			$rights = array(
				"select", "insert", "update", "delete",
				"create", "drop", "alter", "index", "lock tables",
				"create view", "show view");

			$query = "grant ".implode(", ", $rights)." on %S.* to %s@%s";
			if ($db->query($query, DB_DATABASE, DB_USERNAME, DB_HOSTNAME) == false) {
				$db->query("rollback");
				$this->output->add_message("Error setting access rights.");
				return false;
			}

			/* Commit changes
			 */
			$db->query("commit");
			$db->query("flush privileges");
			unset($db);

			return true;
		}

		/* Import database tables from file
		 */
		public function import_tables() {
			system("mysql -u \"".DB_USERNAME."\" --password=\"".DB_PASSWORD."\" \"".DB_DATABASE."\" < ../database/mysql.sql", $result);
			if ($result != 0) {
				$this->output->add_message("Error while importing database tables.");
				return false;
			}

			$this->settings->secret_website_code = random_string();

			$username = "admin";
			$password = "banshee";

			$aes = new AES256($password);
			$crypto_key = base64_encode($aes->encrypt(random_string(32)));
			$password = hash_password($password, $username);

			$query = "update users set password=%s, crypto_key=%s, status=%d where username=%s";
			if ($this->db->query($query, $password, $crypto_key, USER_STATUS_CHANGEPWD, $username) === false) {
				$this->output->add_message("Database error while setting password.");
				return false;
			}

			return true;
		}
	}
?>
