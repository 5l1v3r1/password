<?php
	class banshee_page_model extends model {
		public function get_page($url) {
			return $this->db->entry("pages", $url, "url");
		}
	}
?>
