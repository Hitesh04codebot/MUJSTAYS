<?php
// owner/add-listing.php — Multi-step PG listing form
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';
require_once '../includes/upload_handler.php';

require_auth('owner');
$uid = current_user_id();
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

        // Images
        $images_uploaded = [];
        if (!empty($_FILES['pg_images']['name'][0])) {
            try {
                $images_uploaded = handle_multiple_uploads($_FILES['pg_images'], 'pg_images/'.$uid, true);
            } catch (Exception $e) { $errors[] = 'Image upload failed: ' . $e->getMessage(); }
        }
        if (count($images_uploaded) < 3 && empty($errors)) $errors[] = 'Please upload at least 3 photos.';

        if (empty($errors)) {
            // Find or insert the area safely
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
            $slug = unique_slug($pdo, make_slug($title));

            $room_prices_all = array_values(array_filter(array_map('intval', $room_prices)));
            $price_min = !empty($room_prices_all) ? min($room_prices_all) : 0;
            $price_max = !empty($room_prices_all) ? max($room_prices_all) : 0;

            $stmt = $pdo->prepare("INSERT INTO pg_listings (owner_id,area_id,title,slug,description,address,latitude,longitude,distance_from_muj,price_min,price_max,gender_preference,has_food,has_wifi,has_ac,has_parking,has_laundry,has_gym,has_cctv,has_warden,has_transport,rules,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");
            $stmt->execute([$uid,$area_id,$title,$slug,$description,$address,$lat,$lng,$distance,$price_min,$price_max,$gender,$am_values['has_food'],$am_values['has_wifi'],$am_values['has_ac'],$am_values['has_parking'],$am_values['has_laundry'],$am_values['has_gym'],$am_values['has_cctv'],$am_values['has_warden'],$am_values['has_transport'],$rules]);
            $pg_id = $pdo->lastInsertId();

            // Room types
            foreach ($room_titles as $i => $rtype) {
                if (!$rtype) continue;
                $pdo->prepare("INSERT INTO room_types (pg_id,type,price_per_month,security_deposit,total_beds,available_beds) VALUES (?,?,?,?,?,?)")
                    ->execute([$pg_id,$rtype,(int)($room_prices[$i]??0),(int)($room_deposits[$i]??0),(int)($room_total[$i]??0),(int)($room_avail[$i]??0)]);
            }

            // Images
            foreach ($images_uploaded as $idx => $path) {
                $pdo->prepare("INSERT INTO pg_images (pg_id,file_path,is_cover,sort_order) VALUES (?,?,?,?)")
                    ->execute([$pg_id, $path, $idx===0?1:0, $idx]);
            }

            // Notify admin
            create_notification($pdo, 1, 'listing_approved', 'New listing submitted', "New PG listing '$title' awaiting approval.", '/admin/listings.php');

            flash_set('success', 'Listing submitted! It will be reviewed by our team within 24 hours.');
            redirect(BASE_URL . '/owner/listings.php');
        }
    }
}

