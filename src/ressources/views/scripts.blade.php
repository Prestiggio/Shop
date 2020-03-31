<script type="text/javascript" src="/languages/{{ str_replace('_', '-', app()->getLocale()) }}.js"></script>
@if(env('APP_ENV')=='local')
<script type="text/javascript" src="/themes/{{$theme}}/medias/js/{{$theme}}.amelior.js"></script>
@else
<script type="text/javascript" src="/rythemes/{{$theme}}/medias/js/vendors~{{$theme}}.amelior.js"></script>
<script type="text/javascript" src="/rythemes/{{$theme}}/medias/js/{{$theme}}.amelior.js"></script>
@endif
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-112432596-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-112432596-1');
</script>