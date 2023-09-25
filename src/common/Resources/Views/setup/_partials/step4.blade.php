{{ html()->form('POST', route('setup.stepprocess', ['step' => $step]))
		->attributes(['accept-charset' => 'UTF-8'])
		->open() }}

<div class="row">
	<div class="col-sm-12">
		{{ html()->button('next', 'submit')->id('next-button')->class('btn btn-sm btn-danger next-button float-end')->style(['width' => '100px']) }}
	</div>
</div>

<h4>Import Content from iSeed files</h4>

<ul>
	@foreach($entities as $entity)
		<li>{{ $entity->entity_key }}</li>
	@endforeach
</ul>


{{ html()->form()->close() }}

