<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Primary Meta Tags -->
    <title>{{ $meta['title'] }}</title>
    <meta name="title" content="{{ $meta['title'] }}">
    <meta name="description" content="{{ $meta['description'] }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $meta['type'] }}">
    <meta property="og:url" content="{{ $meta['url'] }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:image" content="{{ $meta['image'] }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $meta['url'] }}">
    <meta property="twitter:title" content="{{ $meta['title'] }}">
    <meta property="twitter:description" content="{{ $meta['description'] }}">
    <meta property="twitter:image" content="{{ $meta['image'] }}">

    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
        body {
            background-color: #000;
            color: white;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            max-width: 400px;
            padding: 20px;
        }
        .cover {
            width: 250px;
            height: 250px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            margin-bottom: 30px;
        }
        h1 {
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 10px;
        }
        p {
            color: #888;
            margin-bottom: 30px;
        }
        .btn {
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: bold;
            display: inline-block;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ $meta['image'] }}" alt="Cover" class="cover">
        <h1>{{ $meta['title'] }}</h1>
        <p>{{ $meta['description'] }}</p>
        <a href="{{ $meta['url'] }}" class="btn">Play on Echo Panda</a>
    </div>

    <script>
        // Deep Link logic
        const type = "{{ $meta['type'] }}".split('.')[1] || "{{ $meta['type'] }}";
        const id = "{{ $meta['url'] }}".split('/').pop();
        const deepLink = `echopanda://${type}/${id}`;

        // Try to open app if mobile
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        const isBot = /bot|googlebot|crawler|spider|robot|crawling/i.test(navigator.userAgent);

        if (isMobile && !isBot) {
            window.location.href = deepLink;
            // Fallback to web app after a short delay if app not installed
            setTimeout(() => {
                window.location.href = "{{ $meta['url'] }}";
            }, 2500);
        } else if (!isBot) {
            // Automatic redirect to web app for desktop if not a bot
            setTimeout(() => {
                window.location.href = "{{ $meta['url'] }}";
            }, 1000);
        }
    </script>
</body>
</html>
