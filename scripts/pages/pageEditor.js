(function () {
	var advancedShowen = false;
	$(document).ready(function () {
		//
		// Select the input with the shortened URL on page load
		//
		$("#inputHeaderLinkShortner").select();

		//
		// Handle clicking the copy button
		//
		$("#inputHeaderLinkCopyButton").click(function () {
			document.getElementById("inputHeaderLinkShortner").select();
			document.execCommand("Copy");
			$(this).text("copied!");
			$("#inputHeaderLinkCopyButton").addClass("active");
			window.setTimeout(() => {
				$("#inputHeaderLinkCopyButton").text("copy");
				$("#inputHeaderLinkCopyButton").removeClass("active");
			}, 2000)
		})

		//
		// Select the input with the shortened URL when clicked
		//
		$("#inputHeaderLinkShortner").click(function () {
			$(this).select();
		})

		//
		// Setup the show advanced button of the page editor
		//
		$("#pageEditorShowAdvancedButton").click(function (e) {
			e.preventDefault();
			if (!advancedShowen) {
				$("#pageEditorAdvancedMetaTags").show();
				$(this).text("- Hide advanced options");
			} else {
				$("#pageEditorAdvancedMetaTags").hide();
				$(this).text("+ Show advanced options");
			}
			advancedShowen = !advancedShowen;
		})

		//
		// Setup suggestion lists
		//
		$(".suggestions-list").each(function (e) {
			let $list = $(this);
			let $target = $($list.attr("data-target"));
			$target.mousedown(() => {
				$list.removeClass("hidden");
			})
			$target.blur(() => {
				$list.addClass("hidden");
			})
			$target.keyup(() => {
				$list.addClass("hidden");
			})
			$list.children(".list-group-item").mousedown(function (e) {
				e.preventDefault();
				$target.val($(this).text().trim());
				$list.addClass("hidden");
				
			});
		})
	})
})()



