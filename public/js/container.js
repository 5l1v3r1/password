function search_style() {
	if ((elem = document.getElementById("search")) == undefined) {
		return;
	}

	if (elem.value != "Search") {
		return;
	}

	elem.value = "";
	elem.style.color = "#000000";
}
