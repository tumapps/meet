<?php
/* @var $this yii\web\View */
/* @var $username string */
/* @var $vcName string */
/* @var $affectedAppointments array */
/* @var $rescheduleLink string */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appointments Need Rescheduling</title>
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
        .appointments-list {
            margin: 20px 0;
            padding-left: 20px;
        }
        .appointments-list li {
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Appointments Need Rescheduling</h2>
        </div>
        <div class="email-body">
            <p>Due to update's on availability, some of your appointments need to be rescheduled. Please review the affected appointments below and reschedule them at your earliest convenience.</p>
            <ul class="appointments-list">
                <?php foreach ($affectedAppointments as $appointment): ?>
                    <li>
                        <strong>Date:</strong> <?= htmlspecialchars($appointment['date']) ?>,
                        <strong>Time:</strong> <?= htmlspecialchars($appointment['start_time']) ?> - <?= htmlspecialchars($appointment['end_time']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> Tum Tumeet. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
