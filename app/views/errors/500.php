<?php
/**
 * 500 Error Page Template
 */
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Xətası - 500</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 72px;
            margin: 0;
            color: #dc3545;
        }
        h2 {
            font-size: 28px;
            margin: 15px 0;
            color: #343a40;
        }
        p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #6c757d;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <h1>500</h1>
            <h2>Server Xətası</h2>
            <p>Üzr istəyirik, sorğunuz zamanı serverdə xəta baş verdi. Texniki komanda məlumatlandırılıb.</p>
            <a href="/" class="btn btn-primary">Ana Səhifəyə Qayıt</a>
        </div>
    </div>
</body>
</html>
