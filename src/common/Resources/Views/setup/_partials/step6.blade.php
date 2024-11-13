{{ html()->form('POST', route('setup.stepprocess', ['step' => $step]))
		->attributes(['accept-charset' => 'UTF-8'])
		->open() }}

<div class="row">
	<div class="col-sm-12">
		{{ html()->button('next', 'submit')->id('next-button')->class('btn btn-sm btn-danger next-button float-end')->style(['width' => '100px']) }}
	</div>
</div>

<h4>Finish Setup</h4>

<ul>
	<li>Save configuration to ENV</li>
	<li>Redirect to Dashboard</li>
</ul>


{{ html()->form()->close() }}

