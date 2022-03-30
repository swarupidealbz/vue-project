<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- <link rel="icon" href="<%= BASE_URL %>favicon.ico"> -->

  <title>{{ env('APP_NAME') }}</title>

  <!-- Splash Screen/Loader Styles -->
  <link rel="stylesheet" type="text/css" href="{{ asset(mix('css/loader.css')) }}" />

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset(mix('css/app.css')) }}">

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap"
    rel="stylesheet">
</head>

<body>
  <noscript>
    <strong>We're sorry but {{ env('APP_NAME') }} doesn't work properly without
      JavaScript enabled. Please enable it to continue.</strong>
  </noscript>
  <div id="loading-bg">
    <div class="loading-logo">
    <svg
      viewBox="0 0 139 95"
      version="1.1"
      xmlns="http://www.w3.org/2000/svg"
      xmlns:xlink="http://www.w3.org/1999/xlink"
      height="24"
    >
      <defs>
        <linearGradient
          id="linearGradient-1"
          x1="100%"
          y1="10.5120544%"
          x2="50%"
          y2="89.4879456%"
        >
          <stop
            stop-color="#000000"
            offset="0%"
          />
          <stop
            stop-color="#FFFFFF"
            offset="100%"
          />
        </linearGradient>
        <linearGradient
          id="linearGradient-2"
          x1="64.0437835%"
          y1="46.3276743%"
          x2="37.373316%"
          y2="100%"
        >
          <stop
            stop-color="#EEEEEE"
            stop-opacity="0"
            offset="0%"
          />
          <stop
            stop-color="#FFFFFF"
            offset="100%"
          />
        </linearGradient>
      </defs>
      <g
        id="Page-1"
        stroke="none"
        stroke-width="1"
        fill="none"
        fill-rule="evenodd"
      >
        <g
          id="Artboard"
          transform="translate(-400.000000, -178.000000)"
        >
          <g
            id="Group"
            transform="translate(400.000000, 178.000000)"
          >
            <path
              id="Path"
              class="text-primary"
              d="M-5.68434189e-14,2.84217094e-14 L39.1816085,2.84217094e-14 L69.3453773,32.2519224 L101.428699,2.84217094e-14 L138.784583,2.84217094e-14 L138.784199,29.8015838 C137.958931,37.3510206 135.784352,42.5567762 132.260463,45.4188507 C128.736573,48.2809251 112.33867,64.5239941 83.0667527,94.1480575 L56.2750821,94.1480575 L6.71554594,44.4188507 C2.46876683,39.9813776 0.345377275,35.1089553 0.345377275,29.8015838 C0.345377275,24.4942122 0.230251516,14.560351 -5.68434189e-14,2.84217094e-14 Z"
              style="fill:currentColor"
            />
            <path
              id="Path1"
              d="M69.3453773,32.2519224 L101.428699,1.42108547e-14 L138.784583,1.42108547e-14 L138.784199,29.8015838 C137.958931,37.3510206 135.784352,42.5567762 132.260463,45.4188507 C128.736573,48.2809251 112.33867,64.5239941 83.0667527,94.1480575 L56.2750821,94.1480575 L32.8435758,70.5039241 L69.3453773,32.2519224 Z"
              fill="url(#linearGradient-1)"
              opacity="0.2"
            />
            <polygon
              id="Path-2"
              fill="#000000"
              opacity="0.049999997"
              points="69.3922914 32.4202615 32.8435758 70.5039241 54.0490008 16.1851325"
            />
            <polygon
              id="Path-21"
              fill="#000000"
              opacity="0.099999994"
              points="69.3922914 32.4202615 32.8435758 70.5039241 58.3683556 20.7402338"
            />
            <polygon
              id="Path-3"
              fill="url(#linearGradient-2)"
              opacity="0.099999994"
              points="101.428699 0 83.0667527 94.1480575 130.378721 47.0740288"
            />
          </g>
        </g>
      </g>
    </svg>
    </div>
    <div class="loading">
      <div class="effect-1 effects"></div>
      <div class="effect-2 effects"></div>
      <div class="effect-3 effects"></div>
    </div>
  </div>
  <div id="app">
  </div>

  <script src="{{ asset(mix('js/app.js')) }}"></script>
  <!-- User segmentation start -->
<script>
let user = localStorage.getItem('userData');
if(user) {
  user = JSON.parse(user)
  if(user.role) {
    window.usetifulTags = { 
      role : user.role,     
    };
  }
}
console.log(window.usetifulTags)
</script>
<!-- User segmentation end -->
<!-- Usetiful script start -->
            <script>
(function (w, d, s) {
    var a = d.getElementsByTagName('head')[0];
    var r = d.createElement('script');
    r.async = 1;
    r.src = s;
    r.setAttribute('id', 'usetifulScript');
    r.dataset.token = "c048724c130f5e808988bc239714f85a";
                        a.appendChild(r);
  })(window, document, "https://www.usetiful.com/dist/usetiful.js");</script>

<!-- Usetiful script end -->


</body>

</html>
