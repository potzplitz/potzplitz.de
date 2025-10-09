<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      color: #e5e7eb;
      background: #111827;
      display: grid;
      place-items: center;
      padding: 24px;
    }

    .card {
      width: min(520px, 100%);
      background: linear-gradient(
        180deg,
        color-mix(in oklab, #1f2937 92%, black 8%),
        color-mix(in oklab, #1f2937 85%, black 15%)
      );
      border: 1px solid rgba(255,255,255,0.05);
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.6);
      padding: clamp(20px, 4vw, 32px);
      backdrop-filter: saturate(130%) blur(6px);
    }

    header {
      display: grid;
      gap: 6px;
      margin-bottom: 18px;
    }

    header .kicker {
      color: #9ca3af;
      font-size: .9rem;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    header h1 {
      margin: 0;
      font-size: clamp(1.4rem, 2.2vw, 1.8rem);
      font-weight: 700;
    }

    header p.sub {
      margin: 4px 0 0;
      color: #9ca3af;
    }

    .btn {
      display: inline-block;
      margin-top: 20px;
      border: none;
      border-radius: 12px;
      padding: 12px 16px;
      font-weight: 700;
      color: #0b1220 !important;
      text-decoration: none;
      background: linear-gradient(
        180deg,
        color-mix(in oklab, #0ea5e9 95%, white 5%),
        color-mix(in oklab, #0ea5e9 85%, black 15%)
      );
    }

    .alt {
      font-size: .9rem;
      color: #9ca3af;
      margin-top: 18px;
    }

    .alt a {
      color: #0ea5e9;
      text-decoration: none;
    }

    .alt a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <main class="card">
    <header>
      <span class="kicker">Email Verification</span>
      <h1>Confirm your account</h1>
      <p class="sub">Click the button below to verify your email address and finish setting up your account.</p>
    </header>

    <a href="<-URL_HTBASE->account/verify/code?token=<-CODE->" class="btn">Verify Email</a>

    <p class="alt">If you didn’t request this, you can safely ignore this email.</p>
  </main>
</body>
</html>
