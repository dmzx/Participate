phpbb.addAjaxCallback('participate', function(res) {
	'use strict';
	if (res.success) {
		var $button = $('#participate_button');
		var $bar = $('#participate_bar');
		$button
			.html(res.DMZX_PARTICIPATE_TXT)
			.attr('class', res.DMZX_PARTICIPATE_STATUS_CLASS + " button icon-button")
			.attr('title', res.DMZX_PARTICIPATE_BUTTON_TXT);
		$bar.html(res.PARTICIPANTSBAR);
	}
});