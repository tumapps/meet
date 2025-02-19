<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Appointment Venue Updated</title>
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

        .appointment-details {
            margin: 20px 0;
            padding-left: 20px;
            border-left: 4px solid #f0ad4e;
        }

        .email-footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Appointment Venue Updated</h2>
        </div>
        <div class="email-body">
            <p>Dear <?= htmlspecialchars($recipientName) ?>,</p>
            <p>We would like to inform you that the venue for your upcoming appointment has been updated. Below are the details:</p>

            <div class="appointment-details">
                <p><strong>Previous Venue:</strong> <?= htmlspecialchars($previous_venue) ?></p>
                <p><strong>New Venue:</strong> <?= htmlspecialchars($new_venue) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($startTime) ?> - <?= htmlspecialchars($endTime) ?></p>
                <p><strong>With:</strong> <?= htmlspecialchars($contact_name) ?></p>
            </div>

            <p>Please take note of the new location and make necessary arrangements accordingly.</p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= \Yii::$app->name; ?>. All rights reserved.</p>
        </div>
    </div>
</body>

</html>