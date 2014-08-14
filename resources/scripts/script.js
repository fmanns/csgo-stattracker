$(document).ready(function(){

	$(".tab").click(function(){
		$('.tab').removeClass("activeTab");
		$(this).addClass("activeTab");

		$('.contentPane').removeClass('activePane');
		var index = getIndex($(this));
		$('.contentPane').eq(index).addClass("activePane");

		if (index !== 0) {
			tab_name = $(this).text().toLowerCase();
			window.location.hash = tab_name;
		} else {
			window.location.hash = "";
		}
	});

	if(window.location.hash) {
		var hashValue = window.location.hash.replace(/#/, "");
		var tab = $('.tab.' + hashValue);
		if (tab.length) {
			// Hash value corresponds to a tab
			tab.trigger("click");
		} else {
			// No tabs match hash. Set to 'general' tab
			$(".tab").eq(0).trigger("click");
		}
	} else {
		// No hash given. Set to 'general' tab
		$(".tab").eq(0).trigger("click");
	}

	$('.refresher').click(function(){
		refreshData($(this));
	});

	sortUsingNestedText($(".sortable"), "article", "header");

	$('.picker.filter li').click(function(){
		var value = $(this).attr("id").replace(/.*:/, "");
		var container = $(this).closest(".contentPane").children(".container");
		filterStats(value, container);

		var picker = $(this).closest(".picker");
		picker.find("h2").html($(this).text());
		picker.trigger("mouseleave");
	});

	$('.picker.sort li').click(function(){
		var value = $(this).attr("id").replace(/.*:/, "");
		var container = $(this).closest(".contentPane").children(".container");
		sortUsingNestedText(container, "article", value);
		var picker = $(this).closest(".picker");
		picker.find("h2").html($(this).text());
		picker.trigger("mouseleave");
	});

	$('#communityID').tooltip({content: "Examples: <br>	http://steamcommunity.com/id/fmanns <br> http://steamcommunity.com/profiles/76561198004495197"});

	if ($('#communityID').val() === "") {
		$('#communityID').val("http://steamcommunity.com/id/fmanns");
	}

	$('form.ajax').on('submit', function(){
		var url = $(this).attr('action'),
			type = $(this).attr('method'),
			data = {};

		$(this).find('[name]').each(function(){
			var name = $(this).attr('name'),
				value = $(this).val();

			data[name] = value;
		});

		$.ajax({
			url: url,
			type: type,
			data: data,
			dataType: "json",
			success: function(response) {
				if (!jQuery.isEmptyObject(response)) {
					refreshAll();
					console.log(response);

					$('#avatar').attr("src", response["avatar"]);
					$('#profileLink').attr("href", "http://steamcommunity.com/profiles/" + response["steamID64"]);
					$('#profileName').html(response["steamID"]);


					$('#loginInfo').slideUp("slow", function(){
						$(this).remove()
						$('#main').removeClass("dontDisplay");
					});

					$('#banner').attr("src", "resources/images/header_csgo_thin.png").addClass("thin");
					if (response["custom_url"] != "") {
						window.history.pushState("", "", response["custom_url"]);
					} else {
						window.history.pushState("", "", response["steamID64"]);
					}
				} else {
					$("#communityID").addClass("warning");
					$('#alert').html("Could not locate Steam account.");
				}
			}
		});
		return false;
	});

	$('#avatar').click(function(){
		$('#profileInfo .dropdown').slideToggle(0);
	});

	$('#tracking').click(function(){
		updateTracking();
	});

	$('.picker').mouseenter(function(){
		$(this).find(".dropdown").slideDown("fast");
		$(this).find("i").addClass("fa-rotate-180");
	});

	$('.picker').mouseleave(function(){
		$(this).find(".dropdown").slideUp("fast");
		$(this).find("i").removeClass("fa-rotate-180");
	});

	if ($('#main').css("display") !== "none") {
		refreshAll();
	}
});

function getIndex(element) {
	var siblings = element.parent().children();
	return siblings.index(element);
}

function refreshData(element, data) {
	var pane = element.closest(".contentPane");
	spinIcon(element);
	var loader = pane.find(".container").find(".loader");
	loader.css("display", "block");
	$.ajax({
		url: "stats.php",
		dataType: "json",
		type: "post",
		data: {stats: "all"},
		success: function(response) {
			updateData(element, pane, loader, response);
		}
	});
}

function updateData(element, pane, loader, response) {
	pane.find(".stat").each(function(){
		var stat_name = $(this).attr("id");
		var stat_value = response[stat_name];
		if ($(this).prop("tagName") == "IMG") {
			$(this).attr("src", stat_value);
		} else {
			$(this).html(stat_value);
		}
	});
	spinIcon(element);
	loader.css("display", "none");
}

function spinIcon(icon) {
	icon.toggleClass("fa-spin").toggleClass("orange");
}

function filterStats(filter, container) {
	container.children(".statBox").css("display", "none");
	container.children(".statBox" + filter).css("display", "inline-block");
}

function refreshAll() {
	$(".loader").css("display", "block");
	$(".refresher").each(function(){
		spinIcon($(this));
	});
	$.ajax({
		url: "stats.php",
		dataType: "json",
		type: "post",
		data: {stats: "all"},
		success: function(response) {
			$(".refresher").each(function(){
				var pane = $(this).closest(".contentPane");
				var loader = pane.find(".container").find(".loader");
				updateData($(this), pane, loader, response);
			});
		}
	});
}

function updateTracking() {
	$.ajax({
		url: "stats.php",
		type: "post",
		data: {"tracking": "data"},
		success: function(response) {
			if (response.substring(0, 5) !== "error") {
				$("#tracking").html(response);
			} else {
				//$("#tracking").html(response);
				console.log(response);
			}
		}
	});
}

//Taken from: http://stackoverflow.com/questions/7831712/jquery-sort-divs-by-innerhtml-of-children
function sortUsingNestedText(parent, childSelector, keySelector) {
	parent.each(function(){
		var items = $(this).children(childSelector).sort(function(a, b) {
			var vA = $(keySelector, a).text();
			if (vA.match(/[0-9]*\.[0-9]*[%]|---/)) {
				vA = vA.replace(/%|---/, "");
			}
			var vB = $(keySelector, b).text();
			if (vB.match(/[0-9]*\.[0-9]*[%]|---/)) {
				vB = vB.replace(/%|---/, "");
			}
			var result;
			if (!isNaN(Number(vA)) && !isNaN(Number(vB))) {
				vA = Number(vA);
				vB = Number(vB);
				result = (vA > vB) ? -1 : (vA < vB) ? 1 : 0;
			} else {
				result = (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
			}
			return result;
		});
		$(this).append(items);
	});
}
