<?php
/* @var $this yii\web\View */
/* @var $username string */
/* @var $appointmentDate string */
/* @var $startTime string */
/* @var $endTime string */
/* @var $contactLink string */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appointment Rescheduled Successfully</title>
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
        }
        .email-header {
            text-align: center;
            padding-bottom: 20px;
        }
        .email-header h2 {
            margin: 0;
            color: #333333;
        }
        .email-body {
            font-size: 16px;
            color: #555555;
        }
        .email-body p {
            line-height: 1.5;
        }
        .appointment-details {
            margin: 20px 0;
            padding-left: 20px;
        }
        .appointment-details li {
            margin-bottom: 10px;
        }
        .email-footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #999999;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 15px;
            background-color: #5cb85c;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }
        .button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Appointment Rescheduled Successfully</h2>
        </div>
        <div class="email-body">
            <p>Dear <?= htmlspecialchars($name) ?>,</p>
            <p>Your appointment has been successfully rescheduled. Below are the updated details:</p>
            <ul class="appointment-details">
                <li><strong>Date:</strong> <?= htmlspecialchars($date) ?></li>
                <li><strong>Time:</strong> <?= htmlspecialchars($startTime) ?> - <?= htmlspecialchars($endTime) ?></li>
            </ul>
            <p>If you have any questions or need further assistance, please feel free to contact us.</p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> Your Company. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
