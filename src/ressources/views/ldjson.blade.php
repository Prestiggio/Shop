<?php 
$__vars = get_defined_vars();
$__ar = [];
foreach($__vars as $k => $v) {
    if(preg_match("/^__/", $k))
        continue;
    $__ar[$k] = $v;
}
if(isset($errors)) {
    $__ar['errors'] = $errors->getBags();
}
?>
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
<title>{{ config('app.name', 'Laravel') }} : {{ $page["title"] }}</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="viewport"
	content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="apple-touch-icon" sizes="180x180"
	href="{{$site->nsetup['general']['favicon']}}">
<link rel="icon" type="image/png" sizes="32x32"
	href="{{$site->nsetup['general']['favicon']}}">
<link rel="icon" type="image/png" sizes="16x16"
	href="{{$site->nsetup['general']['favicon']}}">
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="{{$site->nsetup['general']['favicon']}}" color="#5bbad5">
<!--Google web font-->
<link href='https://fonts.googleapis.com/css?family=Roboto:100,400,700|Tangerine|Lato:400,700,400italic' rel='stylesheet' type='text/css' />
<!-- Font Awsome -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
@include("ryshop::styles")
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
</head>
<body>
	<div id="app">
		<script type="application/ld+json">
            {!!json_encode($__ar)!!}
        </script>
	</div>
	@include("ryshop::scripts")
</body>
</html>