<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Stalled Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #f95559;
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            color: #555555;
            line-height: 1.6;
        }
        .details {
            background-color: #f0f0f0;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-family: monospace;
            color: #333;
        }
        .footer {
            font-size: 12px;
            color: #999999;
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f95559;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 Queue Stalled Alert</h1>
        <p>Hi Admin,</p>
        <p>The Laravel queue has been detected as <strong>stalled</strong> or <strong>not processing jobs</strong>. Immediate attention is required to ensure your application functions properly.</p>

        <div class="details">
            <strong>App:</strong> {{ $app_name }}
            <br />
            <strong>Timestamp:</strong> {{ now() }}
        </div>

        <p>You can attempt to restart the queue manually:</p>

        <p class="footer">This is an automated message from your application queue monitoring system.</p>
    </div>
</body>
</html>
