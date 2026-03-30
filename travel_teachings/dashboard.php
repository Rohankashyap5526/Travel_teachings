<?php
/**
 * dashboard.php — Admin Dashboard
 */
require_once __DIR__ . '/includes/bootstrap.php';
Security::requireAdmin();

// Session timeout: 1 hour
if (isset($_SESSION['admin_time']) && (time() - $_SESSION['admin_time']) > SESSION_LIFETIME) {
    session_destroy();
    header('Location: admin.php?timeout=1');
    exit;
}
$_SESSION['admin_time'] = time();

$message = $error = '';

// ── Handle POST actions ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf(Security::post('csrf_token'))) {
        $error = 'CSRF validation failed.';
    } else {
        $action = Security::post('action');

        // Add Category
        if ($action === 'add_category') {
            $name = preg_replace('/\s+/', '_', trim($_POST['category_name'] ?? ''));
            $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
            if (empty($name)) {
                $error = 'Category name must contain only letters, numbers, underscores.';
            } elseif (Notes::categoryExists($name)) {
                $error = "Category '$name' already exists.";
            } elseif (Notes::addCategory($name)) {
                $message = "Category '$name' created successfully.";
            } else {
                $error = 'Failed to create category. Name must be 2–50 alphanumeric characters.';
            }
        }

        // Delete Category
        elseif ($action === 'delete_category') {
            $cat = Security::post('category');
            if (Notes::deleteCategory($cat)) {
                $message = "Category '$cat' and all its notes deleted.";
            } else {
                $error = 'Failed to delete category.';
            }
        }

        // Upload Note
        elseif ($action === 'upload_note') {
            $noteName = Security::post('note_name');
            $cat      = Security::post('category');

            if (empty($noteName) || empty($cat)) {
                $error = 'Note name and category are required.';
            } elseif (!Notes::categoryExists($cat)) {
                $error = 'Invalid category selected.';
            } elseif (!isset($_FILES['file'])) {
                $error = 'No file uploaded.';
            } else {
                $validation = Security::validateUpload($_FILES['file']);
                if (!$validation['ok']) {
                    $error = $validation['msg'];
                } else {
                    $ext      = 'pdf';
                    $unique   = bin2hex(random_bytes(8)) . '.' . $ext;
                    $destPath = UPLOAD_DIR . $unique;
                    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
                        if (Notes::addNote($cat, $noteName, $unique)) {
                            $message = "Note '$noteName' uploaded to '$cat' successfully.";
                        } else {
                            @unlink($destPath);
                            $error = 'DB insert failed. Note not saved.';
                        }
                    } else {
                        $error = 'File move failed. Check server permissions.';
                    }
                }
            }
        }

        // Delete Note
        elseif ($action === 'delete_note') {
            $cat  = Security::post('category');
            $note = Security::post('note_name');
            if (Notes::deleteNote($cat, $note)) {
                $message = "Note '$note' deleted.";
            } else {
                $error = 'Failed to delete note.';
            }
        }

        // Save Groq API Key
        elseif ($action === 'save_settings') {
            $groqKey = trim($_POST['groq_api_key'] ?? '');
            // Store in a .env-style file (in a non-public dir ideally)
            $envFile = ROOT_PATH . '/.env';
            $existing = file_exists($envFile) ? file_get_contents($envFile) : '';
            if (preg_match('/^GROQ_API_KEY=.*/m', $existing)) {
                $existing = preg_replace('/^GROQ_API_KEY=.*/m', "GROQ_API_KEY=$groqKey", $existing);
            } else {
                $existing .= "\nGROQ_API_KEY=$groqKey";
            }
            file_put_contents($envFile, $existing);
            $message = 'Settings saved. Groq API key updated.';
        }
    }
}

// ── Fetch data ─────────────────────────────────────────────────────────────
$categories = Notes::getCategories();
$grouped    = Notes::getAllGrouped();
$visitStats = Stats::getVisitStats();
$totalDl    = Stats::getTotalDownloads();
$totalNotes = Stats::getTotalNotes();

// Recent downloads
$db = Database::get();
$recentDl = $db->query(
    "SELECT note_name, file_name, ip_address, downloaded_at FROM downloads ORDER BY downloaded_at DESC LIMIT 10"
)->fetchAll();
$recentVisits = $db->query(
    "SELECT ip_address, visited_at FROM visits ORDER BY visited_at DESC LIMIT 10"
)->fetchAll();

