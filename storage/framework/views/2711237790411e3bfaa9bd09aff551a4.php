<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #ffffff;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #111827;
        }

        .content {
            width: min(720px, 100%);
            text-align: center;
        }

        .content img {
            width: min(420px, 100%);
            height: auto;
            display: block;
            margin: 0 auto 18px;
        }

        .title {
            margin: 0 0 8px;
            font-size: clamp(24px, 4vw, 34px);
            color: #111827;
        }

        .message {
            margin: 0;
            font-size: clamp(16px, 2.3vw, 20px);
            color: #111827;
        }
    </style>
</head>
<body>
    <main class="content">
        <img src="<?php echo e(asset('images/AccesoDenegado.png')); ?>" alt="Acceso denegado">
        <h1 class="title">Acceso denegado</h1>
        <p class="message">Estas en zona restrigida, Se te estara redireccionando en <span id="countdown">5</span> segundos.</p>
    </main>

    <script>
        var secondsLeft = 5;
        var countdownEl = document.getElementById('countdown');

        var timer = setInterval(function () {
            secondsLeft -= 1;
            countdownEl.textContent = String(secondsLeft);

            if (secondsLeft <= 0) {
                clearInterval(timer);
                window.location.href = <?php echo json_encode($redirectUrl, 15, 512) ?>;
            }
        }, 1000);
    </script>
</body>
</html>
<?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/errors/access-denied.blade.php ENDPATH**/ ?>