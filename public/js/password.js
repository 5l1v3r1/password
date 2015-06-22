ajax = new ajax;

/* Show password
 */
function load_values(id) {
	ajax.get("password/get/" + id, "", show_values);
}

function show_values(result) {
	if ((error = result.getValue("error")) != "none") {
		password.value = "&lt;&lt; " + error + " &gt;&gt;";
		return;
	}

	/* Show password
	 */
	if ((password = document.getElementById("password")) != undefined) {
		password.value = result.getValue("password");
	}

	/* Show information
	 */
	if ((info = document.getElementById("info")) != undefined) {
		if ((info.innerHTML = result.getValue("info")) != null) {
			if ((row = document.getElementById("inforow")) != undefined) {
				row.style.display = "table-row";
			}
		}
	}
}

/* Generate random password
 */
function get_random_password() {
	ajax.get("password/random", "", set_random_password);
}

function set_random_password(result) {
	if ((password = document.getElementById("password")) == undefined) {
		alert("Error");
		return;
	}

	password.value = result.getValue("password");
}
