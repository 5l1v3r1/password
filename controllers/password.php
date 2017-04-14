<?php
	class password_controller extends controller {
		private $url = array("url" => "container");

		private function show_crumbs($container_id) {
			$this->output->open_tag("crumbs");

			if (($crumbs = $this->model->borrow("container")->get_crumbs($container_id)) !== false) {
				foreach ($crumbs as $crumb) {
					$this->output->add_tag("crumb", $crumb["name"], array("id" => $crumb["id"]));
				}
			}

			$this->output->close_tag();
		}

		private function show_password($password_id) {
			if (($password = $this->model->get_password($password_id)) === false) {
				$this->output->add_tag("result", "Password not found.", $this->url);
				return;
			}

			$this->output->add_javascript("password.js");
			$this->output->run_javascript("load_values(".$password_id.")");

			$this->output->record($password, "password");

			$this->show_crumbs($password["container_id"]);
		}

		private function show_password_form($password) {
			$this->output->add_javascript("password.js");

			$this->output->open_tag("edit");
			$this->output->record($password, "password");

			if (isset($password["id"])) {
				if (($containers = $this->model->get_all_containers()) !== false) {
					$this->output->open_tag("containers");
					foreach ($containers as $cont) {
						$this->output->add_tag("container", $cont["name"], array("id" => $cont["id"]));
					}
					$this->output->close_tag();
				}
			}

			$this->output->close_tag();

			$this->show_crumbs($password["container_id"]);
		}

		private function ajax_values($password_id) {
			if (valid_input($password_id, VALIDATE_NUMBERS, VALIDATE_NONEMPTY) == false) {
				$this->output->add_tag("error", "Invalid password id.");
				return;
			}

			if (($values = $this->model->get_password($password_id)) == false) {
				$this->output->add_tag("error", "Unknown password.");
				return;
			}

			if (($values["password"] === false) || ($values["info"] === false)) {
				$this->output->add_tag("error", "Error decrypting password.");
			} else {
				$this->output->add_tag("error", "none");
				$this->output->add_tag("password", $values["password"]);
				$this->output->add_tag("info", $values["info"]);
			}
		}

		private function random_password() {
			$this->output->add_tag("password", random_string(20));
		}

		public function execute() {
			if ($this->page->ajax_request) {	
				switch ($this->page->pathinfo[1]) {
					case "get":
						$this->ajax_values($this->page->pathinfo[2]);
						break;
					case "random":
						$this->random_password();
						break;
				}
				return;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$this->url["url"] .= "/".$_POST["container_id"];

				if ($_POST["submit_button"] == "Save password") {
					/* Save password
					 */
					if ($this->model->save_oke($_POST) == false) {
						$this->show_password_form($_POST);
					} else if (isset($_POST["id"]) === false) {
						/* Create password
						 */
						if ($this->model->create_password($_POST) === false) {
							$this->output->add_message("Error creating password.");
							$this->show_password_form($_POST);
						} else {
							$this->user->log_action("password created");
							$this->output->add_tag("result", "Password created.", $this->url);
							header("Location: /".$this->url["url"]);
						}
					} else {
						/* Update password
						 */
						if ($this->model->update_password($_POST) === false) {
							$this->output->add_message("Error updating password.");
							$this->show_password_form($_POST);
						} else {
							$this->user->log_action("password updated");
							$this->output->add_tag("result", "Password updated.", $this->url);
							header("Location: /".$this->url["url"]);
						}
					}
				} else if ($_POST["submit_button"] == "Delete password") {
					/* Delete password
					 */
					if ($this->model->delete_oke($_POST) == false) {
						$this->show_password_form($_POST);
					} else if ($this->model->delete_password($_POST["id"]) === false) {
						$this->output->add_message("Error deleting password.");
						$this->show_password_form($_POST);
					} else {
						$this->user->log_action("password deleted");
						$this->output->add_tag("result", "Password deleted.", $this->url);
						header("Location: /".$this->url["url"]);
					}
				} else {
					$this->output->add_tag("result", "Password not found.", $this->url);
				}
			} else if (valid_input($this->page->pathinfo[1], VALIDATE_NUMBERS, VALIDATE_NONEMPTY)) {
				if ($this->page->pathinfo[2] === "new") {
					/* New password
					 */
					if ($this->model->borrow("container")->get_container($this->page->pathinfo[1]) == false) {
						$this->output->add_tag("result", "Parent container not found.", array("url" => "container"));
					} else {
						$password = array("container_id" => $this->page->pathinfo[1]);
						$this->show_password_form($password);
					}
				} else if ($this->page->pathinfo[2] === "edit") {
					/* Edit password
					 */
					if (($password = $this->model->get_password($this->page->pathinfo[1])) === false) {
						$this->output->add_tag("result", "Password not found.\n");
					} else {
						$this->show_password_form($password);
					}
				} else {
					$this->show_password($this->page->pathinfo[1]);
				}
			} else {
				/* Show error
				 */
				$this->output->add_tag("result", "No password selected.", $this->url);
			}
		}
	}
?>
