<?php // includes/footer.php ?>
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <img src="assets/images/logo.png" alt="Logo" class="footer-logo">
      <p>Providing quality tourism education resources for students and academics worldwide.</p>
    </div>

    <div class="footer-links">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="study.php">Study Material</a></li>
        <li><a href="about.php">About Author</a></li>
        <li><a href="contact.php">Ask Us</a></li>
      </ul>
    </div>

    <div class="footer-social">
      <h4>Connect</h4>
      <div class="social-row">
        <a href="https://www.facebook.com/malrarenu?mibextid=MKOS29" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://instagram.com/sustainable_zindgi" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="mailto:malrarenu@gmail.com" aria-label="Email"><i class="fas fa-envelope"></i></a>
        <a href="https://www.linkedin.com/in/renu-malra-405a3217" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.researchgate.net/profile/Renu-Malra" target="_blank" rel="noopener" aria-label="ResearchGate"><i class="fab fa-researchgate"></i></a>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> TravelTeachings. All rights reserved.</p>
    <a href="admin.php" class="admin-link" title="Admin"><i class="fas fa-lock"></i></a>
  </div>
</footer>

<!-- Chatbot Widget -->
<div id="chat-fab" title="Ask AI about notes"><i class="fas fa-robot"></i><span class="chat-badge">AI</span></div>
<div id="chat-widget" class="chat-hidden">
  <div class="chat-header">
    <div class="chat-title"><i class="fas fa-robot"></i> Tourism AI Assistant</div>
    <button id="chat-close"><i class="fas fa-times"></i></button>
  </div>
  <div class="chat-messages" id="chat-messages">
    <div class="msg bot"><div class="bubble">Hi! I'm your Tourism AI assistant. Ask me anything about the study notes available here — topics, concepts, or help finding resources!</div></div>
  </div>
  <div class="chat-input-row">
    <input type="text" id="chat-input" placeholder="Ask about tourism topics..." maxlength="500" autocomplete="off">
    <button id="chat-send"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
