<?php
/* @var $this yii\web\View */
/* @var $username string */
/* @var $resetLink string */

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
    <style>
        /* Add your CSS styles here */
        body {
            font-family: Arial, sans-serif;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 10px;
        }
        .email-header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .bbtn{
            text-align: center;
        }
        .email-body {
            font-size: 16px;
            color: #555;
        }
        .email-footer {
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        a {
            color: #0275d8;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            Password Reset Request
        </div>
        <div class="email-body">
            <p>Hello, <strong><?= $username ?></strong></p>
            <p>You have requested a password reset. Please click the link below to reset your password:</p>
            <button class="btn btn-primary bbtn">
                <a class="link"> href="<?= $resetLink ?>">Reset Password</a>
            </button>
            <p>If you did not request this, please ignore this email.</p>
        </div>
        <div class="email-footer">
            <p>Thank you,<br><span>&copy;</span> <?= date('Y') ?> Tum Tumeet</p>
        </div>
    </div>
</body>
</html>
