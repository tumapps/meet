<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Appointment Rejected</title>
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
            border-left: 4px solid #d9534f;
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

        .contact-info {
            margin-top: 20px;
            font-size: 14px;
            color: #777777;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Appointment Rejected</h2>
        </div>
        <div class="email-body">
            <?php if ($recipientType === 'contactPerson'): ?>
                <p>Dear <?= htmlspecialchars($contact_name) ?>,</p>
                <p>
                    <?php if ($spaceRequestUpdate): ?>
                        Your requested space has been rejected, and the meeting will revert to the previous space.
                    <?php else: ?>
                        Your appointment request with <?= htmlspecialchars($username) ?> has been rejected. Below are the details:
                    <?php endif; ?>
                </p>

            <?php elseif ($recipientType === 'chairPerson'): ?>
                <p>Dear <?= htmlspecialchars($username) ?>,</p>
                <p>
                    <?php if ($spaceRequestUpdate): ?>
                        Your requested space has been rejected, and the meeting will revert to the previous space.
                    <?php else: ?>
                        Your appointment request by <?= htmlspecialchars($contact_name) ?> has been rejected. Here are the details:
                    <?php endif; ?>
                </p>

            <?php elseif ($recipientType === 'attendee'): ?>
                <p>Dear <?= htmlspecialchars($attendeeName) ?>,</p>
                <p>
                    <?php if ($spaceRequestUpdate): ?>
                        Unfortunately, the appointment you were invited to attend has been rejected, and the meeting will revert to the previous space.
                    <?php else: ?>
                        Unfortunately, the appointment you were invited to attend has been rejected. Here are the details:
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <div class="appointment-details">
                <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($startTime) ?> - <?= htmlspecialchars($endTime) ?></p>
                <p><strong>With:</strong> <?= htmlspecialchars($recipientType === 'user' ? $username : $contact_name) ?></p>
                <p><strong>Reason for Rejection:</strong> <?= htmlspecialchars($rejectionReason) ?></p>
            </div>
        </div>
        <div class="contact-info">
            <p>If you have any questions, feel free to contact us.</p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> Tum Tumeet. All rights reserved.</p>
        </div>
    </div>
</body>

</html>