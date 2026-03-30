<?php
require_once __DIR__ . '/includes/bootstrap.php';
$grouped   = Notes::getAllGrouped();
$pageTitle = 'Study Material';
include __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="page-banner-inner">
    <div class="breadcrumb"><a href="index.php">Home</a><span>/</span> Study Material</div>
    <h1>Study Material</h1>
  </div>
</div>

<!-- Search -->
<div class="search-bar">
  <div class="search-inner">
    <div class="search-input-wrap">
      <i class="fas fa-search"></i>
      <input type="text" id="search-input" placeholder="Search notes…" autocomplete="off">
    </div>
    <div class="filter-tags">
      <span class="filter-tag active" data-cat="">All Categories</span>
      <?php foreach (array_keys($grouped) as $cat): ?>
      <span class="filter-tag" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<section class="section">
  <div class="section-inner">
    <?php if (empty($grouped)): ?>
    <div style="text-align:center;padding:60px 0;color:var(--text-muted);">
      <i class="fas fa-folder-open" style="font-size:3rem;opacity:.3;"></i>
      <p style="margin-top:16px">No notes available yet.</p>
    </div>
    <?php else: ?>
    <?php foreach ($grouped as $cat => $notes): ?>
    <div class="category-section reveal" data-cat="<?= htmlspecialchars($cat) ?>">
      <div class="category-title">
        <i class="fas fa-folder"></i>
        <?= htmlspecialchars($cat) ?>
        <span style="font-size:.8rem;font-weight:400;color:var(--text-muted);font-family:var(--font-body)">(<?= count($notes) ?>)</span>
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
