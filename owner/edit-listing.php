<?php
// owner/edit-listing.php — Edit existing PG listing
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';
require_once '../includes/upload_handler.php';

require_auth('owner');
$uid = current_user_id();
$lid = (int)($_GET['id'] ?? 0);

if (!$lid) { redirect(BASE_URL . '/owner/listings.php'); }

// Verify ownership
$stmt = $pdo->prepare("SELECT p.*, a.name AS area_name FROM pg_listings p JOIN areas a ON a.id = p.area_id WHERE p.id = ? AND p.owner_id = ? AND p.is_deleted = 0");
$stmt->execute([$lid, $uid]);
$listing = $stmt->fetch();

if (!$listing) {
    flash_set('error', 'Listing not found or access denied.');
    redirect(BASE_URL . '/owner/listings.php');
}

// Fetch existing room types
$room_types_existing = $pdo->prepare("SELECT * FROM room_types WHERE pg_id = ?");
$room_types_existing->execute([$lid]);
$room_types_existing = $room_types_existing->fetchAll();

// Fetch existing images
$images_existing = $pdo->prepare("SELECT * FROM pg_images WHERE pg_id = ? ORDER BY sort_order ASC");
$images_existing->execute([$lid]);
$images_existing = $images_existing->fetchAll();

$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $errors[] = 'Invalid request.'; }
    else {
        // Sanitize all inputs
        $title       = sanitize($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $address     = sanitize($_POST['address'] ?? '');
        $area_name   = sanitize($_POST['area_name'] ?? '');
        $lat         = (float)($_POST['latitude'] ?? 26.8467);
        $lng         = (float)($_POST['longitude'] ?? 75.5664);
        $gender      = in_array($_POST['gender_preference']??'', ['male','female','any']) ? $_POST['gender_preference'] : 'any';
        $rules       = trim($_POST['rules'] ?? '');

        // Amenities
        $amenities = ['has_food','has_wifi','has_ac','has_parking','has_laundry','has_gym','has_cctv','has_warden','has_transport'];
        $am_values  = [];
        foreach ($amenities as $a) $am_values[$a] = isset($_POST[$a]) ? 1 : 0;

        // Validation
        if (!$title) $errors[] = 'PG Title is required.';
        if (!$description || strlen($description) < 50) $errors[] = 'Description must be at least 50 characters.';
        if (!$address) $errors[] = 'Address is required.';
        if (!$area_name) $errors[] = 'Please provide a valid area layout.';

        // Room types
        $room_titles = $_POST['room_type'] ?? [];
        $room_prices = $_POST['room_price'] ?? [];
        $room_deposits = $_POST['room_deposit'] ?? [];
        $room_total = $_POST['room_total_beds'] ?? [];
        $room_avail = $_POST['room_available_beds'] ?? [];
        if (empty($room_titles)) $errors[] = 'At least one room type is required.';

        // Handle Image Deletion
        if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $img_id) {
                $img_stmt = $pdo->prepare("SELECT file_path FROM pg_images WHERE id = ? AND pg_id = ?");
                $img_stmt->execute([(int)$img_id, $lid]);
                $img_row = $img_stmt->fetch();
                if ($img_row) {
                    @unlink('../' . $img_row['file_path']);
                    $pdo->prepare("DELETE FROM pg_images WHERE id = ?")->execute([(int)$img_id]);
                }
            }
        }

        // New Images Upload
        $images_uploaded = [];
        if (!empty($_FILES['pg_images']['name'][0])) {
            try {
                $images_uploaded = handle_multiple_uploads($_FILES['pg_images'], 'pg_images/'.$uid, true);
            } catch (Exception $e) { $errors[] = 'Image upload failed: ' . $e->getMessage(); }
        }

        if (empty($errors)) {
            // Update Area
            $stmt = $pdo->prepare("SELECT id FROM areas WHERE name = ?");
            $stmt->execute([$area_name]);
            $area_row = $stmt->fetch();
            if ($area_row) {
                $area_id = $area_row['id'];
            } else {
                $pdo->prepare("INSERT INTO areas (name) VALUES (?)")->execute([$area_name]);
                $area_id = $pdo->lastInsertId();
            }

            $distance = distance_from_muj($lat, $lng);
            
            // Check if title changed for slug uniqueness
            if ($title !== $listing['title']) {
                $slug = unique_slug($pdo, make_slug($title));
            } else {
                $slug = $listing['slug'];
            }

            $room_prices_all = array_values(array_filter(array_map('intval', $room_prices)));
            $price_min = !empty($room_prices_all) ? min($room_prices_all) : 0;
            $price_max = !empty($room_prices_all) ? max($room_prices_all) : 0;

            // Update Listing (reset status to pending on edit)
            $stmt = $pdo->prepare("UPDATE pg_listings SET 
                area_id=?, title=?, slug=?, description=?, address=?, latitude=?, longitude=?, 
                distance_from_muj=?, price_min=?, price_max=?, gender_preference=?, 
                has_food=?, has_wifi=?, has_ac=?, has_parking=?, has_laundry=?, has_gym=?, has_cctv=?, has_warden=?, has_transport=?,
                rules=?, status='pending' 
                WHERE id=? AND owner_id=?");
            $stmt->execute([
                $area_id, $title, $slug, $description, $address, $lat, $lng, 
                $distance, $price_min, $price_max, $gender, 
                $am_values['has_food'], $am_values['has_wifi'], $am_values['has_ac'], 
                $am_values['has_parking'], $am_values['has_laundry'], $am_values['has_gym'], 
                $am_values['has_cctv'], $am_values['has_warden'], $am_values['has_transport'],
                $rules, $lid, $uid
            ]);

            // Sync Room types (Replace simple: delete old, insert new)
            $pdo->prepare("DELETE FROM room_types WHERE pg_id = ?")->execute([$lid]);
            foreach ($room_titles as $i => $rtype) {
                if (!$rtype) continue;
                $pdo->prepare("INSERT INTO room_types (pg_id,type,price_per_month,security_deposit,total_beds,available_beds) VALUES (?,?,?,?,?,?)")
                    ->execute([$lid,$rtype,(int)($room_prices[$i]??0),(int)($room_deposits[$i]??0),(int)($room_total[$i]??0),(int)($room_avail[$i]??0)]);
            }

            // Sync Images
            foreach ($images_uploaded as $idx => $path) {
                // If there are no current cover images, set first upload as cover
                $has_cover = $pdo->prepare("SELECT count(*) FROM pg_images WHERE pg_id=? AND is_cover=1");
                $has_cover->execute([$lid]);
                $is_cover = ($has_cover->fetchColumn() == 0 && $idx === 0) ? 1 : 0;
                
                $pdo->prepare("INSERT INTO pg_images (pg_id,file_path,is_cover,sort_order) VALUES (?,?,?,?)")
                    ->execute([$lid, $path, $is_cover, 10 + $idx]);
            }

            flash_set('success', 'Listing updated. Your changes are under review.');
            redirect(BASE_URL . '/owner/listings.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Listing — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
</head><body>
<?php require_once '../components/navbar.php'; ?>

<div class="page-header" style="padding:40px 0">
  <div class="container">
    <h1>Edit PG Listing</h1>
    <p>Modify details for "<?= htmlspecialchars($listing['title']) ?>"</p>
  </div>
</div>

<div class="section-sm">
  <div class="container-sm">
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error" style="margin-bottom:24px">
      <div><strong><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</strong>
        <ul style="margin:8px 0 0 16px"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    </div>
    <?php endif; ?>

    <!-- Step indicators -->
    <div style="display:flex;gap:0;margin-bottom:32px;background:var(--bg2);border-radius:var(--radius-lg);overflow:hidden">
      <?php $steps_labels = ['Basic Info','Rooms & Pricing','Amenities & Rules','Photos']; foreach($steps_labels as $i=>$lbl): ?>
      <div class="step-indicator" data-step="<?= $i ?>" style="flex:1;padding:14px;text-align:center;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;<?= $i===0?'background:var(--accent);color:#fff':'color:var(--text-muted)' ?>">
        <div style="font-size:18px;margin-bottom:2px"><?= ['📝','🛏','✅','📸'][$i] ?></div>
        <div><?= $lbl ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" id="edit-listing-form">
      <?= csrf_field() ?>

      <!-- STEP 1: Basic Info -->
      <div class="form-step card" id="step-0">
        <div class="card-body">
          <h3 style="margin-bottom:24px"><i class="fas fa-info-circle" style="color:var(--accent)"></i> Basic Information</h3>
          <div class="form-group">
            <label class="form-label">PG Title <span class="req">*</span></label>
            <input type="text" name="title" class="form-control" placeholder='e.g. "Shanti Girls PG Near MUJ Gate 2"' value="<?= htmlspecialchars($_POST['title']??$listing['title']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Description <span class="req">*</span></label>
            <textarea name="description" class="form-control" rows="5" placeholder="Describe your PG in detail..." required><?= htmlspecialchars($_POST['description']??$listing['description']) ?></textarea>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">Full Address <span class="req">*</span></label>
              <input type="text" name="address" class="form-control" placeholder="Plot no., Street, Colony..." value="<?= htmlspecialchars($_POST['address']??$listing['address']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Area / Locality <span class="req">*</span></label>
              <input type="text" name="area_name" class="form-control" placeholder="e.g. Jagatpura" value="<?= htmlspecialchars($_POST['area_name']??$listing['area_name']) ?>" required>
            </div>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">Latitude <span class="req">*</span></label>
              <input type="number" name="latitude" id="auto_lat" class="form-control" step="0.00000001" value="<?= $_POST['latitude']??$listing['latitude'] ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Longitude <span class="req">*</span></label>
              <input type="number" name="longitude" id="auto_lng" class="form-control" step="0.00000001" value="<?= $_POST['longitude']??$listing['longitude'] ?>" required>
            </div>
          </div>
          <div style="margin-bottom: 24px;">
            <button type="button" id="getLocationBtn" class="btn btn-outline btn-sm" onclick="getRealLocation()"><i class="fas fa-map-marker-alt"></i> Redetect Location</button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleMapPicker()"><i class="fas fa-map"></i> View Map Picker</button>
            <div id="map-picker" style="height:350px; width:100%; margin-top:12px; border-radius:12px; border:1px solid var(--border); display:none;"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Gender Preference</label>
            <div style="display:flex;gap:12px">
              <?php foreach(['any'=>'⚥ Co-ed','male'=>'♂ Boys Only','female'=>'♀ Girls Only'] as $v=>$l): ?>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="radio" name="gender_preference" value="<?= $v ?>" <?= ($_POST['gender_preference']??$listing['gender_preference'])===$v?'checked':'' ?>> <?= $l ?></label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end">
          <button type="button" class="btn btn-primary next-step">Next: Rooms & Pricing →</button>
        </div>
      </div>

      <!-- STEP 2: Room Types -->
      <div class="form-step card" id="step-1" style="display:none">
        <div class="card-body">
          <h3 style="margin-bottom:24px"><i class="fas fa-bed" style="color:var(--accent)"></i> Room Types & Pricing</h3>
          <div id="room-rows">
            <?php 
            $rooms_data = !empty($_POST['room_type']) ? array_map(null, $_POST['room_type'], $_POST['room_price'], $_POST['room_deposit'], $_POST['room_total_beds'], $_POST['room_available_beds']) : (array)$room_types_existing;
            $room_types_opts = ['single','double','triple','dormitory'];
            foreach($rooms_data as $i=>$r): 
              if (is_array($r)) { // From Database
                $rt = $r['type']; $rp = $r['price_per_month']; $rd = $r['security_deposit']; $rtot = $r['total_beds']; $rav = $r['available_beds'];
              } else { // From POST (placeholder - actually POST is structured differently)
                $rt = $_POST['room_type'][$i]; $rp = $_POST['room_price'][$i]; $rd = $_POST['room_deposit'][$i]; $rtot = $_POST['room_total_beds'][$i]; $rav = $_POST['room_available_beds'][$i];
              }
            ?>
            <div class="room-row" style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:12px;border:1px solid var(--border)">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <strong style="color:var(--primary)">Room Type <?= $i+1 ?></strong>
                <?php if($i > 0): ?><button type="button" class="btn btn-ghost btn-sm" onclick="this.closest('.room-row').remove()" style="color:var(--danger)"><i class="fas fa-trash"></i></button><?php endif; ?>
              </div>
              <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr 1fr">
                <div class="form-group"><label class="form-label">Type</label>
                  <select name="room_type[]" class="form-select">
                    <?php foreach($room_types_opts as $opt): ?><option value="<?= $opt ?>" <?= $rt===$opt?'selected':'' ?>><?= ucfirst($opt) ?></option><?php endforeach; ?>
                  </select></div>
                <div class="form-group"><label class="form-label">Price/Month</label><input type="number" name="room_price[]" class="form-control" value="<?= $rp ?>" min="0"></div>
                <div class="form-group"><label class="form-label">Deposit</label><input type="number" name="room_deposit[]" class="form-control" value="<?= $rd ?>" min="0"></div>
                <div class="form-group"><label class="form-label">Total Beds</label><input type="number" name="room_total_beds[]" class="form-control" value="<?= $rtot ?>" min="1"></div>
                <div class="form-group"><label class="form-label">Avail Beds</label><input type="number" name="room_available_beds[]" class="form-control" value="<?= $rav ?>" min="0"></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" id="add-room-btn" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Add Another Room Type</button>
        </div>
        <div class="card-footer" style="display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost prev-step">← Back</button>
          <button type="button" class="btn btn-primary next-step">Next: Amenities →</button>
        </div>
      </div>

      <!-- STEP 3: Amenities -->
      <div class="form-step card" id="step-2" style="display:none">
        <div class="card-body">
          <h3 style="margin-bottom:24px"><i class="fas fa-list-ul" style="color:var(--accent)"></i> Amenities & Rules</h3>
          <div class="amenity-grid" style="margin-bottom:24px">
            <?php $ams=['has_wifi'=>'📶 Wi-Fi','has_ac'=>'❄️ Air Conditioning','has_food'=>'🍽️ Meals Included','has_parking'=>'🅿️ Parking','has_laundry'=>'👕 Laundry Service','has_gym'=>'💪 Gym Access','has_cctv'=>'📷 CCTV Security','has_warden'=>'👮 Warden on Premises','has_transport'=>'🚐 Pick and Drop'];
            foreach($ams as $k=>$v): ?>
            <label class="amenity-checkbox">
              <input type="checkbox" name="<?= $k ?>" value="1" <?= ($_POST[$k]??$listing[$k])?'checked':'' ?>>
              <span><?= $v ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="form-group">
            <label class="form-label">House Rules & Policies</label>
            <textarea name="rules" class="form-control" rows="4"><?= htmlspecialchars($_POST['rules']??$listing['rules']) ?></textarea>
          </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost prev-step">← Back</button>
          <button type="button" class="btn btn-primary next-step">Next: Photos →</button>
        </div>
      </div>

      <!-- STEP 4: Photos -->
      <div class="form-step card" id="step-3" style="display:none">
        <div class="card-body">
          <h3 style="margin-bottom:24px"><i class="fas fa-images" style="color:var(--accent)"></i> Photos</h3>
          
          <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(120px, 1fr));gap:12px;margin-bottom:24px">
            <?php foreach($images_existing as $img): ?>
              <div style="position:relative">
                <img src="<?= BASE_URL . '/' . $img['file_path'] ?>" alt="" style="width:100%;height:100px;object-fit:cover;border-radius:var(--radius-sm)">
                <label style="position:absolute;top:4px;right:4px;background:white;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;box-shadow:var(--shadow-sm)">
                  <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete
                </label>
                <?php if($img['is_cover']): ?><span style="position:absolute;bottom:4px;left:4px;background:var(--accent);color:white;font-size:9px;padding:1px 4px;border-radius:3px">Cover</span><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="upload-zone" id="upload-zone" onclick="document.getElementById('pg_images').click()">
            <i class="fas fa-cloud-upload-alt"></i>
            <div style="font-weight:600;color:var(--primary);margin-bottom:4px">Upload New Photos</div>
            <div style="font-size:13px;color:var(--text-muted)">JPG, PNG, WebP · Max 10MB each</div>
          </div>
          <input type="file" id="pg_images" name="pg_images[]" multiple accept="image/*" style="display:none">
          <div class="upload-previews" id="upload-previews"></div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost prev-step">← Back</button>
          <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once '../components/footer.php'; ?>
<script>
var BASE_URL='<?= BASE_URL ?>';
var roomCount=<?= count($rooms_data) ?>;
document.getElementById('add-room-btn')?.addEventListener('click',function(){
  roomCount++;
  const tpl=`<div class="room-row" style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:12px;border:1px solid var(--border)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <strong style="color:var(--primary)">Room Type ${roomCount}</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="this.closest('.room-row').remove()" style="color:var(--danger)"><i class="fas fa-trash"></i></button>
    </div>
    <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr 1fr">
      <div class="form-group"><label class="form-label">Type</label><select name="room_type[]" class="form-select"><option value="single">Single</option><option value="double">Double</option><option value="triple">Triple</option><option value="dormitory">Dormitory</option></select></div>
      <div class="form-group"><label class="form-label">Price/Month</label><input type="number" name="room_price[]" class="form-control" placeholder="8000" min="0"></div>
      <div class="form-group"><label class="form-label">Deposit</label><input type="number" name="room_deposit[]" class="form-control" placeholder="10000" min="0"></div>
      <div class="form-group"><label class="form-label">Total Beds</label><input type="number" name="room_total_beds[]" class="form-control" placeholder="4" min="1"></div>
      <div class="form-group"><label class="form-label">Avail Beds</label><input type="number" name="room_available_beds[]" class="form-control" placeholder="4" min="0"></div>
    </div></div>`;
  document.getElementById('room-rows').insertAdjacentHTML('beforeend',tpl);
});

let uploadedFiles = [];
document.getElementById('pg_images')?.addEventListener('change', function() {
  const newFiles = Array.from(this.files);
  uploadedFiles = uploadedFiles.concat(newFiles);
  const dt = new DataTransfer();
  uploadedFiles.forEach(file => dt.items.add(file));
  this.files = dt.files;
  const prev = document.getElementById('upload-previews'); 
  prev.innerHTML = '';
  uploadedFiles.forEach(f => {
    const r = new FileReader(); 
    r.onload = e => {
      prev.insertAdjacentHTML('beforeend', `<div class="upload-preview" style="position:relative;display:inline-block;margin:5px;">
        <img src="${e.target.result}" alt="" style="width:100px;height:100px;object-fit:cover;border-radius:8px;">
      </div>`);
    }; 
    r.readAsDataURL(f);
  });
});

// Drag and drop
const zone=document.getElementById('upload-zone');
zone?.addEventListener('dragover',e=>{e.preventDefault();zone.classList.add('dragover')});
zone?.addEventListener('dragleave',()=>zone.classList.remove('dragover'));
zone?.addEventListener('drop',e=>{e.preventDefault();zone.classList.remove('dragover');document.getElementById('pg_images').files=e.dataTransfer.files;document.getElementById('pg_images').dispatchEvent(new Event('change'))});

// Step indicators
document.querySelectorAll('.step-indicator').forEach((ind,i)=>{
  ind.addEventListener('click',()=>{
    document.querySelectorAll('.form-step').forEach((s,j)=>s.style.display=j===i?'block':'none');
    document.querySelectorAll('.step-indicator').forEach((si,j)=>{si.style.background=j===i?'var(--accent)':''; si.style.color=j===i?'#fff':'var(--text-muted)';});
    window.scrollTo(0,0);
  });
});
document.querySelectorAll('.next-step').forEach(btn=>{btn.addEventListener('click',function(){const i=parseInt(this.closest('.form-step').id.replace('step-',''));document.querySelector(`.step-indicator[data-step="${i+1}"]`)?.click();});});
document.querySelectorAll('.prev-step').forEach(btn=>{btn.addEventListener('click',function(){const i=parseInt(this.closest('.form-step').id.replace('step-',''));document.querySelector(`.step-indicator[data-step="${i-1}"]`)?.click();});});

// GPS Location
function getRealLocation() {
  const btn = document.getElementById('getLocationBtn');
  if (navigator.geolocation) {
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Detecting...';
    navigator.geolocation.getCurrentPosition((pos)=>{
      document.getElementById('auto_lat').value=pos.coords.latitude.toFixed(6);
      document.getElementById('auto_lng').value=pos.coords.longitude.toFixed(6);
      btn.innerHTML='<i class="fas fa-check"></i> Location Detected';
      setTimeout(()=>{btn.innerHTML='<i class="fas fa-map-marker-alt"></i> Redetect Location'},3000);
    },()=>{alert('Error getting location'); btn.innerHTML='<i class="fas fa-map-marker-alt"></i> Redetect Location';});
  }
}

// Map Picker
let map, marker;
function toggleMapPicker() {
  const div=document.getElementById('map-picker');
  if(div.style.display==='none'){
    div.style.display='block';
    setTimeout(()=>{
      if(!map){
        const lat=parseFloat(document.getElementById('auto_lat').value)||26.8467, lng=parseFloat(document.getElementById('auto_lng').value)||75.5664;
        map=L.map('map-picker').setView([lat,lng],15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        marker=L.marker([lat,lng],{draggable:true}).addTo(map);
        marker.on('dragend',()=>{const p=marker.getLatLng();document.getElementById('auto_lat').value=p.lat.toFixed(6);document.getElementById('auto_lng').value=p.lng.toFixed(6);});
        map.on('click',e=>{marker.setLatLng(e.latlng);document.getElementById('auto_lat').value=e.latlng.lat.toFixed(6);document.getElementById('auto_lng').value=e.latlng.lng.toFixed(6);});
      }
      map.invalidateSize();
    },100);
  } else div.style.display='none';
}
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
