/* Native page editor controls for the CV template. */
(function () {
	'use strict';

	document.addEventListener('click', function (event) {
		var addButton = event.target.closest('[data-cv-add]');
		if (addButton) {
			var repeater = addButton.closest('[data-cv-repeater]');
			var template = repeater.querySelector('[data-cv-template]');
			var items = repeater.querySelector('[data-cv-items]');
			var index = parseInt(repeater.getAttribute('data-next-index'), 10) || 0;
			var token = repeater.getAttribute('data-index-token');
			var html = template.innerHTML.split(token).join(String(index));
			repeater.setAttribute('data-next-index', String(index + 1));
			items.insertAdjacentHTML('beforeend', html);
			return;
		}

		var removeButton = event.target.closest('[data-cv-remove]');
		if (removeButton) {
			removeButton.closest('[data-cv-item]').remove();
			return;
		}

		var pdfRemove = event.target.closest('[data-cv-pdf-remove]');
		if (pdfRemove) {
			document.querySelector('[data-cv-pdf-id]').value = '0';
			document.querySelector('[data-cv-pdf-blog-id]').value = '0';
			document.querySelector('[data-cv-pdf-status]').textContent = 'Using the PDF bundled with the theme. Choose a PDF from the Media Library to replace it.';
			return;
		}

		var pdfSelect = event.target.closest('[data-cv-pdf-select]');
		if (!pdfSelect || !window.wp || !wp.media) {
			return;
		}

		var frame = wp.media({
			title: pdfSelect.getAttribute('data-title'),
			button: { text: pdfSelect.getAttribute('data-button') },
			library: { type: 'application/pdf' },
			multiple: false
		});
		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			if (!attachment || 'application/pdf' !== attachment.mime) {
				return;
			}
			document.querySelector('[data-cv-pdf-id]').value = String(attachment.id);
			document.querySelector('[data-cv-pdf-blog-id]').value = pdfSelect.getAttribute('data-blog-id');
			document.querySelector('[data-cv-pdf-status]').textContent = 'Selected PDF: ' + attachment.filename;
		});
		frame.open();
	});
}());
