<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>New Appointment Created</title>
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

        .meeting-description {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #D89837;
            border-radius: 5px;
        }


        .appointment-details p {
            margin-bottom: 10px;
        }

        .file-preview {
            margin-top: 15px;
        }

        .file-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
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

        .contact-info {
            margin-top: 20px;
            font-size: 14px;
            color: #777777;
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
            <h2 class="uppercase"><?= $subject ? $subject : 'Appointment Created' ?></h2>
        </div>
        <div class="email-body">
            <?php if ($recipientType === 'contact_person'): ?>
                <p>Dear <span class="capitalize"><?= htmlspecialchars($contactPerson) ?>,</span></p>
                <p>An appointment has been successfully created for you with <span class="capitalize"><?= htmlspecialchars($chairPerson) ?></span>. Below are the appointment details:</p>

            <?php elseif ($recipientType === 'chair_person'): ?>
                <p>Dear <span class="capitalize"><?= htmlspecialchars($chairPerson) ?></span>,</p>
                <p>A new appointment has been booked with you by <?= htmlspecialchars($contactPerson) ?>. Here are the details:</p>

            <?php elseif ($recipientType === 'attendee'): ?>
                <p>Dear <span class="capitalize"><?= htmlspecialchars($attendeeName) ?>,</span></p>
                <p>You have been invited to attend a scheduled appointment with <?= htmlspecialchars($contactPerson) ?>. Here are the details:</p>
            <?php endif; ?>
            <?php if (!empty($description)): ?>
                <div class="meeting-description">
                    <p><strong>Meeting Description:</strong></p>
                    <p><?= nl2br(htmlspecialchars($description)) ?></p>
                </div>
            <?php endif; ?>
            <div class="appointment-details">
                <p><strong>Subject:</strong> <?= htmlspecialchars($subject) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($startTime) ?> - <?= htmlspecialchars($endTime) ?></p>
                <p><strong>With:</strong><span class="uppercase"><?= htmlspecialchars($recipientType === 'contact_person' ? $chairPerson : $contactPerson) ?></span></p>
            </div>

            <?php if (!empty($attachment_file_name) && !empty($attachment_download_link)): ?>
                <div class="file-preview">
                    <p><strong>Attached File:</strong> <?= htmlspecialchars($attachment_file_name) ?></p>
                    <a href="<?= htmlspecialchars($attachment_download_link) ?>">
                    </a>
                    <p>Click the link below to download the file:</p>
                    <a href="<?= htmlspecialchars($attachment_download_link) ?>" class="button" download>Download Agenda</a>
                </div>
            <?php endif; ?>
            <?php if ($recipientType === 'attendee'): ?>
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