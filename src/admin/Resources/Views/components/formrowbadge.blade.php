<div class="row form-group">
	<div class="col-12 col-md-2">

		<label for="{{ $labelfield }}">{{ $labeltext }}:</label>

		<a tabindex="0" class="badge lara-info-badge" role="button" data-bs-toggle="popover" data-bs-placement="{{ $badgeplacement }}" data-bs-trigger="focus" data-bs-container="body" title="{{ $badgetitle }}" data-bs-content="{{ $badgecontent }}">?</a>

	</div>
	<div class="col-12 col-md-10 col-lg-9">
		{{ $slot }}
		<div class="help-block with-errors"></div>
	</div>
</div>
