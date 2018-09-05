(function() {
	//
	// Checks if the given string is a valid URL
	//
	var isURL = function (str) {
	  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
	  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
	  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
	  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
	  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
	  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
	  return pattern.test(str);
	}
	//
	// Fetches metadata through an ajax call
	//
	var fetchMeta = function () {
		// Add http if the URL is missing it
		var urlValue = $("#inputHeaderLinkShortner").val();
		if (urlValue.indexOf('http://') == -1 && urlValue.indexOf('https://') == -1) {
			urlValue = "http://" + urlValue;
		}
		// Fetch metadata via ajax
		$.ajax({
			'url': '/ajax/ajax_get_meta.php?url=' + urlValue
		}).always(function (dataResult) {
			if (dataResult == undefined || dataResult.errors == undefined) {
				return;
			}
			if (dataResult.errors.length <= 0) {
				// Set metadata attributes

				// Set title
				if (dataResult.results.title) {
					$("#inputMetaTitle").val(dataResult.results.title);
				} else {
					$("#inputMetaTitle").val("");
				}
				// Set description
				if (dataResult.results.description) {
					$("#inputMetaDescription").val(dataResult.results.description);
				} else {
					$("#inputMetaDescription").val("");
				}
				// Set image
				if (dataResult.results.imageURL) {
					$("#inputMetaImageLink").val(dataResult.results.imageURL);
					document.getElementById("imgMetaImagePreview").src = dataResult.results.imageURL;
					$("#imgMetaImagePreview").fadeIn();
				} else {
					$("#inputMetaImageLink").val("");
					$("#imgMetaImagePreview").fadeOut();
				}

				// Remove fetching label
				$("#actionLinkHeaderLinkShortner").text("Fetch existing meta data");
			}
		})
	}

	// Holds the last URL value
	var lastURLValue = "";
	// Used to delay parsing
	var parseWorker;
	var imageFetcherWorker;

	$(document).ready(function () {
		// Handle image url changing
		$("#inputMetaImageLink").change(function () {
			if (imageFetcherWorker != null) {
				window.clearInterval(imageFetcherWorker);
			}
			imageFetcherWorker = setInterval(function () {
				if (isURL($("#inputMetaImageLink").val())) {
					document.getElementById("imgMetaImagePreview").src = $("#inputMetaImageLink").val();
					$("#imgMetaImagePreview").fadeIn();
				} else {
					$("#imgMetaImagePreview").fadeOut();
				}
			}, 200);
		});
		// Handle fetch meta click
		$("#actionLinkHeaderLinkShortner").click(function (e) {
			$("#actionLinkHeaderLinkShortner").text("Fetching...");
			e.preventDefault();
			fetchMeta();
		})
		$("#inputHeaderLinkShortner").keyup(function (e) {
			var urlValue = $(this).val();
			if (e.keyCode == 13 && isURL(urlValue)) {
				$("#actionLinkHeaderLinkShortner").text("Fetching...");
				fetchMeta();
			}
			// Delay parsing
			if (parseWorker != null) {
				window.clearInterval(parseWorker);
			}
			parseWorker = setInterval(function () {
				// Check that the value changed
				if (lastURLValue == urlValue) {
					return
				}
				lastURLValue = urlValue
				// Check that the URL is valid
				if (!isURL(urlValue)) {
					$("#actionLinkHeaderLinkShortner").fadeOut();
				} else {
					$("#actionLinkHeaderLinkShortner").fadeIn();
				}
				window.clearInterval(parseWorker);
				parseWorker = null;
			}, 200);
		})
	})
})()