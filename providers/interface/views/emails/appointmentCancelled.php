<?php
/* @var $this yii\web\View */
/* @var $name string */
/* @var $date string */
/* @var $startTime string */
/* @var $endTime string */
/* @var $recipientType string */
/* @var $contactLink string */
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
            background-color: #0275d8;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }
        .button:hover {
            background-color: #025aa5;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Appointment Cancellation</h2>
        </div>
        <div class="email-body">
            <?php if ($recipientType === 'user'): ?>
                <p>Dear <?= htmlspecialchars($name) ?>,</p>
            <?php endif; ?>

            <!-- Different content based on the recipient -->
            <?php if ($recipientType === 'user'): ?>
                <p>We regret to inform you that your appointment scheduled on <strong><?= htmlspecialchars($date) ?></strong> from <strong><?= htmlspecialchars($startTime) ?></strong> to <strong><?= htmlspecialchars($endTime) ?></strong> has been cancelled.</p>

            <?php elseif ($recipientType === 'vc'): ?>
                <p>The appointment you had with <strong><?= htmlspecialchars($name) ?></strong> scheduled on <strong><?= htmlspecialchars($date) ?></strong> from <strong><?= htmlspecialchars($startTime) ?></strong> to <strong><?= htmlspecialchars($endTime) ?></strong> has been cancelled.</p>
            <?php endif; ?>

            <P><strong>Reason:</strong> <?= htmlspecialchars($reason); ?></P>

            <?php if ($recipientType === 'user'): ?>
                <p>If you have any questions or need further assistance, please feel free to contact us.</p>

                <a href="<?= htmlspecialchars($contactLink) ?>" class="button">Contact Support</a>
            <?php endif; ?>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> Tum Tumeet. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
