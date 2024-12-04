<!-- TinyMCE -->
<script src="{{ asset('assets/admin/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>

<script>
	var editor_config_full = {
		path_absolute: "/",
		selector: "textarea.tiny",
		height : "400",
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime nonbreaking save table contextmenu directionality",
			"template paste textcolor colorpicker textpattern"
		],
		toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link",
		menubar: 'edit insert view format table tools help',
		convert_urls: false,
		relative_urls: false,
		paste_as_text: true,
		extended_valid_elements: 'i[class]',
		rel_list: [
			{title: 'None', value: ''},
			{title: 'Alternate', value: 'alternate'},
			{title: 'Author', value: 'author'},
			{title: 'Bookmark', value: 'bookmark'},
			{title: 'External', value: 'external'},
			{title: 'Help', value: 'help'},
			{title: 'License', value: 'license'},
			{title: 'Next', value: 'next'},
			{title: 'Nofollow', value: 'nofollow'},
			{title: 'Noreferrer', value: 'noreferrer'},
			{title: 'Noopener', value: 'noopener'},
			{title: 'Prev', value: 'prev'},
			{title: 'Search', value: 'search'},
			{title: 'Tag', value: 'tag'},
		],
		templates: [
			{
				title: '2 kolommen [-][-]',
				description: '2 responsive kolommen [-][-]',
				content: '<p>[kolom_1van2]</p><p>...</p><p>[/kolom_1van2]</p><p>[kolom_2van2]</p><p>...</p><p>[/kolom_2van2]</p>'
			},
			{
				title: '2 kolommen [-][- -]',
				description: '2 responsive kolommen ([-][- -])',
				content: '<p>[kolom_1van3]</p><p>...</p><p>[/kolom_1van3]</p><p>[kolom_23van3]</p><p>...</p><p>[/kolom_23van3]</p>'
			},
			{
				title: '2 kolommen [- -][-]',
				description: '2 responsive kolommen ([- -][-])',
				content: '<p>[kolom_12van3]</p><p>...</p><p>[/kolom_12van3]</p><p>[kolom_3van3]</p><p>...</p><p>[/kolom_3van3]</p>'
			},
			{
				title: '2 kolommen [-][- - -]',
				description: '2 responsive kolommen ([-][- - -])',
				content: '<p>[kolom_1van4]</p><p>...</p><p>[/kolom_1van4]</p><p>[kolom_234van4]</p><p>...</p><p>[/kolom_234van4]</p>'
			},
			{
				title: '2 kolommen [- - -][-]',
				description: '2 responsive kolommen [- - -][-]',
				content: '<p>[kolom_123van4]</p><p>...</p><p>[/kolom_123van4]</p><p>[kolom_4van4]</p><p>...</p><p>[/kolom_4van4]</p>'
			},
			{
				title: '3 kolommen [-][-][-]',
				description: '3 responsive kolommen [-][-][-]',
				content: '<p>[kolom_1van3]</p><p>...</p><p>[/kolom_1van3]</p><p>[kolom_2van3]</p><p>...</p><p>[/kolom_2van3]</p><p>[kolom_3van3]</p><p>...</p><p>[/kolom_3van3]</p>'
			},
			{
				title: '4 kolommen [-][-][-][-]',
				description: '4 responsive kolommen [-][-][-][-]',
				content: '<p>[kolom_1van4]</p><p>...</p><p>[/kolom_1van4]</p><p>[kolom_2van4]</p><p>...</p><p>[/kolom_2van4]</p><p>[kolom_3van4]</p><p>...</p><p>[/kolom_3van4]</p><p>[kolom_4van4]</p><p>...</p><p>[/kolom_4van4]</p>'
			},
		],
		file_picker_callback : function(callback, value, meta) {
			var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
			var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

			var cmsURL = editor_config_full.path_absolute + 'laravel-filemanager?editor=' + meta.fieldname;
			if (meta.filetype == 'image') {
				cmsURL = cmsURL + "&type=Images";
			} else {
				cmsURL = cmsURL + "&type=Files";
			}

			tinyMCE.activeEditor.windowManager.openUrl({
				url : cmsURL,
				title : 'Filemanager',
				width : x * 0.8,
				height : y * 0.8,
				resizable : "yes",
				close_previous : "no",
				onMessage: (api, message) => {
					callback(message.content);
				}
			});
		},
	};

	tinymce.init(editor_config_full);
</script>


<script>
	var editor_config_min = {
		path_absolute: "/",
		selector: "textarea.tinymin",
		height : "200",
		plugins: [
			"advlist autolink lists link image charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime media nonbreaking save table contextmenu directionality",
			"template paste textcolor colorpicker textpattern"
		],
		toolbar: "code | styleselect | bold italic | alignleft aligncenter alignright alignjustify",
		convert_urls: false,
		relative_urls: false,
		menubar: false,
		paste_as_text: true,

	};

	tinymce.init(editor_config_min);
</script>
