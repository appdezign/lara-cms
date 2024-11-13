{{ html()->form('POST', route('setup.stepprocess', ['step' => $step]))
		->attributes(['accept-charset' => 'UTF-8'])
		->open() }}

<div class="row">
	<div class="col-sm-12">
		{{ html()->button('save', 'submit')->id('next-button')->class('btn btn-sm btn-danger next-button float-end')->style(['width' => '100px']) }}
	</div>
</div>

<h4>Users</h4>

@foreach($users as $user)
	<div class="row py-4">
		<div class="col-3 pt-2 text-end">
			{{ $user->username }}
		</div>
		<div class="col-9">
			{{ html()->text('_user_' . $user->id, null)->class('form-control')->placeholder('password') }}
		</div>
	</div>
@endforeach


{{ html()->form()->close() }}

