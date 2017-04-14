<?php
	class password_model extends model {
		public function get_password($password_id) {
			$query = "select p.* from passwords p, containers c ".
			         "where p.id=%d and p.container_id=c.id and c.user_id=%d";
			if (($result = $this->db->execute($query, $password_id, $this->user->id)) == false) {
				return false;
			}
			$password = $result[0];

			$password["password"] = $this->decrypt($password["password"]);
			$password["info"] = $this->decrypt($password["info"]);
			if ($password["container_id"] === null) {
				$password["container_id"] = 0;
			}

			return $password;
		}

		public function get_all_containers() {
			return $this->borrow("container")->get_all_containers(null);
		}

		public function save_oke($password) {
			$result = true;

			if (isset($password["id"])) {
				if ($this->get_password($password["id"]) == false) {
					return false;
				}
			}

			if ($this->borrow("container")->valid_container_id($password["container_id"]) == false) {
				$this->output->add_message("Invalid container id.");
				$result = false;
			} else if ($password["container_id"] == 0) {
				$this->output->add_message("Invalid container id.");
				$result = false;
			}

			$fields = array("name");
			if (isset($password["id"]) == false) {
				array_push($fields, "password");
			}
			foreach ($fields as $field) {
				if (trim($password[$field]) == "") {
					$this->output->add_message("Fill in the ".$field.".");
					$result = false;
				}
			}

			return $result;
		}

		private function encrypt($data) {
			if ($data == "") {
				return "";
			}

			$aes = new AES256($_SESSION["crypto_key"].$_COOKIE["crypto_key"]);
			return $aes->encrypt($data);
		}

		private function decrypt($data) {
			if ($data == "") {
				return "";
			}

			$aes = new AES256($_SESSION["crypto_key"].$_COOKIE["crypto_key"]);
			return $aes->decrypt($data);
		}

		public function create_password($password) {
			$keys = array("id", "container_id", "name", "url", "username", "password", "info");

			$password["id"] = null;
			$password["password"] = $this->encrypt($password["password"]);
			$password["info"] = $this->encrypt($password["info"]);

			return $this->db->insert("passwords", $password, $keys);
		}

		public function update_password($password) {
			$keys = array("container_id", "name", "url", "username", "info");

			if ($password["password"] != "") {
				array_push($keys, "password");
				$password["password"] = $this->encrypt($password["password"]);
			}

			$password["info"] = $this->encrypt($password["info"]);

			return $this->db->update("passwords", $password["id"], $password, $keys);
		}

		public function delete_oke($password) {
			return $this->get_password($password["id"]) !== false;
		}

		public function delete_password($password_id) {
			return $this->db->delete("passwords", $password_id);
		}
	}
?>
