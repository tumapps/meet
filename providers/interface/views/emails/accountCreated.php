<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Account Created</title>
    <style>
        body {
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .email-container {
            background-color: #ffffff;
            margin: 20px auto;
            padding: 20px;
            max-width: 600px;
            border-radius: 8px;
            border: 1px solid #dddddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            text-align: center;
            padding-bottom: 20px;
        }

        .email-header h2 {
            margin: 0;
            color: #333333;
            font-size: 24px;
        }

        .email-body {
            font-size: 16px;
            color: #555555;
        }

        .email-body p {
            line-height: 1.5;
        }

        .login-details {
            margin: 20px 0;
            padding-left: 20px;
            border-left: 4px solid #007bff;
        }

        .login-details p {
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 15px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .email-footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #999999;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .capitalize {
            text-transform: capitalize;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Welcome to <?= \Yii::$app->name; ?></h2>
        </div>
        <div class="email-body">
            <p>Dear <span class="capitilize"><?= htmlspecialchars($contact_name) ?>,</span> </p>
            <p>Your account has been successfully created on our system. You can now log in using the details below:</p>

            <div class="login-details">
                <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
                <p><strong>Password:</strong> <?= htmlspecialchars($username) ?> (same as your username)</p>
            </div>

            <p>To access your account, click the link below:</p>
            <a href="<?= htmlspecialchars($loginLink) ?>" class="button">Log In</a>

            <p>We strongly recommend updating your password after logging in to ensure your account remains secure.</p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= \Yii::$app->name; ?>. All rights reserved.</p>
        </div>
    </div>
</body>

</html>