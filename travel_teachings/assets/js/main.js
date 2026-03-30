/* TravelTeachings main.js */

// ── Hamburger ────────────────────────────────────────────────────────────
const hamburger = document.getElementById('hamburger');
const nav       = document.getElementById('main-nav');
hamburger?.addEventListener('click', () => {
  hamburger.classList.toggle('open');
  nav?.classList.toggle('open');
});

// ── Header shadow on scroll ──────────────────────────────────────────────
window.addEventListener('scroll', () => {
  document.getElementById('site-header')?.classList.toggle('scrolled', window.scrollY > 20);
}, { passive: true });

// ── Scroll Reveal ────────────────────────────────────────────────────────
const revealObserver = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); revealObserver.unobserve(e.target); } });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ── Notes Search / Filter ────────────────────────────────────────────────
const searchInput = document.getElementById('search-input');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase().trim();
    document.querySelectorAll('.note-card').forEach(card => {
      const name = card.querySelector('.note-name')?.textContent.toLowerCase() ?? '';
      card.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
    document.querySelectorAll('.category-section').forEach(sec => {
      const visible = [...sec.querySelectorAll('.note-card')].some(c => c.style.display !== 'none');
      sec.style.display = visible ? '' : 'none';
    });
  });
}

// Filter tags (by category)
document.querySelectorAll('.filter-tag').forEach(tag => {
  tag.addEventListener('click', () => {
    document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
    tag.classList.add('active');
    const cat = tag.dataset.cat;
    document.querySelectorAll('.category-section').forEach(sec => {
      sec.style.display = (!cat || sec.dataset.cat === cat) ? '' : 'none';
    });
  });
});

// ── Chatbot ──────────────────────────────────────────────────────────────
const fab    = document.getElementById('chat-fab');
const widget = document.getElementById('chat-widget');
const closeBtn = document.getElementById('chat-close');
const input    = document.getElementById('chat-input');
const sendBtn  = document.getElementById('chat-send');
const msgs     = document.getElementById('chat-messages');

fab?.addEventListener('click', () => {
  widget?.classList.toggle('chat-hidden');
  if (!widget?.classList.contains('chat-hidden')) input?.focus();
});
closeBtn?.addEventListener('click', () => widget?.classList.add('chat-hidden'));

function addMsg(text, who) {
  const div = document.createElement('div');
  div.className = `msg ${who}`;
  div.innerHTML = `<div class="bubble">${text}</div>`;
  msgs?.appendChild(div);
  msgs && (msgs.scrollTop = msgs.scrollHeight);
  return div;
}

function addTyping() {
  const div = document.createElement('div');
  div.className = 'msg bot';
  div.id = 'typing-indicator';
  div.innerHTML = `<div class="bubble typing"><div class="typing-dots"><span></span><span></span><span></span></div></div>`;
  msgs?.appendChild(div);
  msgs && (msgs.scrollTop = msgs.scrollHeight);
}

async function sendChat() {
  const text = input?.value.trim();
  if (!text) return;
  input && (input.value = '');
  addMsg(escapeHtml(text), 'user');
  addTyping();
  sendBtn && (sendBtn.disabled = true);
  try {
    const res  = await fetch('api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text })
    });
    const data = await res.json();
    document.getElementById('typing-indicator')?.remove();
    addMsg(escapeHtml(data.reply ?? 'Sorry, I could not process that.'), 'bot');
  } catch {
    document.getElementById('typing-indicator')?.remove();
    addMsg('Network error — please try again.', 'bot');
  } finally {
    sendBtn && (sendBtn.disabled = false);
    input?.focus();
  }
}

sendBtn?.addEventListener('click', sendChat);
input?.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); } });

function escapeHtml(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(str));
  return d.innerHTML.replace(/\n/g, '<br>');
}
