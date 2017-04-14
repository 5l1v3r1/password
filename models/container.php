<?php
	class container_model extends model {
		public function get_containers($parent_id) {
			$query = "select id,name from containers ".
			         "where user_id=%d and parent_id";
			$query .= ($parent_id == 0) ?  " is null " : "=%d ";
			$query .= "order by name";

			return $this->db->execute($query, $this->user->id, $parent_id);
		}

		private function get_parent_path($child, $containers) {
			$result = "";

			foreach ($containers as $container) {
				if ($container["id"] == $child["parent_id"]) {
					$result = $container["name"]." / ";

					if ($container["parent_id"] != 0) {
						$path = $this->get_parent_id($container, $containers);
						$result = $path.$result;
					}
				}
			}

			return $result;
		}

		private function container_sort($a, $b) {
			return strcmp($a["name"], $b["name"]);
		}

		public function get_all_containers($current_id) {
			$query = "select id,name,parent_id from containers where user_id=%d";

			$containers = $this->db->execute($query, $this->user->id);

			foreach ($containers as $i => $container) {
				$path = $this->get_parent_path($container, $containers);
				$containers[$i]["name"] = $path.$container["name"];
			}

			usort($containers, array($this, "container_sort"));

			return $containers;
		}

		public function get_passwords($container_id) {
			$query = "select p.* from passwords p, containers c ".
			         "where p.container_id=c.id and p.container_id=%d and c.user_id=%d ".
			         "order by name";

			return $this->db->execute($query, $container_id, $this->user->id);
		}

		public function search_passwords($search) {
			$query = "select p.* from passwords p, containers c ".
			         "where p.container_id=c.id and (p.name like %s or p.username like %s) and c.user_id=%d ".
			         "order by name";
			$search = "%".$search."%";

			return $this->db->execute($query, $search, $search, $this->user->id);
		}

		public function get_path($container_id) {
			if (($container = $this->get_container($container_id)) === false) {
				return false;
			}

			$result = $container["name"]." / ";

			if ($container["parent_id"] != 0) {
				$result = $this->get_path($container["parent_id"]).$result;
			}

			return $result;
		}

		public function get_parent_id($parent_id) {
			if ($parent_id == 0) {
				return 0;
			}

			$query = "select parent_id from containers where id=%d and user_id=%d limit 1";

			if (($container = $this->db->execute($query, $parent_id, $this->user->id)) == false) {	
				return false;
			}
			if ($container[0]["parent_id"] == null) {
				return 0;
			}

			return $container[0]["parent_id"];
		}

		public function get_container($container_id) {	
			static $cache = array();

			if (isset($cache[$container_id])) {
				return $cache[$container_id];
			}

			$query = "select * from containers where id=%d and user_id=%d";
			if (($container = $this->db->execute($query, $container_id, $this->user->id)) === false) {
				return false;
			}

			$cache[$container_id] = $container[0];

			return $container[0];
		}

		public function get_crumbs($container_id) {
			$result = array();

			while ($container_id != 0) {
				if (($container = $this->get_container($container_id)) === false) {
					return false;
				}
				array_unshift($result, $container);

				$container_id = $container["parent_id"];
			}

			return $result;
		}

		public function valid_container_id($container_id) {
			if (valid_input($container_id, VALIDATE_NUMBERS, VALIDATE_NONEMPTY) == false) {
				return false;
			} else if ($container_id == 0) {
				return true;
			} else if (($container = $this->get_container($container_id)) === false) {
				return false;
			} else if ($container["user_id"] != $this->user->id) {
				return false;
			}

			return true;
		}

		public function parent_loop($container_id, $parent_id) {
			do {
				if ($parent_id == $container_id) {
					return true;
				}

				$parent_id = $this->get_parent_id($parent_id);
			} while ($parent_id != 0);

			return false;
		}

		public function save_oke($container) {
			$result = true;

			/* Check name
			 */
			if (trim($container["name"]) == "") {
				$this->output->add_message("The name can't be empty.");
				$result = false;
			} else if (strtolower(trim($container["name"])) == strtolower(ROOT_CONTAINER_NAME)) {
				$this->output->add_message("Invalid container name.");
				$result = false;
			}

			if ($this->valid_container_id($container["parent_id"]) == false) {
				$this->output->add_message("Invalid parent container id.");
				$result = false;
			}

			/* Check double name
			 */
			$query = "select count(*) as count from containers where user_id=%d and name=%s and parent_id";
			$args = array($this->user->id, $container["name"]);

			if ($container["parent_id"] == 0) {
				$query .= " is null";
			} else {
				$query .= "=%d";
				array_push($args, $container["parent_id"]);
			}

			if (isset($container["id"])) {
				$query .= " and id!=%d";
				array_push($args, $container["id"]);
			}

			if (($count = $this->db->execute($query, $args)) == false) {
				$result = false;
			} else if ($count[0]["count"] > 0) {
				$this->output->add_message("Container name already exists.");
				$result = false;
			}

			/* Check parent loop
			 */
			if (isset($container["id"])) {
				if ($this->parent_loop($container["id"], $container["parent_id"])) {
					$this->output->add_message("Container parent loop detected.");
					$result = false;
				}
			}

			return $result;
		}

		public function create_container($container) {
			$keys = array("id", "user_id", "parent_id", "name");

			$container["id"] = null;
			$container["user_id"] = $this->user->id;
			if ($container["parent_id"] == 0) {
				$container["parent_id"] = null;
			}

			return $this->db->insert("containers", $container, $keys);
		}

		public function update_container($container) {
			$query = "update containers set name=%s, parent_id=";
			$params = array($container["name"]);

			if ($container["parent_id"] == 0) {
				$query .= "null";
			} else {
				$query .= "%d";
				array_push($params, $container["parent_id"]);
			}

			$query .= " where id=%d and user_id=%d";

			array_push($params, $container["id"], $this->user->id);

			return $this->db->query($query, $params) !== false;
		}

		public function delete_oke($container) {
			$result = true;

			if ($this->valid_container_id($container["id"]) == false) {
				$this->output->add_message("Invalid container id.");
				$result = false;
			}

			$query = "select count(*) as count from containers where parent_id=%d";
			if (($count = $this->db->execute($query, $container["id"])) == false) {
				$this->output->add_message("Database error.");
				return false;
			}
			if ($count[0]["count"] > 0) {
				$this->output->add_message("Container contains other container(s).");
				$result = false;
			}

			$query = "select count(*) as count from passwords where container_id=%d";
			if (($count = $this->db->execute($query, $container["id"])) == false) {
				$this->output->add_message("Database error.");
				return false;
			}
			if ($count[0]["count"] > 0) {
				$this->output->add_message("Container contains password(s).");
				$result = false;
			}

			return $result;
		}

		public function delete_container($container_id) {
			$this->db->delete("containers", $container_id);
		}
	}
?>
