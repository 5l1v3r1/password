function set_focus() {
	username = document.getElementById("username");
	password = document.getElementById("password");
	
	if (username.value == "") {
		username.focus();
	} else {
		password.focus();
	}
}
