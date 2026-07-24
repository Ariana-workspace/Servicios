<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'%3E%3Crect x='2' y='7' width='20' height='13' rx='2' fill='%230d0f1c' stroke='%236c8cff' stroke-width='1.8'/%3E%3Cpath d='M8 7V5.5C8 4.67 8.67 4 9.5 4H14.5C15.33 4 16 4.67 16 5.5V7' stroke='%236c8cff' stroke-width='1.8'/%3E%3Cpath d='M2 12H22' stroke='%236c8cff' stroke-width='1.8'/%3E%3C/svg%3E">
    <title>AriWorkplace</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at top, #1b1f3b, #0d0f1c);
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 18px 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.04);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(6px);
        }

        .logo-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6c8cff;
            box-shadow: 0 0 10px #6c8cff;
        }

        .brand {
            color: #fff;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 50px 70px;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }

        .card span {
            display: block;
            color: #9aa7d1;
            font-size: 0.85rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        h2 {
            color: #fff;
            font-size: 2.2rem;
            font-weight: 600;
            background: linear-gradient(90deg, #6c8cff, #9d6cff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        footer {
            position: absolute;
            bottom: 20px;
            color: #555c7c;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo-dot"></div>
        <span class="brand">AriWorkplace</span>
    </div>

    <div class="card">
        <span>Bienvenido a</span>
        <?php
        $saludo = "<h2>Hola mundo</h2>";
            echo $saludo;
        ?>
    </div>

    <footer>&copy; <?php echo date("Y"); ?> AriWorkplace</footer>
</body>
</html>