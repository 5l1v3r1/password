<?php
	/* controllers/logout.php
	 *
	 * Copyright (C) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * http://www.banshee-php.org/
	 */

	class logout_controller extends controller {
		public function execute() {
			if ($this->user->logged_in) {
				header("Status: 401");
				$this->user->logout();

				$this->output->add_tag("result", "You are now logged out.", array("url" => $this->settings->start_page));
			} else {
				$this->output->add_tag("result", "You are not logged in.", array("url" => $this->settings->start_page));
			}
		}
	}
?>
