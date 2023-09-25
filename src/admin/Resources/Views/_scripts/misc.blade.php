<script>

	document.addEventListener("DOMContentLoaded", function () {

		const expirationButton = document.querySelector('#show_expiration');
		if(expirationButton) {
			expirationButton.addEventListener('change', (event) => {
				if (event.currentTarget.checked) {
					$('.expiration_date').show();
				} else {
					$('.expiration_date').hide();
				}
			});
		}

	});

	function imageDataToggler(id) {

		const infoPanel = document.querySelector("#image_info_" + id);
		const editPanel = document.querySelector("#image_edit_" + id);
		const globalSave = document.querySelector("#globalsave");

		if(infoPanel.style.display == '' || infoPanel.style.display == 'block') {
			infoPanel.style.display = 'none';
			editPanel.style.display = 'block';
			globalSave.disabled = true;
		} else {
			infoPanel.style.display = 'block';
			editPanel.style.display = 'none';
			globalSave.disabled = false;
		}

		// reset other image rows
		editPanels = document.querySelectorAll('.image-edit-panel');
		editPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'none';
			}
		});
		infoPanels = document.querySelectorAll('.image-info-panel');
		infoPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'block';
			}
		});

	}

	function fileDataToggler(id) {

		const infoPanel = document.querySelector("#file_info_" + id);
		const editPanel = document.querySelector("#file_edit_" + id);
		const globalSave = document.querySelector("#globalsave");

		if(infoPanel.style.display == '' || infoPanel.style.display == 'block') {
			infoPanel.style.display = 'none';
			editPanel.style.display = 'block';
			globalSave.disabled = true;
		} else {
			infoPanel.style.display = 'block';
			editPanel.style.display = 'none';
			globalSave.disabled = false;
		}

		// reset other image rows
		editPanels = document.querySelectorAll('.file-edit-panel');
		editPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'none';
			}
		});
		infoPanels = document.querySelectorAll('.file-info-panel');
		infoPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'block';
			}
		});

	}

	function videoDataToggler(id) {

		const infoPanel = document.querySelector("#video_info_" + id);
		const editPanel = document.querySelector("#video_edit_" + id);
		const globalSave = document.querySelector("#globalsave");

		if(infoPanel.style.display == '' || infoPanel.style.display == 'block') {
			infoPanel.style.display = 'none';
			editPanel.style.display = 'block';
			globalSave.disabled = true;
		} else {
			infoPanel.style.display = 'block';
			editPanel.style.display = 'none';
			globalSave.disabled = false;
		}

		// reset other image rows
		editPanels = document.querySelectorAll('.video-edit-panel');
		editPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'none';
			}
		});
		infoPanels = document.querySelectorAll('.video-info-panel');
		infoPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'block';
			}
		});

	}

	function videofileDataToggler(id) {

		const infoPanel = document.querySelector("#videofile_info_" + id);
		const editPanel = document.querySelector("#videofile_edit_" + id);
		const globalSave = document.querySelector("#globalsave");

		if(infoPanel.style.display == '' || infoPanel.style.display == 'block') {
			infoPanel.style.display = 'none';
			editPanel.style.display = 'block';
			globalSave.disabled = true;
		} else {
			infoPanel.style.display = 'block';
			editPanel.style.display = 'none';
			globalSave.disabled = false;
		}

		// reset other image rows
		editPanels = document.querySelectorAll('.videofile-edit-panel');
		editPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'none';
			}
		});
		infoPanels = document.querySelectorAll('.videofile-info-panel');
		infoPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'block';
			}
		});

	}

	function larawidgetDataToggler(id) {

		const infoPanel = document.querySelector("#larawidget_info_" + id);
		const editPanel = document.querySelector("#larawidget_edit_" + id);
		const globalSave = document.querySelector("#globalsave");

		if(infoPanel.style.display == '' || infoPanel.style.display == 'flex') {
			infoPanel.style.display = 'none';
			editPanel.style.display = 'block';
			globalSave.disabled = true;
		} else {
			infoPanel.style.display = 'flex';
			editPanel.style.display = 'none';
			globalSave.disabled = false;
		}

		// reset other image rows
		editPanels = document.querySelectorAll('.larawidget-edit-panel');
		editPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'none';
			}
		});
		infoPanels = document.querySelectorAll('.larawidget-info-panel');
		infoPanels.forEach((panel) => {
			let array = panel.id.split("_");
			let panelId = array.pop();
			if(panelId != id) {
				panel.style.display = 'flex';
			}
		});
	}
</script>

