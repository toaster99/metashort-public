var indicators = Array();

function setLoading(isLoading, indicatorDivID) {
	if (isLoading && indicators[indicatorDivID] != undefined && indicators[indicatorDivID] != null) {
		return;
	}
	if (isLoading) {
		$(indicatorDivID).text(".");
		$(indicatorDivID).css("display","block");
		indicators[indicatorDivID] = setInterval(function () {
			if (indicators[indicatorDivID] == null) {
				$(indicatorDivID).text("");
				return;
			}
			let currentDotsLength = $(indicatorDivID).text().length ? $(indicatorDivID).text().length : 0;
			let newDotsLength = currentDotsLength >= 3 ? 1 : currentDotsLength + 1;
			let newDots = Array(newDotsLength + 1).join('.');
			$(indicatorDivID).fadeOut(500, function() {
				$(indicatorDivID).text(newDots);
				$(indicatorDivID).fadeIn(500);
			});
		}, 1000);
	} else {
		indicators[indicatorDivID] = null;
		$(indicatorDivID).css("display","none");
		$(indicatorDivID).text("");
		window.clearInterval(indicators[indicatorDivID]);
	}
}