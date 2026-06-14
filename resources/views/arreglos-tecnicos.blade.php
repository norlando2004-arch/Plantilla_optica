<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arreglos Tecnicos</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            padding: 24px;
        }

        .maintenance {
            width: min(900px, 100%);
            text-align: center;
        }

        .maintenance img {
            width: min(760px, 92vw);
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .maintenance p {
            margin: 18px 0 0;
            font-size: clamp(1rem, 2.4vw, 1.45rem);
            line-height: 1.5;
            color: #111111;
        }
    </style>
</head>
<body>
    <main class="maintenance">
        <img src="{{ asset('images/Construccion.png') }}" alt="Sitio en arreglos tecnicos">
        <p>Estamos en Proceso de cambio de view, intente entrar mas tarde.</p>
    </main>
</body>
</html>
