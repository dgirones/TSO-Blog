/**
 * Mount Google Maps embed on contact map placeholders.
 */
(function () {
	'use strict';

	function mountMap(el) {
		var url = el.getAttribute('data-map-src');
		var title = el.getAttribute('data-map-title') || '';
		if (!url) {
			return;
		}
		var frame = document.createElement('iframe');
		frame.className = 'tso-contact-map-frame';
		frame.setAttribute('title', title);
		frame.setAttribute('src', url);
		frame.setAttribute('width', '100%');
		frame.setAttribute('height', '420');
		frame.setAttribute('loading', 'lazy');
		frame.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
		frame.setAttribute('allowfullscreen', '');
		frame.style.border = '0';
		el.appendChild(frame);
	}

	function init() {
		var nodes = document.querySelectorAll('.tso-contact-map[data-map-src]');
		for (var i = 0; i < nodes.length; i++) {
			mountMap(nodes[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
