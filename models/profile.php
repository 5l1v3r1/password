<?php
	class profile_model extends model {
		public function last_account_logs() {
			if (($fp = fopen("../logfiles/actions.log", "r")) == false) {
				return false;
			}

			$result = array();

			while (($line = fgets($fp)) !== false) {	
				list($ip, $timestamp, $user_id, $message) = explode("|", chop($line));

				if ($user_id == "-") {
					continue;
				} else if ($user_id != $this->user->id) {
					continue;
				}

				array_push($result, array(
					"ip"        => $ip,
					"timestamp" => $timestamp,
					"message"   => $message));
				if (count($result) > 15) {
					array_shift($result);
				}
			}

			fclose($fp);

			return array_reverse($result);
		}

		public function profile_oke($profile) {
			$result = true;

			if (trim($profile["fullname"]) == "") {
				$this->output->add_message("Fill in your name.");
				$result = false;
			}

			if (valid_email($profile["email"]) == false) {
				$this->output->add_message("Invalid e-mail address.");
				$result = false;
			} else if (($check = $this->db->entry("users", $profile["email"], "email")) != false) {
				if ($check["id"] != $this->user->id) {
					$this->output->add_message("E-mail address already exists.");
					$result = false;
				}
			}

			$current = hash_password($profile["current"], $this->user->username);
			if ($current != $this->user->password) {
				$this->output->add_message("Current password is incorrect.");
				$result = false;
			}

			if ($profile["password"] != "") {
				if ($profile["password"] != $profile["repeat"]) {
					$this->output->add_message("New passwords do not match.");
					$result = false;
				} else if ($this->user->password == $profile["hashed"]) {
					$this->output->add_message("New password must be different from current password.");
					$result = false;
				}
			}

			return $result;
		}

		public function update_profile($profile) {
			$keys = array("fullname", "email");

			if ($profile["password"] != "") {
				array_push($keys, "status");
				$profile["status"] = USER_STATUS_ACTIVE;

				array_push($keys, "crypto_key");
				$aes = new AES256($profile["password"]);
				$profile["crypto_key"] = base64_encode($aes->encrypt($_SESSION["crypto_key"].$_COOKIE["crypto_key"]));

				array_push($keys, "password");
				$profile["password"] = $profile["hashed"];
			}

			return $this->db->update("users", $this->user->id, $profile, $keys) !== false;
		}
	}
?>
