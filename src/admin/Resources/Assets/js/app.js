
// use Bootstrap 5 validation
(function () {
	'use strict'
	const forms = document.querySelectorAll('.needs-validation');
	const saveButton = document.querySelector('button.save-button');

	Array.prototype.slice.call(forms)
		.forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!form.checkValidity()) {
					event.preventDefault()
					event.stopPropagation()
				} else {
					saveButton.innerHTML = '<i class="fas fa-spin fa-circle-notch p-0"></i>';
				}
				form.classList.add('was-validated')
			}, false)
		})
})();

// slug Toggle
const slugToggle = document.getElementById('slug-toggle');
const slugReset = document.getElementById('slug-reset');
if(slugToggle) {
	slugToggle.addEventListener('click', e => {
		if(slugReset) {
			if (slugReset.style.display == 'none') {
				slugReset.style.display = 'block';
				slugToggle.innerHTML = 'hide';
			} else {
				slugReset.style.display = 'none';
				slugToggle.innerHTML = 'edit';
			}
		}
	});
}

// Datepicker
flatpickr("#dtp-publish-from", {
	enableTime: true,
	dateFormat: "Y-m-d H:i",
	time_24hr: true,
	wrap: true,
});

flatpickr("#dtp-publish-to", {
	enableTime: true,
	dateFormat: "Y-m-d H:i",
	time_24hr: true,
	wrap: true,
});

// Alerts
const myAlerts = document.querySelectorAll('div.alert:not(.alert-important)');
myAlerts.forEach((myAlert) => {
	setTimeout(() => {
		myAlert.classList.add('hide')
	}, 4000);
});