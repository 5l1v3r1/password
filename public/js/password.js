/* Show password
 */
function load_values(id) {
	$.get("/password/get/" + id, function(data) {
		password = $(data).find("password").text();
		$("input#password").val(password);

		if ((info = $(data).find("info").text()) != "") {
			$("span#info").text(info);
			if (info.substr(0, 10) == "-----BEGIN") {
				$("span#info").css("font-family", '"Lucida Console",Monaco,monospace');
				$("span#info").css("font-size", "12px");
			}
			$("tr#inforow").css("display", "table-row");
		}
	}).fail(function() {
		alert("Error retrieving account information.");
	});
}

/* Generate random password
 */
function get_random_password() {
	$.get("/password/random", function(data) {
		password = $(data).find("password").text();
		$("input#password").val(password);
	}).fail(function() {
		alert("Error retrieving random password.");
	});
}
