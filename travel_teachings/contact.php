<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Ask Us';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf(Security::post('csrf_token'))) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $name    = Security::post('name');
        $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $subject = Security::post('subject');
        $message = Security::post('message');

        if (!$name || !$email || !$subject || !$message) {
            $error = 'All fields are required.';
        } elseif (strlen($message) > 2000) {
            $error = 'Message too long (max 2000 characters).';
        } else {
            $to      = 'travelteachings@gmail.com';
            $headers = "From: noreply@travelteachings.com\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";
            $body    = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
            if (mail($to, "TravelTeachings Contact: $subject", $body, $headers)) {
                $success = 'Your message has been sent! We\'ll respond within 24 hours.';
            } else {
                $error = 'Failed to send message. Please email us directly at travelteachings@gmail.com';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="page-banner-inner">
    <div class="breadcrumb"><a href="index.php">Home</a><span>/</span> Ask Us</div>
    <h1>Ask About Tourism</h1>
  </div>
</div>

<section class="section">
  <div class="section-inner">
    <div class="contact-layout">
      <div class="contact-info reveal">
        <h2>Get in Touch</h2>
        <p>Have questions about tourism concepts, looking for specific study materials, or want to collaborate? Dr. Renu Malra and the TravelTeachings team are happy to help.</p>

        <div class="info-item">
          <div class="info-item i"><i class="fas fa-envelope"></i></div>
          <div class="info-text">
            <strong>Email</strong>
            <span>travelteachings@gmail.com</span>
          </div>
        </div>
        <div class="info-item">
          <div class="info-item i"><i class="fas fa-map-marker-alt"></i></div>
          <div class="info-text">
            <strong>Institution</strong>
            <span>Kurukshetra University, Haryana, India</span>
          </div>
        </div>
        <div class="info-item">
          <div class="info-item i"><i class="fas fa-clock"></i></div>
          <div class="info-text">
            <strong>Response Time</strong>
            <span>Within 24 hours on working days</span>
          </div>
        </div>

        <div style="margin-top:32px;padding:20px;background:linear-gradient(135deg,var(--navy),var(--navy-mid));border-radius:var(--radius);color:var(--white);">
          <p style="font-size:.85rem;opacity:.8;margin-bottom:8px">💡 Try our AI Assistant!</p>
          <p style="font-size:.9rem;">Click the robot icon at the bottom-right of any page to chat with our Tourism AI — it knows all the notes and can answer tourism questions instantly.</p>
        </div>
      </div>

      <div class="card-form reveal">
        <h3 style="font-family:var(--font-display);font-size:1.3rem;color:var(--navy);margin-bottom:24px;">Send a Message</h3>

        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <?= Security::csrfField() ?>
          <div class="form-group">
            <label>Your Name</label>
            <input type="text" name="name" required maxlength="100" placeholder="Full name">
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="your@email.com">
          </div>
          <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" required maxlength="200" placeholder="What's your question about?">
          </div>
          <div class="form-group">
            <label>Message</label>
            <textarea name="message" required maxlength="2000" placeholder="Describe your question or request…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
