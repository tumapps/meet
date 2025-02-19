<?php
/* @var $this yii\web\View */
/* @var $username string */
/* @var $appointmentDate string */
/* @var $startTime string */
/* @var $endTime string */
/* @var $vcName string */
/* @var $appointmentLink string */
/* @var $markAttendanceLink string */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Action Required: Mark Appointment as Attended</title>
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
            border-left: 4px solid #d9534f;
            border-radius: 5px;
        }

        .appointment-details p {
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
            background-color: #d9534f;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }

        .button:hover {
            background-color: #c9302c;
        }

        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2 class="uppercase"><?= htmlspecialchars($subject) ?></h2>
        </div>
        <div class="email-body">
            <p>Dear <?= htmlspecialchars($chairPerson) ?>,</p>

            <p>The Meeting scheduled on <strong><?= htmlspecialchars($date) ?></strong> from <strong><?= htmlspecialchars($startTime) ?> to <?= htmlspecialchars($endTime) ?></strong> has ended more than an hour ago.</p>

            <p>Please confirm whether the meeting was attended. If it was, kindly log in into your account and mark it as attended .</p>

            <div class="appointment-details">
                <p><strong>Subject:</strong> <?= htmlspecialchars($meeting_subject) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($startTime) ?> - <?= htmlspecialchars($endTime) ?></p>
            </div>

            <p>If this meeting was not attended, you can ignore this message.</p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= \Yii::$app->name; ?>. All rights reserved.</p>
        </div>
    </div>
</body>

</html>