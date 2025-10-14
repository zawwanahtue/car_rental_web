<!DOCTYPE html>
<html lang="en" style="margin:0; padding:0;">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to Journey Wheel</title>

  <!-- Google Font: Orbitron -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">

  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
      font-family: Arial, sans-serif;
    }
    .logo-text {
      font-family: 'Orbitron', sans-serif;
      font-size: 28px;
      letter-spacing: 1.5px;
      color: #00F5D4;
      margin: 0;
      text-transform: uppercase;
    }
  </style>
</head>

<body>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f4f4; padding:40px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color:#ffffff; border-radius:12px; border:3px solid #00F5D4; overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td align="center" style="background-color:#1B263B; padding:25px;">
              <h1 class="logo-text">Journey Wheel</h1>
              <p style="color:#ffffff; margin-top:5px; font-size:14px;">Car Rental Made Easy</p>
            </td>
          </tr>

          <!-- Welcome Content -->
          <tr>
            <td style="padding:30px 40px; color:#333333;">
              <h2 style="color:#1B263B;">Welcome, {{ $user_name }}!</h2>
              <p style="font-size:16px; line-height:1.6;">
                We're thrilled to have you at <strong>Journey Wheel</strong> — your trusted partner for smooth and reliable car rentals.
              </p>
              <p style="font-size:16px; line-height:1.6;">
                You can now explore our wide range of vehicles and start your next journey with just a few clicks.
              </p>
              <p style="font-size:16px; margin-top:25px;">
                🚗 <em>Drive your dreams, one wheel at a time.</em>
              </p>
            </td>
          </tr>

          <!-- Call to Action -->
          <tr>
            <td align="center" style="padding:20px;">
              <a href="https://car-rental-frontend-weu1.vercel.app/" 
                 style="background-color:#00F5D4; color:#1B263B; text-decoration:none; 
                        padding:12px 25px; border-radius:6px; font-weight:bold; display:inline-block;">
                Visit Journey Wheel
              </a>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="background-color:#1B263B; padding:20px;">
              <p style="color:#ffffff; font-size:13px; margin:0;">
                © {{ $date }} Journey Wheel. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
