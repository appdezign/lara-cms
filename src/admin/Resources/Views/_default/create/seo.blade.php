@if($entity->hasSeo())
	{{ html()->hidden('_seo_focus', '') }}
	{{ html()->hidden('_seo_title', '') }}
	{{ html()->hidden('_seo_description', '') }}
	{{ html()->hidden('_seo_keywords', '') }}
@endif




