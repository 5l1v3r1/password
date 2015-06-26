<?php
	class register_model extends model {
		private $minimum_username_length = 4;
		private $minimum_password_length = 8;
		private $mimimum_fullname_length = 4;

		public function valid_signup($data) {
			$result = true;

			if ((strlen($data["username"]) < $this->minimum_username_length) || (valid_input($data["username"], VALIDATE_NONCAPITALS, VALIDATE_NONEMPTY) == false)) {
				$this->output->add_message("Your username must consist of lowercase letters with a mimimum length of %d.", $this->minimum_username_length);
				$result = false;
			}

			if (valid_email($data["email"]) == false) {
				$this->output->add_message("Invalid e-mail address.");
				$result = false;
			}

			if ($result == false) {
				return false;
			}

			if (strlen($data["password"]) < $this->minimum_password_length) {
				$this->output->add_message("The length of your password must be equal or greater than %d.", $this->minimum_password_length);
				$result = false;
			}

			if (strlen($data["fullname"]) < $this->mimimum_fullname_length) {
				$this->output->add_message("The length of your name must be equal or greater than %d.", $this->mimimum_fullname_length);
				$result = false;
			}

			$query = "select * from users where username=%s or email=%s";
			if (($users = $this->db->execute($query, $data["username"], $data["email"])) === false) {
				$this->output->add_message("Error while validating sign up.");
				return false;
			}

			foreach ($users as $user) {
				if ($user["username"] == $data["username"]) {
					$this->output->add_message("This username is already taken.");
					$result = false;
				}

				if ($data["email"] != "") {
					if ($user["email"] == $data["email"]) {
						$this->output->add_message("This e-mail address has already been used to register an account.");
						$result = false;
					}
				}
			}

			return $result;
		}

		private function get_signature($data) {
			$data["secret"] = $this->settings->secret_website_code;

			return hash("sha256", json_encode($data));
		}

		public function send_link($data) {
			$data = array(
				"username"  => $data["username"],
				"password"  => $data["password"],
				"email"     => strtolower($data["email"]),
				"fullname"  => $data["fullname"],
				"timestamp" => time());
			$data["signature"] = $this->get_signature($data);

			$link = json_encode($data);

			$aes = new AES256($this->settings->secret_website_code);
			if (($link = $aes->encrypt($link)) === false) {
				return false;
			}

			if (($link = base64_encode($link)) === false) {
				return false;
			}
			$link = strtr($link, "/+=", "_-:");


			$email = new email("Confirm account creation at ".$_SERVER["SERVER_NAME"], $this->settings->webmaster_email);
			$email->set_message_fields(array(
				"FULLNAME" => $data["fullname"],
				"HOSTNAME" => $_SERVER["SERVER_NAME"],
				"PROTOCOL" => $_SERVER["HTTP_SCHEME"],
				"LINK"     => $link));
			$email->message(file_get_contents("../extra/register.txt"));

			if ($email->send($data["email"], $data["fullname"]) == false) {
				return false;
			}

			return true;
		}

		public function sign_up($data) {
			$data = strtr($data, "_-:", "/+=");
			if (($data = base64_decode($data)) === false) {
				return false;
			}
		 	
			$aes = new AES256($this->settings->secret_website_code);
			if (($data = $aes->decrypt($data)) === false) {
				return false;
			}

			if (($data = json_decode($data, true)) === false) {
				return false;
			}

			if ($data["timestamp"] + HOUR < time()) {
				return false;
			}

			$signature = $data["signature"];
			unset($data["signature"]);
			if ($this->get_signature($data) != $signature) {
				return false;
			}

			if ($this->valid_signup($data) == false) {
				return false;
			}

			$aes = new AES256($data["password"]);
			$crypto_key = base64_encode($aes->encrypt(random_string(32)));

			$user = array(
				"id"              => null,
				"organisation_id" => 1,
				"username"        => $data["username"],
				"password"        => hash_password($data["password"], $data["username"]),
				"one_time_key"    => null,
			    "status"          => USER_STATUS_ACTIVE,
				"fullname"        => $data["fullname"],
				"email"           => $data["email"],
				"crypto_key"      => $crypto_key);

			if ($this->db->query("begin") == false) {
				return false;
			}

			if ($this->db->insert("users", $user) == false) {
				$this->db->query("rollback");
				return false;
			}
			$user_id = $this->db->last_insert_id;

			if ($this->db->query("insert into user_role values (%d, %d)", $user_id, USER_ROLE_ID) == false) {
				$this->db->query("rollback");
				return false;
			}

			$email = new email("New account registered at ".$_SERVER["SERVER_NAME"], $this->setttings->webmaster_email);
			$email->set_message_fields(array(
				"FULLNAME" => $data["fullname"],
				"EMAIL"    => $data["email"],
				"USERNAME" => $data["username"],
				"HOSTNAME" => $_SERVER["SERVER_NAME"],
				"IP_ADDR"  => $_SERVER["REMOTE_ADDR"]));
			$email->message(file_get_contents("../extra/account_registered.txt"));
			$email->send($this->settings->webmaster_email);

			return $this->db->query("commit") !== false;
		}
	}
?>
