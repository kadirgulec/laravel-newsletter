<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 20px; border-radius: 8px; }
        .footer { margin-top: 20px; font-size: 12px; color: #888; text-align: center; }
        .btn { background: #e74c3c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">

    <div class="content">
        {!! $content !!}
    </div>

    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">

    <!-- Footer with Unsubscribe Link -->
    <div class="footer">
        <p>You received this email because you subscribed to our newsletter.</p>
        <p>
            <a href="{{ $unsubscribeUrl }}">Unsubscribe here</a>
        </p>
    </div>
</div>
</body>
</html>