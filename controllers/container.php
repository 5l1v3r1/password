<?php
	class container_controller extends controller {
		private function show_crumbs($container_id) {
			$this->output->open_tag("crumbs");

			if (($crumbs = $this->model->get_crumbs($container_id)) !== false) {
				foreach ($crumbs as $crumb) {
					$this->output->add_tag("crumb", $crumb["name"], array("id" => $crumb["id"]));
				}
			}
			$this->output->close_tag();
		}

		private function show_overview($parent_id = 0) {
			if (($containers = $this->model->get_containers($parent_id)) === false) {
				$this->output->add_tag("result", "Database error.");
				return;
			}

			if (($parent_parent_id = $this->model->get_parent_id($parent_id)) === false) {
				$this->output->add_tag("result", "Container not found.");
				return false;
			}

			if (($passwords = $this->model->get_passwords($parent_id)) === false) {
				return;
			}

			$this->output->run_javascript("document.getElementById('search').focus()");

			$params = array(
				"id"        => $parent_id,
				"parent_id" => $parent_parent_id);
			$this->output->open_tag("overview", $params);

			foreach ($containers as $container) {
				$this->output->record($container, "container");
			}
			foreach ($passwords as $password) {
				$this->output->record($password, "password");
			}
			$this->output->close_tag();

			$this->show_crumbs($parent_id);
		}

		private function search_passwords($search) {
			if (($passwords = $this->model->search_passwords($search)) === false) {
				$this->output->add_tag("result", "Database error.");
				return;
			}

			if (count($passwords) == 0) {
				$this->output->add_tag("result", "No search results");
				return;
			} else if (count($passwords) == 1) {
				header("Location: /password/".$passwords[0]["id"]);
			}

			$this->output->open_tag("overview");

			foreach ($passwords as $password) {
				$password["path"] = $this->model->get_path($password["container_id"]);
				$this->output->record($password, "password");
			}

			$this->output->close_tag();
		}

		private function show_container_form($container) {
			if ($container["parent_id"] == null) {
				$container["parent_id"] = 0;
			}

			$this->output->open_tag("edit");
			$this->output->record($container, "container");

			if (isset($container["id"])) {
				if (($containers = $this->model->get_all_containers($container["id"])) !== false) {
					$this->output->open_tag("containers");
					$this->output->add_tag("container", ROOT_CONTAINER_NAME, array("id" => 0));
					foreach ($containers as $cont) {
						if ($this->model->parent_loop($container["id"], $cont["id"])) {
							continue;
						}
						$this->output->add_tag("container", $cont["name"], array("id" => $cont["id"]));
					}
					$this->output->close_tag();
				}
			}

			$this->output->close_tag();

			$this->show_crumbs($container["parent_id"]);
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Search") {
					/* Search
					 */
					$this->search_passwords($_POST["search"]);
				} else if ($_POST["submit_button"] == "Save container") {
					/* Save container
					 */
					if ($this->model->save_oke($_POST) == false) {
						$this->show_container_form($_POST);
					} else if (isset($_POST["id"]) === false) {
						/* Create container
						 */
						if ($this->model->create_container($_POST) === false) {
							$this->output->add_message("Error creating container.");
							$this->show_container_form($_POST);
						} else {
							$this->user->log_action("container created");
							$this->show_overview($_POST["parent_id"]);
						}
					} else {
						/* Update container
						 */
						if ($this->model->update_container($_POST) === false) {
							$this->output->add_message("Error updating container.");
							$this->show_container_form($_POST);
						} else {
							$this->user->log_action("container updated");
							$this->show_overview($_POST["parent_id"]);
						}
					}
				} else if ($_POST["submit_button"] == "Delete container") {
					/* Delete container
					 */
					if ($this->model->delete_oke($_POST) == false) {
						$this->show_container_form($_POST);
					} else if ($this->model->delete_container($_POST["id"]) === false) {
						$this->output->add_message("Error deleting container.");
						$this->show_container_form($_POST);
					} else {
						$this->user->log_action("container deleted");
						$this->show_overview($_POST["parent_id"]);
					}
				} else {
					$this->show_overview();
				}
			} else if (valid_input($this->page->pathinfo[1], VALIDATE_NUMBERS, VALIDATE_NONEMPTY)) {
				if ($this->page->pathinfo[2] === "new") {
					/* New container
					 */
					if ($this->page->pathinfo[1] != 0) {
						if ($this->model->get_container($this->page->pathinfo[1]) == false) {	
							$this->output->add_tag("result", "Parent container not found.");
							return;
						}
					}
					$container = array("parent_id" => $this->page->pathinfo[1]);
					$this->show_container_form($container);
				} else if ($this->page->pathinfo[2] === "edit") {
					/* Edit container
					 */
					if (($container = $this->model->get_container($this->page->pathinfo[1])) === false) {
						$this->output->add_tag("result", "Container not found.\n");
					} else {
						$this->show_container_form($container);
					}
				} else {
					$this->show_overview($this->page->pathinfo[1]);
				}
			} else {
				/* Show overview
				 */
				$this->show_overview();
			}
		}
	}
?>
