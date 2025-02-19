<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Appointment Affected</title>
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
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .email-header h2 {
            margin: 0;
            color: #d9534f;
        }

        .email-body {
            font-size: 16px;
            color: #333333;
            line-height: 1.6;
            padding-top: 15px;
        }

        .appointment-details {
            background-color: #f9f9f9;
            padding: 15px;
            margin-top: 15px;
            border-radius: 6px;
            border-left: 4px solid #d9534f;
        }

        .appointment-details p {
            margin: 5px 0;
            font-size: 15px;
        }

        .email-footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #999999;
            border-top: 2px solid #e0e0e0;
            margin-top: 20px;
            padding-top: 10px;
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

        .capitalize {
            text-transform: capitalize;
        }

        .upercase {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2 class="uppercase"><?= $isEvent ? 'Appointments Affected by Event' : 'Appointment Affected' ?></h2>
        </div>

        <div class="email-body">
            <p>Dear <span class="capitalize"><?= htmlspecialchars($contact_name) ?>,</span></p>

            <?php if ($isEvent): ?>
                <p>We wanted to inform you that your appointment has been affected due to a scheduled event on that day.</p>
                <p>Unfortunately, the appointment cannot proceed as planned, and we will contact you shortly to reschedule. We apologize for any inconvenience and appreciate your understanding.</p>

            <?php else: ?>
                <?php if ($recipientType === 'user'): ?>
                    <p>We wanted to inform you that due to <strong class="capitalize"><?= htmlspecialchars($chairPerson) ?></strong> updating their availability, your appointment has been affected.</p>
                    <p>We will contact you shortly to reschedule the appointment. We apologize for any inconvenience this may cause, and we appreciate your understanding.</p>

                <?php elseif ($recipientType === 'attendee'): ?>
                    <p>We wanted to inform you that the appointment you were invited to with <strong class="capitalize"><?= htmlspecialchars($chairPerson) ?></strong> has been affected due to a change in availability.</p>
                    <p>You will be notified of the new schedule once it is confirmed. We apologize for any inconvenience and appreciate your patience.</p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="appointment-details">
                <p><strong>Subject:</strong> <?= htmlspecialchars($appointmentSubject) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                <p><strong>Start Time:</strong> <?= htmlspecialchars($start_time) ?></p>
                <p><strong>End Time:</strong> <?= htmlspecialchars($end_time) ?></p>
            </div>

        </div>

        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= \Yii::$app->name; ?>. All rights reserved.</p>
        </div>
    </div>
</body>

</html>