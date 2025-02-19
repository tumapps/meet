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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            line-height: 1.6;
        }

        .appointments-list {
            margin: 20px 0;
            padding: 0;
            list-style: none;
        }

        .appointments-list li {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 5px solid #d9534f;
        }

        .appointments-list li strong {
            color: #333;
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
            font-weight: bold;
        }

        .button:hover {
            background-color: #c9302c;
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
            <h2 class="uppercase">Appointments Need Rescheduling</h2>
        </div>
        <div class="email-body">
            <?php if ($isEvent): ?>
                <p>Some of your appointments have been affected due to scheduled events on the same day. Please check the details below and make necessary adjustments.</p>
            <?php else: ?>
                <p>Due to an update in availability, some of your appointments need to be rescheduled. Please review the affected appointments below and reschedule them at your earliest convenience.</p>
            <?php endif; ?>

            <ul class="appointments-list">
                <?php foreach ($affectedAppointments as $appointment): ?>
                    <li>
                        <p><strong>Subject:</strong> <?= htmlspecialchars($appointment['subject']) ?></p>
                        <p><strong>Date:</strong> <?= htmlspecialchars($appointment['appointment_date']) ?></p>
                        <p><strong>Time:</strong> <?= htmlspecialchars($appointment['start_time']) ?> - <?= htmlspecialchars($appointment['end_time']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- <p>Click the button below to manage your appointments:</p> -->
            <!-- <a href="<//= htmlspecialchars($rescheduleLink) ?>" class="button">Reschedule Now</a> -->
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= \Yii::$app->name; ?>. All rights reserved.</p>
        </div>
    </div>
</body>

</html>