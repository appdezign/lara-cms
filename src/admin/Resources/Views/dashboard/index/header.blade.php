<?php
// Lara Updates
$laraUpdCount = sizeof($data->updates->lara);
$hasLaraUpdates = $laraUpdCount > 0;

// Eve Updates
$eveUpdCount = sizeof($data->updates->eve);
$hasEveUpdates = $eveUpdCount > 0;

// Translation Updates
$translationUpdCount = sizeof($data->updates->translation);
$hasTranslationUpdates = $translationUpdCount > 0;
?>

<!--begin::content-header-->
@if($hasLaraUpdates || $hasEveUpdates  || $hasTranslationUpdates)
	<div class="row mb-6 fs-5">
		<div class="col-md-4 col-lg-3 offset-lg-1 fs-6">
			@if($hasLaraUpdates)
				Lara updates available: <span class="color-primary">{{ $laraUpdCount }}</span>
				<br>
				<a href="{{ route('admin.dashboard.index', ['update-lara' => 'true']) }}"
				   class="color-danger fs-6 text-decoration-underline">update now</a>
			@endif
		</div>
		<div class="col-md-4 col-lg-3 fs-6 text-center">
			@if($hasTranslationUpdates)
				Translations available: <span class="color-primary">{{ $translationUpdCount }}</span>
				<br>
				@if($hasLaraUpdates)
					<span class="text-muted">update now</span>
				@else
					<a href="{{ route('admin.dashboard.index', ['update-translation' => 'true']) }}"
					   class="color-danger fs-6 text-decoration-underline">update now</a>
				@endif
			@endif
		</div>
		<div class="col-md-4 col-lg-4 fs-6 text-end">
			@if($hasEveUpdates)
				App updates available: <span class="color-primary">{{ $eveUpdCount }}</span>
				<br>
				@if($hasLaraUpdates || $hasTranslationUpdates)
					<span class="text-muted">update now</span>
				@else
					<a href="{{ route('admin.dashboard.index', ['update-eve' => 'true']) }}"
					   class="color-danger fs-6 text-decoration-underline">update now</a>
				@endif
			@endif
		</div>

	</div>
@endif
<!--end::content-header-->
