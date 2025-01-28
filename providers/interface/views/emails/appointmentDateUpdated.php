<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appointment Date Updated</title>
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
            <h2>Appointment Date Updated</h2>
        </div>
        <div class="email-body">
            <p>Dear <?= htmlspecialchars($recipientType === 'attendee' ? $attendeeName : $contact_person_name) ?>,</p>

            <?php if ($recipientType === 'contact_person'): ?>
                <p>The appointment with <?= htmlspecialchars($username) ?> has been rescheduled. Please see the updated details below:</p>
            <?php elseif ($recipientType === 'attendee'): ?>
                <p>The appointment you are invited to with <?= htmlspecialchars($username) ?> has been rescheduled. Here are the new details:</p>
            <?php endif; ?>

            <div class="appointment-details">
                <p><strong>Initial Date:</strong> <?= htmlspecialchars($initial_date) ?></p>
                <p><strong>New Date:</strong> <?= htmlspecialchars($current_date) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($start_time) ?> - <?= htmlspecialchars($end_time) ?></p>
            </div>

            <?php if (isset($contactLink)): ?>
                <p>For more details, click below:</p>
                <a href="<?= htmlspecialchars($contactLink) ?>" class="button">View Appointment</a>
            <?php endif; ?>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> Tum Tumeet. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
