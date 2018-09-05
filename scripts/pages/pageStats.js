(function () {
	$(document).ready(function () {
		$("#inputLink").click(function () {
		   $(this).select();
		})
		$("#inputLink").select()
	})
})()

function initLineGraph(data, labels, id) {
	var chart = new Chart(document.getElementById(id), {
		type: 'line',
		data: {
			labels: labels,
			datasets: [{
				label: 'Link clicks',
				data: data,
				backgroundColor: 'rgb(255, 99, 132)',
	            borderColor: 'rgb(255, 255, 240)',
			}]
		},
		responsive: true
	})
}

function initPieGraph(data, labels, id) {
	var chart = new Chart(document.getElementById(id), {
		type: 'pie',
		data: {
			labels: labels,
			datasets: [{
				label: 'Link clicks',
				data: data,
				backgroundColor: [
					'rgb(200, 99, 132)',
					'rgb(255, 99, 100)',
					'rgb(10, 99, 132)',
					'rgb(255, 200, 132)',
					'rgb(255, 200, 200)',
				],
			}]
		},
		responsive: true
	})
}