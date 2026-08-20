document.addEventListener('click', (event) => {
	const button = event.target.closest('.smart-footnote__button[aria-controls]');

	document.querySelectorAll('.smart-footnote.is-open').forEach((footnote) => {
		if (!button || footnote !== button.parentElement) {
			footnote.classList.remove('is-open');
			footnote.querySelector('.smart-footnote__button').setAttribute('aria-expanded', 'false');
		}
	});

	if (!button) {
		return;
	}

	const footnote = button.parentElement;
	const isOpen = footnote.classList.toggle('is-open');
	button.setAttribute('aria-expanded', String(isOpen));
});

document.addEventListener('keydown', (event) => {
	if (event.key !== 'Escape') {
		return;
	}

	document.querySelectorAll('.smart-footnote.is-open').forEach((footnote) => {
		footnote.classList.remove('is-open');
		footnote.querySelector('.smart-footnote__button').setAttribute('aria-expanded', 'false');
	});
});
