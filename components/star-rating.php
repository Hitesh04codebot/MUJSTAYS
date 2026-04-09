<?php
// components/star-rating.php — Star rating display and input widget
// Params: $rating (float), $mode ('display'|'input'), $name (for input mode), $pg_id (for display avg)

$rating = isset($rating) ? (float)$rating : 0;
$mode   = $mode   ?? 'display';
$name   = $name   ?? 'rating';
$size   = $size   ?? 'normal'; // 'small', 'normal', 'large'

$font_size = match($size) { 'small' => '13px', 'large' => '22px', default => '16px' };
?>
<?php if ($mode === 'display'): ?>
  <div class="stars" style="font-size:<?= $font_size ?>">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <?php if ($i <= floor($rating)): ?>
        <i class="fas fa-star"></i>
      <?php elseif ($i == ceil($rating) && ($rating - floor($rating)) >= 0.5): ?>
        <i class="fas fa-star-half-alt"></i>
      <?php else: ?>
        <i class="far fa-star" style="color:var(--border)"></i>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

<?php elseif ($mode === 'input'): ?>
  <div class="stars-input" id="star-input-<?= $name ?>">
    <?php for ($i = 5; $i >= 1; $i--): ?>
      <input type="radio" id="star<?= $i ?>-<?= $name ?>" name="<?= htmlspecialchars($name) ?>" value="<?= $i ?>">
      <label for="star<?= $i ?>-<?= $name ?>" title="<?= $i ?> star<?= $i !== 1 ? 's' : '' ?>">
        <i class="fas fa-star"></i>
      </label>
    <?php endfor; ?>
  </div>
  <style>
    .stars-input{display:flex;flex-direction:row-reverse;gap:4px}
    .stars-input input{display:none}
    .stars-input label{font-size:<?= $size === 'large' ? '32px' : '24px' ?>;color:var(--border);cursor:pointer;transition:color .15s}
    .stars-input input:checked ~ label,.stars-input label:hover,.stars-input label:hover ~ label{color:#F39C12}
  </style>
<?php endif; ?>
