<!DOCTYPE html>
<html>
<head>
    <title>Contact form detail {{ $mailData['domain'] }}</title>
</head>
<body>
        <p><strong>User information detail is :-</strong></p>
        <h1>{{ $mailData['contactName'] }}</h1>
        <p>{{ $mailData['email'] }}</p>
        <p>{{ $mailData['phone'] }}</p>
</body>
</html>