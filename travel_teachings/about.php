<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'About Author';
include __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="page-banner-inner">
    <div class="breadcrumb"><a href="index.php">Home</a><span>/</span> About Author</div>
    <h1>About the Author</h1>
  </div>
</div>

<section class="section">
  <div class="section-inner">
    <div class="about-layout">
      <div class="author-card reveal">
        <img src="assets/images/R67.png" alt="Dr. Renu Malra">
        <div class="author-card-body">
          <div class="author-name">Dr. Renu Malra</div>
          <div class="author-title">Associate Professor, Tourism<br>Kurukshetra University</div>
          <div class="author-tags">
            <span class="tag">Sustainable Tourism</span>
            <span class="tag">E-Tourism</span>
            <span class="tag">Tourism Marketing</span>
            <span class="tag">Hospitality</span>
            <span class="tag">Entrepreneurship</span>
          </div>
          <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;">
            <a href="https://www.linkedin.com/in/renu-malra-405a3217" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="color:var(--navy);border-color:var(--grey-300);background:var(--grey-50);">
              <i class="fab fa-linkedin-in" style="color:#0077b5"></i> LinkedIn Profile
            </a>
            <a href="https://www.researchgate.net/profile/Renu-Malra" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="color:var(--navy);border-color:var(--grey-300);background:var(--grey-50);">
              <i class="fab fa-researchgate" style="color:#00d0af"></i> ResearchGate
            </a>
          </div>
        </div>
      </div>

      <div class="about-content reveal">
        <h2>Academic Profile</h2>
        <p>Dr. Renu Malra serves as Associate Professor in the Department of Tourism, Institute of Integrated and Honors Studies (Erstwhile University College), Kurukshetra University, Kurukshetra, Haryana, India — a position she has held since 2006.</p>
        <p>She has authored numerous books covering the fundamentals of tourism and the environmental impacts of tourism. Her extensive publication record spans edited books and reputed academic journals.</p>
        <p>Notable research contributions include papers on Environmental Impacts of Tourism (Case Study of Mussoorie), Impact of COVID-19 on the Tourism Industry, Blockchain in Tourism, and Implications of Metaverse in Tourism — all of which have achieved significant readership online.</p>

        <h2 style="margin-top:32px">Areas of Expertise</h2>
        <p>Her scholarly interests are centered on E-Tourism and Online Tourism Marketing, Entrepreneurship, Sustainable Tourism, and the Environmental Impacts of Tourism. She has developed specialisations in Tourism Business, E-Tourism, Sustainable Tourism, and Tourism Marketing.</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:24px;">
          <?php
          $highlights = [
            ['fas fa-university','Institution','Kurukshetra University, Haryana, India'],
            ['fas fa-briefcase','Experience','Teaching since 2006'],
            ['fas fa-book','Books','Multiple authored textbooks on Tourism'],
            ['fas fa-flask','Research','Published in journals & edited volumes'],
          ];
          foreach ($highlights as [$icon, $title, $val]):
          ?>
          <div style="background:var(--grey-50);border:1px solid var(--grey-100);border-radius:var(--radius);padding:16px;display:flex;gap:12px;align-items:flex-start;">
            <div style="width:36px;height:36px;background:var(--navy);color:var(--gold);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.85rem;">
              <i class="<?= $icon ?>"></i>
            </div>
            <div>
              <div style="font-weight:600;font-size:.85rem;color:var(--navy)"><?= $title ?></div>
              <div style="font-size:.82rem;color:var(--text-muted);margin-top:2px"><?= $val ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
