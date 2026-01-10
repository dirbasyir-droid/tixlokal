<?php
include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>FAQ</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
        .nav-bar { background: #eee; padding: 10px; margin-bottom: 20px; border-bottom: 1px solid #ccc; }
        .faq-item { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .question { font-weight: bold; font-size: 1.1em; color: #333; margin-bottom: 5px; }
        .answer { color: #555; }
    </style>
</head>
<body>

<div class="nav-bar">
    <strong>TixLokal</strong> | <a href="index.php">Home</a>
</div>

<h1>Frequently Asked Questions</h1>

<div class="faq-item">
    <div class="question">Q: How do I receive my ticket?</div>
    <div class="answer">A: Once your payment receipt is approved by an administrator, your status will change to "Approved" and a "View Ticket" button will appear in your My Tickets dashboard.</div>
</div>

<div class="faq-item">
    <div class="question">Q: Can I get a refund if I can't attend?</div>
    <div class="answer">A: No. As stated in our Terms of Service, all sales are final unless the event is cancelled by the organizer.</div>
</div>

<div class="faq-item">
    <div class="question">Q: How long does payment verification take?</div>
    <div class="answer">A: Verification is a manual process done by our admins. It typically takes between 1 to 24 hours.</div>
</div>

<div class="faq-item">
    <div class="question">Q: Do I need to print my ticket?</div>
    <div class="answer">A: No, you can simply show the QR code on your mobile phone at the entrance.</div>
</div>

<div class="faq-item">
    <div class="question">Q: What payment methods do you accept?</div>
    <div class="answer">A: Currently, we only accept manual Bank Transfers. You must upload the transaction receipt for verification.</div>
</div>

</body>
</html>