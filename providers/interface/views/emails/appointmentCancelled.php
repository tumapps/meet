<?php
/* @var $this yii\web\View */
/* @var $name string */
/* @var $date string */
/* @var $startTime string */
/* @var $endTime string */
/* @var $recipientType string */
/* @var $contactLink string */
/* @var $appointment_subject string */
/* @var $reason string */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Appointment Cancelled</title>
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
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #0275d8;
        }

        .email-header h2 {
            margin: 0;
            color: #333333;
            font-size: 22px;
        }

        .email-body {
            font-size: 16px;
            color: #555555;
            padding-top: 20px;
        }

        .email-body p {
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .email-footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #999999;
            border-top: 1px solid #dddddd;
            margin-top: 20px;
        }

        .highlight {
            font-weight: bold;
            color: #0275d8;
        }

        .capitalize {
            text-transform: capitalize;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Meeting Cancelled</h2>
        </div>
        <div class="email-body">
            <?php if ($recipientType === 'user'): ?>
                <p>Dear <span class="capitalize"><?= htmlspecialchars($name) ?>,</span></p>
            <?php elseif ($recipientType === 'attendee'): ?>
                <p>Dear <span class="capitalize"><?= htmlspecialchars($attendeeName) ?>,</span></p>
            <?php endif; ?>

            <p>
                The meeting that was scheduled on <strong><?= htmlspecialchars($date) ?></strong> from
                <strong><?= htmlspecialchars($startTime) ?></strong> to <strong><?= htmlspecialchars($endTime) ?></strong>
                has been cancelled.
            </p>
            <p>
                <strong>Meeting Subject:</strong> <span class="highlight"><?= htmlspecialchars($appointment_subject) ?></span>
            </p>

            <p><strong>Reason:</strong> <?= htmlspecialchars($reason) ?></p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= \Yii::$app->name; ?>. All rights reserved.</p>
        </div>
    </div>
</body>

</html>