$currentGroqKey = getenv('GROQ_API_KEY') ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | TravelTeachings Admin</title>
<link rel="icon" type="image/png" href="assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/main.css">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<!-- Admin Header -->
<header class="site-header" style="position:sticky;top:0;z-index:200;">
  <div class="header-inner">
    <a href="index.php" class="brand">
      <img src="assets/images/logo.png" alt="" class="brand-logo">
      <span class="brand-name">Travel<em>Teachings</em> <small style="font-size:.65rem;opacity:.6;font-family:var(--font-body)">Admin</small></span>
    </a>
    <div style="display:flex;align-items:center;gap:16px;">
      <span style="color:rgba(255,255,255,.6);font-size:.85rem;"><i class="fas fa-user-shield" style="color:var(--gold)"></i> <?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></span>
      <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</header>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Overview</div>
      <div class="sidebar-link active" onclick="showPanel('overview')"><i class="fas fa-chart-bar"></i> Dashboard</div>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Content</div>
      <div class="sidebar-link" onclick="showPanel('upload')"><i class="fas fa-upload"></i> Upload Note</div>
      <div class="sidebar-link" onclick="showPanel('manage')"><i class="fas fa-folder"></i> Manage Notes</div>
      <div class="sidebar-link" onclick="showPanel('categories')"><i class="fas fa-tags"></i> Categories</div>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Analytics</div>
      <div class="sidebar-link" onclick="showPanel('visits')"><i class="fas fa-eye"></i> Visit Log</div>
      <div class="sidebar-link" onclick="showPanel('downloads')"><i class="fas fa-download"></i> Download Log</div>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">System</div>
      <div class="sidebar-link" onclick="showPanel('settings')"><i class="fas fa-cog"></i> Settings</div>
      <a class="sidebar-link" href="index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">
    <?php if ($message): ?>
    <div class="alert alert-success" style="margin-bottom:24px"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:24px"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ── Overview Panel ─────────────────────────────────────────────── -->
    <div class="admin-panel active" id="panel-overview">
      <h2 class="panel-title">Dashboard Overview</h2>
      <div class="stat-card-grid">
        <div class="stat-card">
          <div class="num"><?= number_format($visitStats['total']) ?></div>
          <div class="lbl"><i class="fas fa-eye"></i> Total Visits</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= number_format($visitStats['today']) ?></div>
          <div class="lbl"><i class="fas fa-calendar-day"></i> Today</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= number_format($visitStats['week']) ?></div>
          <div class="lbl"><i class="fas fa-calendar-week"></i> This Week</div>
        </div>
        <div class="stat-card" style="border-top-color:#2d9c6a">
          <div class="num"><?= number_format($totalDl) ?></div>
          <div class="lbl"><i class="fas fa-download"></i> Downloads</div>
        </div>
        <div class="stat-card" style="border-top-color:#4a90d9">
          <div class="num"><?= number_format($totalNotes) ?></div>
          <div class="lbl"><i class="fas fa-file-pdf"></i> Notes</div>
        </div>
        <div class="stat-card" style="border-top-color:#9c4aaf">
          <div class="num"><?= count($categories) ?></div>
          <div class="lbl"><i class="fas fa-folder"></i> Categories</div>
        </div>
      </div>

      <!-- Notes by category summary -->
      <h3 style="font-family:var(--font-display);font-size:1.2rem;color:var(--navy);margin-bottom:16px;">Notes by Category</h3>
      <table class="data-table">
        <thead><tr><th>Category</th><th>Notes</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($grouped as $cat => $notes): ?>
          <tr>
            <td><i class="fas fa-folder" style="color:var(--gold);margin-right:8px"></i><?= htmlspecialchars($cat) ?></td>
            <td><?= count($notes) ?></td>
            <td><button class="btn btn-sm" style="background:var(--grey-100);color:var(--navy);" onclick="showPanel('manage')">Manage</button></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ── Upload Panel ───────────────────────────────────────────────── -->
    <div class="admin-panel" id="panel-upload">
      <h2 class="panel-title">Upload New Note</h2>
      <div style="max-width:560px;">
        <form method="post" enctype="multipart/form-data">
          <?= Security::csrfField() ?>
          <input type="hidden" name="action" value="upload_note">
          <div class="form-group">
            <label>Display Name <span style="color:var(--danger)">*</span></label>
            <input type="text" name="note_name" required maxlength="255" placeholder="e.g. Introduction to Tourism">
          </div>
          <div class="form-group">
            <label>Category <span style="color:var(--danger)">*</span></label>
            <select name="category" required>
              <option value="">— Select Category —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>PDF File <span style="color:var(--danger)">*</span></label>
            <div class="file-drop-zone" id="file-drop">
              <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--grey-300);display:block;margin-bottom:8px;"></i>
              <p style="color:var(--text-muted);font-size:.9rem;">Drag & drop PDF here, or click to browse</p>
              <p style="color:var(--text-muted);font-size:.78rem;margin-top:4px;">Max 20 MB · PDF only</p>
              <input type="file" name="file" id="file-input" accept=".pdf" required style="position:absolute;inset:0;opacity:0;cursor:pointer;">
              <div id="file-chosen" style="margin-top:8px;font-size:.85rem;color:var(--gold);font-weight:600;"></div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Note</button>
        </form>
      </div>
    </div>

    <!-- ── Manage Notes Panel ─────────────────────────────────────────── -->
    <div class="admin-panel" id="panel-manage">
      <h2 class="panel-title">Manage Notes</h2>
      <?php foreach ($grouped as $cat => $notes): ?>
      <div style="margin-bottom:36px;">
        <div class="category-title"><i class="fas fa-folder"></i> <?= htmlspecialchars($cat) ?> <span style="font-size:.8rem;font-weight:400;color:var(--text-muted);font-family:var(--font-body)">(<?= count($notes) ?>)</span></div>
        <?php if (empty($notes)): ?>
        <p style="color:var(--text-muted);font-size:.9rem;padding:12px 0;">No notes in this category.</p>
        <?php else: ?>
        <table class="data-table">
          <thead><tr><th>Name</th><th>File</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($notes as $note): ?>
            <tr>
              <td><?= htmlspecialchars($note['pgf_name']) ?></td>
              <td><a href="notes/<?= htmlspecialchars($note['notes_name']) ?>" target="_blank" style="color:var(--gold);font-size:.82rem;"><?= htmlspecialchars($note['notes_name']) ?></a></td>
              <td>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete this note?')">
                  <?= Security::csrfField() ?>
                  <input type="hidden" name="action" value="delete_note">
                  <input type="hidden" name="category" value="<?= htmlspecialchars($cat) ?>">
                  <input type="hidden" name="note_name" value="<?= htmlspecialchars($note['pgf_name']) ?>">
                  <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── Categories Panel ───────────────────────────────────────────── -->
    <div class="admin-panel" id="panel-categories">
      <h2 class="panel-title">Manage Categories</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;">
        <!-- Add -->
        <div class="card-form">
          <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--navy);margin-bottom:20px;">Add New Category</h3>
          <form method="post">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="add_category">
            <div class="form-group">
              <label>Category Name</label>
              <input type="text" name="category_name" required maxlength="50" placeholder="e.g. Tourism_History" pattern="[a-zA-Z0-9_ ]{2,50}">
              <small style="color:var(--text-muted);font-size:.78rem;">Letters, numbers, spaces, underscores only</small>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Create Category</button>
          </form>
        </div>
        <!-- Delete -->
        <div class="card-form">
          <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--navy);margin-bottom:20px;">Delete Category</h3>
          <form method="post" onsubmit="return confirm('WARNING: This will delete the category AND all its notes. Are you sure?')">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="delete_category">
            <div class="form-group">
              <label>Select Category</label>
              <select name="category" required>
                <option value="">— Choose —</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete Category</button>
          </form>
        </div>
      </div>

      <!-- Category List -->
      <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--navy);margin:32px 0 16px;">All Categories</h3>
      <table class="data-table">
        <thead><tr><th>Category</th><th>Notes Count</th></tr></thead>
        <tbody>
          <?php foreach ($grouped as $cat => $notes): ?>
          <tr>
            <td><?= htmlspecialchars($cat) ?></td>
            <td><?= count($notes) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ── Visit Log Panel ────────────────────────────────────────────── -->
    <div class="admin-panel" id="panel-visits">
      <h2 class="panel-title">Visit Analytics</h2>
      <div class="stat-card-grid" style="margin-bottom:32px;">
        <div class="stat-card"><div class="num"><?= number_format($visitStats['today']) ?></div><div class="lbl">Today</div></div>
        <div class="stat-card"><div class="num"><?= number_format($visitStats['week']) ?></div><div class="lbl">This Week</div></div>
        <div class="stat-card"><div class="num"><?= number_format($visitStats['month']) ?></div><div class="lbl">This Month</div></div>
        <div class="stat-card"><div class="num"><?= number_format($visitStats['total']) ?></div><div class="lbl">All Time</div></div>
      </div>
      <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--navy);margin-bottom:16px;">Recent Visitors (last 10)</h3>
      <table class="data-table">
        <thead><tr><th>IP Address</th><th>Visited At</th></tr></thead>
        <tbody>
          <?php foreach ($recentVisits as $v): ?>
          <tr><td><?= htmlspecialchars($v['ip_address']) ?></td><td><?= htmlspecialchars($v['visited_at']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ── Download Log Panel ─────────────────────────────────────────── -->
    <div class="admin-panel" id="panel-downloads">
      <h2 class="panel-title">Download Log</h2>
      <div class="stat-card-grid" style="margin-bottom:32px;">
        <div class="stat-card" style="border-top-color:#2d9c6a"><div class="num"><?= number_format($totalDl) ?></div><div class="lbl">Total Downloads</div></div>
      </div>
      <table class="data-table">
        <thead><tr><th>Note</th><th>File</th><th>IP</th><th>Time</th></tr></thead>
        <tbody>
          <?php foreach ($recentDl as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['note_name']) ?></td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= htmlspecialchars($d['file_name']) ?></td>
            <td><?= htmlspecialchars($d['ip_address']) ?></td>
            <td style="font-size:.82rem"><?= htmlspecialchars($d['downloaded_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ── Settings Panel ─────────────────────────────────────────────── -->
    <div class="admin-panel" id="panel-settings">
      <h2 class="panel-title">System Settings</h2>
      <div style="max-width:560px;">
        <form method="post">
          <?= Security::csrfField() ?>
          <input type="hidden" name="action" value="save_settings">
          <div class="card-form">
            <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--navy);margin-bottom:20px;"><i class="fas fa-robot" style="color:var(--gold)"></i> Groq AI Configuration</h3>
            <div class="form-group">
              <label>Groq API Key</label>
              <input type="password" name="groq_api_key" placeholder="gsk_…" value="<?= htmlspecialchars($currentGroqKey) ?>" autocomplete="off">
              <small style="color:var(--text-muted);font-size:.78rem;">Get your free API key from <a href="https://console.groq.com" target="_blank" style="color:var(--gold)">console.groq.com</a>. Powers the RAG chatbot.</small>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Settings</button>
          </div>
        </form>

        <div class="card-form" style="margin-top:24px;">
          <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--navy);margin-bottom:16px;"><i class="fas fa-info-circle" style="color:var(--gold)"></i> System Info</h3>
          <table style="width:100%;font-size:.88rem;">
            <tr><td style="color:var(--text-muted);padding:6px 0">PHP Version</td><td><?= phpversion() ?></td></tr>
            <tr><td style="color:var(--text-muted);padding:6px 0">Upload Directory</td><td style="font-size:.78rem"><?= UPLOAD_DIR ?></td></tr>
            <tr><td style="color:var(--text-muted);padding:6px 0">Upload Dir Writable</td><td><?= is_writable(UPLOAD_DIR) ? '<span style="color:var(--success)">✔ Yes</span>' : '<span style="color:var(--danger)">✘ No</span>' ?></td></tr>
            <tr><td style="color:var(--text-muted);padding:6px 0">Groq API Key Set</td><td><?= !empty($currentGroqKey) ? '<span style="color:var(--success)">✔ Yes</span>' : '<span style="color:var(--danger)">✘ Not set</span>' ?></td></tr>
            <tr><td style="color:var(--text-muted);padding:6px 0">App Version</td><td><?= APP_VERSION ?></td></tr>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="assets/js/main.js"></script>
<script>
function showPanel(name) {
  document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
  document.getElementById('panel-' + name)?.classList.add('active');
  event.currentTarget?.classList.add('active');
}

// File drop zone
const dropZone  = document.getElementById('file-drop');
const fileInput = document.getElementById('file-input');
const fileLabel = document.getElementById('file-chosen');
fileInput?.addEventListener('change', () => {
  fileLabel.textContent = fileInput.files[0]?.name ?? '';
});
dropZone?.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = 'var(--gold)'; });
dropZone?.addEventListener('dragleave', () => { dropZone.style.borderColor = ''; });
dropZone?.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.style.borderColor = '';
  if (e.dataTransfer.files[0]) {
    fileInput.files = e.dataTransfer.files;
    fileLabel.textContent = e.dataTransfer.files[0].name;
  }
});
</script>
</body>
</html>
