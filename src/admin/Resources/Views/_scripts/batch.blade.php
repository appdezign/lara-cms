<script>

	$(document).ready(function () {

		// check all (JS)
		const checkAll = document.querySelector('input.js-check-all');
		const checkboxes = document.querySelectorAll('input.js-check');

		const indexToolbar = document.querySelector('.index-toolbar');
		const indexBatch = document.querySelector('.index-batch');

		if (checkAll && checkboxes) {
			checkAll.addEventListener('change', (event) => {
				if (event.currentTarget.checked) {
					checkboxes.forEach((chbx) => {
						chbx.checked = true;
					});
					if(indexToolbar) {
						indexToolbar.classList.add('d-none');
					}
					if(indexBatch) {
						indexBatch.classList.remove('d-none');
					}
				} else {
					checkboxes.forEach((chbx) => {
						chbx.checked = false;
					});
					if(indexToolbar) {
						indexToolbar.classList.remove('d-none');
					}
					if(indexBatch) {
						indexBatch.classList.add('d-none');
					}
				}
			});

			checkboxes.forEach((item) => {
				item.addEventListener('change', function (event) {
					let checkedCount = 0;
					checkboxes.forEach((chbx) => {
						if (chbx.checked) {
							checkedCount++;
						}
					});
					if (checkedCount < checkboxes.length) {
						checkAll.checked = false;
					} else {
						checkAll.checked = true;
					}
					if (checkedCount > 0) {
						if(indexToolbar) {
							indexToolbar.classList.add('d-none');
						}
						if(indexBatch) {
							indexBatch.classList.remove('d-none');
						}
					} else {
						if(indexToolbar) {
							indexToolbar.classList.remove('d-none');
						}
						if(indexBatch) {
							indexBatch.classList.add('d-none');
						}
					}
				});
			});
		}

		const batchDeleteButton = document.querySelector('input[name="batchdelete"]');
		if (batchDeleteButton) {
			batchDeleteButton.addEventListener('click', e => {
				e.preventDefault();
				Swal.fire({
					title: '{{ _lanq('lara-admin::default.message.are_you_sure') }}',
					text: '{{ _lanq('lara-admin::default.message.batch_warning_delete') }}',
					type: 'warning',
					showCancelButton: true,
					confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
					cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}",
				}).then((result) => {
					if (result.isConfirmed) {
						$('#batchform').append("<input type='hidden' name='submitValue' value='batchdelete' />");
						$('#batchform').submit();
					}
				});
			});
		}


		const batchPublishButton = document.querySelector('input[name="batchpublish"]');
		if (batchPublishButton) {
			batchPublishButton.addEventListener('click', e => {
				e.preventDefault();
				Swal.fire({
					title: '{{ _lanq('lara-admin::default.message.are_you_sure') }}',
					text: '{{ _lanq('lara-admin::default.message.batch_warning_published') }}',
					type: 'warning',
					showCancelButton: true,
					confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
					cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}",
				}).then((result) => {
					if (result.isConfirmed) {
						$('#batchform').append("<input type='hidden' name='submitValue' value='batchpublish' />");
						$('#batchform').submit();
					}
				});
			});
		}

		const batchUnPublishButton = document.querySelector('input[name="batchunpublish"]');
		if (batchUnPublishButton) {
			batchUnPublishButton.addEventListener('click', e => {
				e.preventDefault();
				Swal.fire({
					title: '{{ _lanq('lara-admin::default.message.are_you_sure') }}',
					text: '{{ _lanq('lara-admin::default.message.batch_warning_unpublished') }}',
					type: 'warning',
					showCancelButton: true,
					confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
					cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}",
				}).then((result) => {
					if (result.isConfirmed) {
						$('#batchform').append("<input type='hidden' name='submitValue' value='batchunpublish' />");
						$('#batchform').submit();
					}
				});
			});
		}

		// entity builder
		const batchSaveAllButton = document.querySelector('input[name="saveall"]');
		if (batchSaveAllButton) {
			batchSaveAllButton.addEventListener('click', e => {
				e.preventDefault();
				Swal.fire({
					title: '{{ _lanq('lara-admin::default.message.are_you_sure') }}',
					text: '{{ _lanq('lara-admin::default.message.batch_warning_saveall') }}',
					type: 'warning',
					showCancelButton: true,
					confirmButtonText: "{{ strtoupper(_lanq('lara-admin::default.alert.confirm')) }}",
					cancelButtonText: "{{ ucfirst(_lanq('lara-admin::default.alert.cancel')) }}",
				}).then((result) => {
					if (result.isConfirmed) {
						$('#batchform').append("<input type='hidden' name='submitValue' value='saveall' />");
						$('#batchform').submit();
					}
				});
			});
		}

	});

</script>