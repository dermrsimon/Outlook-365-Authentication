@extends('layout')

@section('content')
<div id="greeting">
	<?php echo $surname . " " . $givenname . "<br>";
	echo $officeLocation; ?>
</div>
@endsection