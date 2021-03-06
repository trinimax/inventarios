@if($errors->any())
	<div class="alert alert-dismissable alert-danger">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<ul>
			{{ implode('', $errors->all('<li class="error">:message</li>')) }}
		</ul>
	</div>
@endif

@if(Session::has('message'))
	<div class="alert alert-dismissable alert-danger">
	  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		{{ Session::get('message') }}
	</div>
@endif

@if(Session::has('messageOk'))
	<div class="alert alert-dismissable alert-success">
	  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		{{ Session::get('messageOk') }}
	</div>
@endif

<?php
/*
 * Otra forma de mostrar los errores.
 */
/*
<div class="span4 well">
	@if(isset($errors) && count($errors->all()) > 0)
		<ul>
			@foreach ($errors->all('<li>:message</li>') as $message)
			{{ $message }}
			@endforeach
		</ul>
	@endif
</div>
 */
?>
