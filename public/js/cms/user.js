function generate_checkbox() {
	checkbox = document.getElementById("generate");
	password = document.getElementById("password");

	if (checkbox.checked) {
		password.value = "";
		password.disabled = true;
	} else {
		password.disabled = false;
	}
}
