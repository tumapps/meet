<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Appointment Notification</title>
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
            border-left: 4px solid #5cb85c;
            border-radius: 5px;
        }

        .appointment-details p {
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
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2><?= htmlspecialchars($subject) ?></h2>
        </div>
        <div class="email-body">
            <p>Dear <?= htmlspecialchars($contact_name) ?>,</p>

            <?php if ($is_removed): ?>
                <p>We regret to inform you that you have been removed from the following Meeting:</p>
                <div class="appointment-details">
                    <p><strong>Subject:</strong> <?= htmlspecialchars($meeting_subject) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                    <p><strong>Start Time:</strong> <?= htmlspecialchars($start_time) ?></p>
                    <p><strong>End Time:</strong> <?= htmlspecialchars($end_time) ?></p>
                </div>
                <p><strong>Reason for Removal:</strong> <?= htmlspecialchars($reason) ?></p>
            <?php else: ?>
                <p>You have been added as an attendee to the following Meeting:</p>
                <div class="appointment-details">
                    <p><strong>Subject:</strong> <?= htmlspecialchars($meeting_subject) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                    <p><strong>Start Time:</strong> <?= htmlspecialchars($start_time) ?></p>
                    <p><strong>End Time:</strong> <?= htmlspecialchars($end_time) ?></p>
                </div>
                <p>Please confirm your attendance by clicking the button below:</p>
                <a href="<?= htmlspecialchars($confirmationLink) ?>" target="_blank"
                    aria-label="Click to confirm your attendance"
                    style="display: inline-block; padding: 10px 20px; background-color: #097B3E; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Confirm Attendance
                </a>
            <?php endif; ?>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= \Yii::$app->name; ?>. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
