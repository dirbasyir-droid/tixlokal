<?php
include 'config.php';

$token = $_GET['token'] ?? '';
$token = mysqli_real_escape_string($conn, $token);

$status = 'error';
$message = 'Invalid verification link.';

if (!empty($token)) {
    // Find user with valid token (not expired)
    $sql = "SELECT id, name, email, verify_expires, email_verified
            FROM users
            WHERE verify_token='$token'
            LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) === 1) {
        $u = mysqli_fetch_assoc($res);

        // If already verified
        if (isset($u['email_verified']) && (int)$u['email_verified'] === 1) {
            $status = 'ok';
            $message = 'Your email is already verified. You can sign in now.';
        } else {
            // Check expiry if column exists (best-effort)
            $expires_ok = true;
            if (!empty($u['verify_expires'])) {
                $expires_ok = (strtotime($u['verify_expires']) >= time());
            }

            if ($expires_ok) {
                mysqli_query($conn, "UPDATE users
                    SET email_verified=1, verify_token=NULL, verify_expires=NULL
                    WHERE id=".(int)$u['id']
                );
                $status = 'ok';
                $message = 'Email verified successfully! You can sign in now.';
            } else {
                $status = 'error';
                $message = 'This verification link has expired. Please resend a new link.';
                $_SESSION['pending_verify_email'] = $u['email'];
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Email Verification • TixLokal</title>
  <style>
    :root{
      --bg1:#0b1020; --bg2:#0f172a;
      --card:rgba(255,255,255,.06);
      --border:rgba(255,255,255,.12);
      --text:#e5e7eb; --muted:rgba(229,231,235,.72);
      --ok:#22c55e; --bad:#ef4444; --accent:#7c3aed;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;
      color:var(--text);
      min-height:100vh;
      background:
        radial-gradient(900px 520px at 20% 10%, rgba(124,58,237,.35), transparent 60%),
        radial-gradient(820px 520px at 90% 20%, rgba(6,182,212,.25), transparent 60%),
        linear-gradient(180deg, var(--bg1), var(--bg2));
      display:grid;
      place-items:center;
      padding:22px;
    }
    .card{
      width:min(720px, 100%);
      border:1px solid var(--border);
      background:var(--card);
      border-radius:18px;
      box-shadow:0 18px 45px rgba(0,0,0,.45);
      backdrop-filter: blur(14px);
      padding:18px;
    }
    h1{margin:0 0 6px;font-size:22px}
    p{margin:0;color:var(--muted)}
    .badge{
      display:inline-flex;align-items:center;gap:8px;
      padding:7px 12px;border-radius:999px;
      margin-top:14px;
      border:1px solid var(--border);
      background:rgba(0,0,0,.18);
      font-weight:800;
    }
    .badge.ok{border-color:rgba(34,197,94,.35)}
    .badge.ok span{color:var(--ok)}
    .badge.bad{border-color:rgba(239,68,68,.35)}
    .badge.bad span{color:var(--bad)}
    .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
    .btn{
      text-decoration:none;
      padding:10px 14px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(255,255,255,.06);
      color:var(--text);
      font-weight:800;
      display:inline-flex;align-items:center;justify-content:center;
    }
    .btn.primary{
      border-color:rgba(124,58,237,.35);
      background:linear-gradient(135deg, rgba(124,58,237,.95), rgba(6,182,212,.80));
    }
  </style>
</head>
<body>
  <div class="card">
    <div style="font-weight:900;letter-spacing:.2px;margin-bottom:8px">TixLokal</div>
    <h1>Email verification</h1>
    <p><?php echo htmlspecialchars($message); ?></p>

    <?php if ($status === 'ok'): ?>
      <div class="badge ok">Status: <span>Verified</span></div>
    <?php else: ?>
      <div class="badge bad">Status: <span>Failed</span></div>
    <?php endif; ?>

    <div class="actions">
      <a class="btn primary" href="index.php?action=login">Go to Sign in</a>
      <a class="btn" href="index.php">Back to Home</a>
    </div>

    <?php if (!empty($_SESSION['pending_verify_email'])): ?>
      <div style="margin-top:14px;border-top:1px solid rgba(255,255,255,.10);padding-top:14px">
        <form method="post" action="index.php?action=login" style="margin:0;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
          <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['pending_verify_email']); ?>">
          <button class="btn" type="submit" name="resend_verification" value="1">Resend verification email</button>
          <div style="color:var(--muted);font-size:13px">Email: <?php echo htmlspecialchars($_SESSION['pending_verify_email']); ?></div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
