<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $url = 'https://YOUR_SUPABASE_URL/auth/v1/signup';
  $data = json_encode(['email' => $email, 'password' => $password]);
  $options = [
    'http' => [
      'method' => 'POST',
      'header' => "Content-Type: application/json\napikey: YOUR_SUPABASE_KEY",
      'content' => $data
    ]
  ];
  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  $response = json_decode($result);
  if (isset($response->error)) {
    echo $response->error->message;
  } else {
    echo "Check your email for confirmation!";
  }
}
?>
<form method="POST">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit">Sign Up</button>
</form>