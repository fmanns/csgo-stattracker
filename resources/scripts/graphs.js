google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawCharts);
function drawCharts() {
	var type = "kd";
	var options = {
		title: 'Kill/Death Ratio',
		width: 900,
		height: 500,
		hAxis: {title: "Hours Played"},
		vAxis: {title: "K/D Ratio"}
	};
	var selector = '#chart_kd';
	var header = ["Hours", "Ratio"];
	getGraphData(type, selector, options, header);

	type = "acc";
	options = {
		title: 'Accuracy',
		width: 900,
		height: 500,
		hAxis: {title: "Hours Played"},
		vAxis: {title: "Accuracy (%)"}
	};
	selector = '#chart_acc';
	header = ["Hours", "Accuracy"];
	getGraphData(type, selector, options, header);

	type = "hs";
	options = {
		title: 'Headshot Percentage',
		width: 900,
		height: 500,
		hAxis: {title: "Hours Played"},
		vAxis: {title: "Headshot (%)"}
	};
	selector = '#chart_hs';
	header = ["Hours", "Headshot %"];
	getGraphData(type, selector, options, header, function(){
		$('#graphs_loading').remove();
	});


}

function getGraphData(type, selector, options, header, callback) {
	$.ajax({
		url: "stats.php",
		type: "post",
		data: {"graph": type},
		dataType: "json",
		success: function(response) {
			if (response !== "error") {
				var x = [];
				x[0] = header;
				for (var i in response[type]) {
					x.push(response[type][i]);
				}
				var data = google.visualization.arrayToDataTable(x);
				var chart = new google.visualization.LineChart($(selector).get(0));
				chart.draw(data, options);
				if (typeof callback === "function") {
					callback();
				}
			} else {
				console.log(response);
			}			
		}
	});
}