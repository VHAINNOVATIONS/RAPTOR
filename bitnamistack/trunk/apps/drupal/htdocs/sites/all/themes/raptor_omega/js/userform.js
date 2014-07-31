$(document).ready(function () {
	// Generic Cancel button click handler
	$('.admin-cancel-button').on('click', function (e) {
		location.href = $(this).attr('data-redirect');
	});
});