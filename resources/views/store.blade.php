<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ request()->is('admin') ? 'Rang Mahal — Store Administration' : 'Buy Sarees Online in Pakistan | Rang Mahal' }}</title>
    <meta name="description" content="Explore sarees online in Pakistan at Rang Mahal. Discover silk, wedding, party and festive styles inspired by the colours of Pakistani celebrations.">
    <meta property="og:title" content="Rang Mahal — Colours of Tradition">
    <meta property="og:description" content="Rooted in tradition. Worn with love. Discover sarees for Eid gatherings, wedding celebrations and your everyday moments.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_PK">
    <meta property="og:image" content="{{ url('/images/campaign.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url()->current() }}">
    @if(request()->is('admin'))<meta name="robots" content="noindex,nofollow">@endif
    @viteReactRefresh
    @vite('resources/js/app.jsx')
</head>
<body><div id="app" class="{{ request()->is('admin') ? 'admin-app' : 'rang-mahal-store' }}"></div></body>
</html>