// Fetch areas from DB
$areas_db = $pdo->query("SELECT * FROM areas ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Listing — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
</head><body>
<?php require_once '../components/navbar.php'; ?>

<div class="page-header" style="padding:40px 0">
  <div class="container">
    <h1>Add New PG Listing</h1>
    <p>Complete all steps to submit your listing for approval</p>
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

    <form method="POST" enctype="multipart/form-data" id="add-listing-form">
      <?= csrf_field() ?>

      <!-- STEP 1: Basic Info -->
      <div class="form-step card" id="step-0">
        <div class="card-body">
          <h3 style="margin-bottom:24px"><i class="fas fa-info-circle" style="color:var(--accent)"></i> Basic Information</h3>
          <div class="form-group">
            <label class="form-label">PG Title <span class="req">*</span></label>
            <input type="text" name="title" class="form-control" placeholder='e.g. "Shanti Girls PG Near MUJ Gate 2"' value="<?= htmlspecialchars($_POST['title']??'') ?>" required>
            <div class="form-text">Be specific — include location and target gender for better visibility</div>
          </div>
          <div class="form-group">
            <label class="form-label">Description <span class="req">*</span></label>
            <textarea name="description" class="form-control" rows="5" placeholder="Describe your PG in detail — facilities, nearby landmarks, what makes it special..." required><?= htmlspecialchars($_POST['description']??'') ?></textarea>
            <div class="form-text">Minimum 50 characters. Mention major MUJ proximity points.</div>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">Full Address <span class="req">*</span></label>
              <input type="text" name="address" class="form-control" placeholder="Plot no., Street, Colony, Jaipur - PINCODE" value="<?= htmlspecialchars($_POST['address']??'') ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Area / Locality <span class="req">*</span></label>
              <input type="text" name="area_name" class="form-control" placeholder="e.g. Jagatpura, Sitapura" value="<?= htmlspecialchars($_POST['area_name']??'') ?>" required>
            </div>
          </div>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">Latitude <span class="req">*</span></label>
              <input type="number" name="latitude" id="auto_lat" class="form-control" step="0.00000001" placeholder="e.g. 26.84582" value="<?= $_POST['latitude']??'' ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Longitude <span class="req">*</span></label>
              <input type="number" name="longitude" id="auto_lng" class="form-control" step="0.00000001" placeholder="e.g. 75.80123" value="<?= $_POST['longitude']??'' ?>" required>
            </div>
          </div>
          <div style="margin-bottom: 24px; margin-top: -12px;">
            <button type="button" id="getLocationBtn" class="btn btn-outline btn-sm" onclick="getRealLocation()" style="font-weight: 600;"><i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> Auto-Detect My Real Location</button>
            <span style="font-size: 12px; color: var(--text-muted); margin-left: 8px;">(Click while standing at your PG)</span>
            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleMapPicker()" style="display:block; margin-top: 8px; color: var(--primary);"><i class="fas fa-map"></i> Or Select Manually on Map</button>
            <div id="map-picker" style="height:350px; width:100%; margin-top:12px; border-radius:12px; border:1px solid var(--border); display:none; z-index:10; box-shadow: var(--shadow-sm);"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Gender Preference</label>
            <div style="display:flex;gap:12px">
              <?php foreach(['any'=>'⚥ Co-ed','male'=>'♂ Boys Only','female'=>'♀ Girls Only'] as $v=>$l): ?>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="radio" name="gender_preference" value="<?= $v ?>" <?= ($_POST['gender_preference']??'any')===$v?'checked':'' ?>> <?= $l ?></label>
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
          <p style="color:var(--text-muted)">Add at least one room type. You can add multiple types with different prices.</p>
          <div id="room-rows">
            <?php $room_types_opts = ['single','double','triple','dormitory']; ?>
            <div class="room-row" style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:12px;border:1px solid var(--border)">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <strong style="color:var(--primary)">Room Type 1</strong>
              </div>
              <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr 1fr">
                <div class="form-group"><label class="form-label">Type</label>
                  <select name="room_type[]" class="form-select">
                    <?php foreach($room_types_opts as $rt): ?><option value="<?= $rt ?>"><?= ucfirst($rt) ?></option><?php endforeach; ?>
                  </select></div>
                <div class="form-group"><label class="form-label">Price/Month (₹)</label><input type="number" name="room_price[]" class="form-control" placeholder="8000" min="0"></div>
                <div class="form-group"><label class="form-label">Security Deposit (₹)</label><input type="number" name="room_deposit[]" class="form-control" placeholder="10000" min="0"></div>
                <div class="form-group"><label class="form-label">Total Beds</label><input type="number" name="room_total_beds[]" class="form-control" placeholder="4" min="1"></div>
                <div class="form-group"><label class="form-label">Available Beds</label><input type="number" name="room_available_beds[]" class="form-control" placeholder="4" min="0"></div>
              </div>
            </div>
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
              <input type="checkbox" name="<?= $k ?>" value="1" <?= !empty($_POST[$k])?'checked':'' ?>>
              <span><?= $v ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="form-group">
            <label class="form-label">House Rules & Policies</label>
            <textarea name="rules" class="form-control" rows="4" placeholder="e.g. Gate closes at 10 PM, no alcohol, vegetarian food only, guest policy..."><?= htmlspecialchars($_POST['rules']??'') ?></textarea>
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
          <p style="color:var(--text-muted)">Upload at least <strong>3 photos</strong>. The first photo will be the cover image. Supported: JPG, PNG, WebP — max 10MB each.</p>
          <div class="upload-zone" id="upload-zone" onclick="document.getElementById('pg_images').click()">
            <i class="fas fa-cloud-upload-alt"></i>
            <div style="font-weight:600;color:var(--primary);margin-bottom:4px">Click or drag photos here</div>
            <div style="font-size:13px;color:var(--text-muted)">JPG, PNG, WebP · Max 10MB each · Min 3 photos</div>
          </div>
          <input type="file" id="pg_images" name="pg_images[]" multiple accept="image/*" style="display:none">
          <div class="upload-previews" id="upload-previews"></div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost prev-step">← Back</button>
          <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-paper-plane"></i> Submit Listing for Review</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once '../components/footer.php'; ?>
<script>
var BASE_URL='<?= BASE_URL ?>';
var roomCount=1;
document.getElementById('add-room-btn')?.addEventListener('click',function(){
  roomCount++;
  const tpl=`<div class="room-row" style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:12px;border:1px solid var(--border)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <strong style="color:var(--primary)">Room Type ${roomCount}</strong>
      <button type="button" class="btn btn-ghost btn-sm" onclick="this.closest('.room-row').remove()" style="color:var(--danger)"><i class="fas fa-trash"></i></button>
    </div>
    <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr 1fr">
      <div class="form-group"><label class="form-label">Type</label><select name="room_type[]" class="form-select"><option value="single">Single</option><option value="double">Double</option><option value="triple">Triple</option><option value="dormitory">Dormitory</option></select></div>
      <div class="form-group"><label class="form-label">Price/Month (₹)</label><input type="number" name="room_price[]" class="form-control" placeholder="8000" min="0"></div>
      <div class="form-group"><label class="form-label">Security Deposit (₹)</label><input type="number" name="room_deposit[]" class="form-control" placeholder="10000" min="0"></div>
      <div class="form-group"><label class="form-label">Total Beds</label><input type="number" name="room_total_beds[]" class="form-control" placeholder="4" min="1"></div>
      <div class="form-group"><label class="form-label">Available Beds</label><input type="number" name="room_available_beds[]" class="form-control" placeholder="4" min="0"></div>
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

// Step indicator sync
document.querySelectorAll('.step-indicator').forEach((ind,i)=>{
  ind.addEventListener('click',()=>{
    document.querySelectorAll('.form-step').forEach((s,j)=>s.style.display=j===i?'block':'none');
    document.querySelectorAll('.step-indicator').forEach((si,j)=>{si.style.background=j===i?'var(--accent)':''; si.style.color=j===i?'#fff':'var(--text-muted)';});
    window.scrollTo(0, 0);
  });
});

// Next and Back buttons
document.querySelectorAll('.next-step').forEach(btn => {
  btn.addEventListener('click', function() {
    const currentObj = this.closest('.form-step');
    const currentIdx = parseInt(currentObj.id.replace('step-',''));
    document.querySelector(`.step-indicator[data-step="${currentIdx + 1}"]`)?.click();
  });
});
document.querySelectorAll('.prev-step').forEach(btn => {
  btn.addEventListener('click', function() {
    const currentObj = this.closest('.form-step');
    const currentIdx = parseInt(currentObj.id.replace('step-',''));
    document.querySelector(`.step-indicator[data-step="${currentIdx - 1}"]`)?.click();
  });
});

// Get Real Location using GPS
function getRealLocation() {
  const btn = document.getElementById('getLocationBtn');
  if (navigator.geolocation) {
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Detecting...';
    navigator.geolocation.getCurrentPosition(
      (position) => {
        document.getElementById('auto_lat').value = position.coords.latitude.toFixed(6);
        document.getElementById('auto_lng').value = position.coords.longitude.toFixed(6);
        btn.innerHTML = '<i class="fas fa-check"></i> Location Detected & Autofilled!';
        btn.classList.remove('btn-outline');
        btn.classList.add('btn-success');
        setTimeout(() => {
          btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Redetect Location';
          btn.classList.remove('btn-success');
          btn.classList.add('btn-outline');
        }, 3000);
      },
      (error) => {
        alert('Permission Denied or Error getting location. Please make sure location services are enabled on your device/browser and try again.');
        btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Auto-Detect My Real Location';
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
  } else {
    alert("Geolocation is not supported by this browser.");
  }
}

// Leaflet Map Picker Initialization
let map, marker;
function toggleMapPicker() {
  const mapDiv = document.getElementById('map-picker');
  if (mapDiv.style.display === 'none') {
    mapDiv.style.display = 'block';
    
    setTimeout(() => { // Initialize slightly after rendering to ensure sizing
      if (!map) {
        // Default to MUJ Location if inputs are empty
        const startLat = parseFloat(document.getElementById('auto_lat').value) || 26.8467;
        const startLng = parseFloat(document.getElementById('auto_lng').value) || 75.5664;

        map = L.map('map-picker').setView([startLat, startLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([startLat, startLng], {draggable: true}).addTo(map);
        
        // Auto-fill coordinates heavily when the marker is dragged
        marker.on('dragend', function (e) {
          const position = marker.getLatLng();
          document.getElementById('auto_lat').value = position.lat.toFixed(6);
          document.getElementById('auto_lng').value = position.lng.toFixed(6);
          
          // Add a subtle success flash to the inputs so the user knows they were filled
          const latInput = document.getElementById('auto_lat');
          const lngInput = document.getElementById('auto_lng');
          latInput.style.backgroundColor = '#e8f5e9';
          lngInput.style.backgroundColor = '#e8f5e9';
          setTimeout(() => {
              latInput.style.backgroundColor = '';
              lngInput.style.backgroundColor = '';
          }, 800);
        });

        // Click anywhere to move marker
        map.on('click', function(e) {
          marker.setLatLng(e.latlng);
          document.getElementById('auto_lat').value = e.latlng.lat.toFixed(6);
          document.getElementById('auto_lng').value = e.latlng.lng.toFixed(6);
        });
      }
      map.invalidateSize(); // Ensure tiles load correctly
    }, 100);
  } else {
    mapDiv.style.display = 'none';
  }
}

</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
