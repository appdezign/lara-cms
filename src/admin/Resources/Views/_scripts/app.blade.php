<script type="text/javascript">

	document.addEventListener("DOMContentLoaded", function () {

		@if(isset($entity) && $entity->getMethod() == 'edit')

			// preserve tab state

			const tabElements = document.querySelectorAll('button[data-bs-toggle="tab"]:not(.subtabs)')
			tabElements.forEach((tabEl) => {
				tabEl.addEventListener('shown.bs.tab', function (event) {
					let bsTarget = event.target.dataset.bsTarget;
					sessionStorage.setItem('lastTab', bsTarget);
				})
			});
			let lastTab = sessionStorage.getItem('lastTab');
			if (lastTab) {
				const activeTabElement = document.querySelector('[data-bs-target="' + lastTab + '"]');
				if(activeTabElement) {
					const activeTab = new bootstrap.Tab(activeTabElement)
					activeTab.show()
				} else {
					// activate first tab
					const activeTabElement = document.querySelector('.js-first-tab');
					const activeTab = new bootstrap.Tab(activeTabElement)
					activeTab.show()
				}
			} else {
				// activate first tab
				const activeTabElement = document.querySelector('.js-first-tab');
				const activeTab = new bootstrap.Tab(activeTabElement)
				activeTab.show()
			}

			const subTabElements = document.querySelectorAll('button.subtabs[data-bs-toggle="tab"]')
			subTabElements.forEach((subTabEl) => {
				subTabEl.addEventListener('shown.bs.tab', function (event) {
					let bsTarget = event.target.dataset.bsTarget;
					sessionStorage.setItem('lastSubTab', bsTarget);
				})
			});
			let lastSubTab = sessionStorage.getItem('lastSubTab');
			if (lastSubTab) {
				const activeTabElement = document.querySelector('[data-bs-target="' + lastSubTab + '"]');
				const activeSubTab = new bootstrap.Tab(activeTabElement)
				activeSubTab.show()
			}

		@else
			sessionStorage.clear();
		@endif


		@if(isset($data->tab) && !empty($data->tab))
			// activate tab from GET request
			const actTabEl = document.querySelector("[data-bs-target='#tab_{{ $data->tab }}']");
			if(actTabEl) {
				const actTab = new bootstrap.Tab(actTabEl);
				actTab.show();
			}
		@endif

		@if(session('routecacheclear'))
		// Route cache (ajax)
		$.ajax({
			url: "/admin/routecache",
			success: function (data) {
				console.log(data.payload);
			}
		});
		@endif

	});

</script>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		const langSwitchers = document.querySelectorAll('.js-lang-switcher');
		langSwitchers.forEach((langSwitcher) => {
			langSwitcher.addEventListener('click', function (event) {
				document.body.classList.add("page-loading");
				document.body.setAttribute("data-kt-app-page-loading","on");
			})
		});
	});
</script>




