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
            <h2>Appointments Affected</h2>
        </div>
        <div class="email-body" style="font-family: Arial, sans-serif; color: #333;">
            <p>Dear <?= htmlspecialchars($name) ?>,</p>
            
            <?php if ($recipientType === 'user'): ?>
                <p>We wanted to inform you that due to <?= htmlspecialchars($bookedUserName) ?> updating their availability, your appointment has been affected.</p>
                <p>We will contact you shortly to reschedule the appointment. We apologize for any inconvenience this may cause, and we appreciate your understanding and patience.</p>
            
            <?php elseif ($recipientType === 'attendee'): ?>
                <p>We wanted to inform you that the appointment you were invited to with <?= htmlspecialchars($bookedUserName) ?> has been affected due to a change in availability.</p>
                <p>You will be notified of the new schedule once it is confirmed. We apologize for any inconvenience this may cause, and thank you for your patience.</p>
            <?php endif; ?>
            
            <p>If you have any questions in the meantime, feel free to reach out to us at <?= htmlspecialchars($supportEmail) ?>.</p>
            <p>Thank you.</p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> Your Company. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
