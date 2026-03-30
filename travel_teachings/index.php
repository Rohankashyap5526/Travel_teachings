<?php
require_once __DIR__ . '/includes/bootstrap.php';
Stats::recordVisit();

$stats   = [
    'visits'    => Stats::getTotalVisits(),
    'downloads' => Stats::getTotalDownloads(),
    'notes'     => Stats::getTotalNotes(),
];
$grouped = Notes::getAllGrouped();
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-text">
      <p class="hero-eyebrow">Tourism Education Platform</p>
      <h1>Your Gateway to <em>Tourism Knowledge</em></h1>
      <p>Comprehensive study notes, research resources, and academic materials on tourism, hospitality, and sustainable travel — curated by Dr. Renu Malra.</p>
      <div class="hero-actions">
        <a href="study.php" class="btn btn-primary"><i class="fas fa-book-open"></i> Browse Notes</a>
        <a href="about.php" class="btn btn-outline"><i class="fas fa-user-tie"></i> About Author</a>
      </div>
    </div>
    <div class="hero-image">
      <img src="assets/images/k1.png" alt="Tourism Study Resources">
    </div>
  </div>
</section>

<!-- Stats Bar -->
<div class="stats-bar">
  <div class="stats-inner">
    <div class="stat-item">
      <div class="stat-icon"><i class="fas fa-eye"></i></div>
      <div>
        <div class="stat-value" id="stat-visits"><?= number_format($stats['visits']) ?></div>
        <div class="stat-label">Total Visits</div>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon"><i class="fas fa-download"></i></div>
      <div>
        <div class="stat-value" id="stat-downloads"><?= number_format($stats['downloads']) ?></div>
        <div class="stat-label">Downloads</div>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon"><i class="fas fa-file-pdf"></i></div>
      <div>
        <div class="stat-value"><?= number_format($stats['notes']) ?></div>
        <div class="stat-label">Study Notes</div>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
      <div>
        <div class="stat-value"><?= count($grouped) ?></div>
        <div class="stat-label">Categories</div>
      </div>
    </div>
  </div>
</div>

<!-- Notes Section -->
<section class="section">
  <div class="section-inner">
    <div class="section-header reveal">
      <p class="eyebrow">Study Resources</p>
      <h2>Browse Notes by Category</h2>
      <p>Explore our curated collection of tourism study materials, lecture notes, and academic resources.</p>
    </div>

    <!-- Search -->
    <div class="search-bar reveal">
      <div class="search-inner">
        <div class="search-input-wrap">
          <i class="fas fa-search"></i>
          <input type="text" id="search-input" placeholder="Search notes by name…" autocomplete="off">
        </div>
        <div class="filter-tags">
          <span class="filter-tag active" data-cat="">All</span>
          <?php foreach (array_keys($grouped) as $cat): ?>
          <span class="filter-tag" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <?php if (empty($grouped)): ?>
    <div style="text-align:center; padding:60px 0; color:var(--text-muted);">
      <i class="fas fa-folder-open" style="font-size:3rem; opacity:.3;"></i>
      <p style="margin-top:16px;">No study materials available yet. Check back soon!</p>
    </div>
    <?php else: ?>
    <?php foreach ($grouped as $cat => $notes): ?>
    <div class="category-section reveal" data-cat="<?= htmlspecialchars($cat) ?>">
      <div class="category-title">
        <i class="fas fa-folder"></i>
        <?= htmlspecialchars($cat) ?>
        <span style="font-size:.8rem;font-weight:400;color:var(--text-muted);font-family:var(--font-body)">(<?= count($notes) ?> notes)</span>
      </div>
      <div class="notes-grid">
        <?php foreach ($notes as $note): ?>
        <div class="note-card">
          <div class="note-icon"><i class="fas fa-file-pdf"></i></div>
          <div class="note-body">
            <div class="note-name" title="<?= htmlspecialchars($note['pgf_name']) ?>"><?= htmlspecialchars($note['pgf_name']) ?></div>
            <div class="note-meta">PDF &middot; <?= htmlspecialchars($cat) ?></div>
          </div>
          <div class="note-actions">
            <a href="notes/<?= htmlspecialchars($note['notes_name']) ?>" target="_blank" class="note-btn note-btn-view" title="View"><i class="fas fa-eye"></i></a>
            <a href="api/download.php?file=<?= urlencode($note['notes_name']) ?>&name=<?= urlencode($note['pgf_name']) ?>" class="note-btn note-btn-dl" title="Download"><i class="fas fa-download"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
