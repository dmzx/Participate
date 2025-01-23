phpbb.addAjaxCallback('participate', function(res) {
	'use strict';
	if (res.success) {
		$('#participate_button').html(res.DMZX_PARTICIPATE_TXT);
		$('#participate_button').attr('class', res.DMZX_PARTICIPATE_STATUS_CLASS + " button icon-button");
		$('#participate_button').attr('title', res.DMZX_PARTICIPATE_BUTTON_TXT);
		$('#participate_bar').html(res.PARTICIPANTSBAR);
	}
});