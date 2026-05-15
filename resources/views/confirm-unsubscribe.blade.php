<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirm Unsubscribe</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px; }
        .card { max-width: 480px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; }
        .btn { background: #e74c3c; color: #fff; padding: 10px 18px; border: 0; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<div class="card">
    <h1>Unsubscribe?</h1>
    <p>Click the button below to remove <strong>{{ $subscriber->email }}</strong> from our newsletter.</p>
    <form action="{{ $actionUrl }}" method="POST">
        <button type="submit" class="btn">Confirm unsubscribe</button>
    </form>
</div>
</body>
</html>
