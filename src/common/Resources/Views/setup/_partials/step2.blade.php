{{ html()->form('POST', route('setup.stepprocess', ['step' => $step]))
	->attributes(['accept-charset' => 'UTF-8'])
	->open() }}

<div class="row">
	<div class="col-sm-12">
		{{ html()->button('next', 'submit')->id('next-button')->class('btn btn-sm btn-danger next-button float-end')->style(['width' => '100px']) }}
	</div>
</div>

<h4>Import Entities from iSeed files</h4>

<ul>
	<li>Entities</li>
	<li>Entity Groups</li>
	<li>Entity Fields</li>
	<li>Entity Options</li>
	<li>Entity Settings</li>
	<li>Entity Relations</li>
	<li>Entity Views</li>
</ul>


{{ html()->form()->close() }}