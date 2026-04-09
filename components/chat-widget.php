<?php
// components/chat-widget.php — Embedded chat widget for user/chat.php and owner/chat.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$current_uid = (int)($_SESSION['user_id'] ?? 0);
if (!$current_uid) return;

$pdo_chat = get_db();

// Get all conversation partners
$stmt = $pdo_chat->prepare("
  SELECT DISTINCT
    CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS partner_id,
    u.name AS partner_name, u.profile_photo,
    m.pg_id,
    pg.title AS pg_title,
    MAX(m.sent_at) AS last_message_time,
    (SELECT message_text FROM messages WHERE (sender_id = partner_id AND receiver_id = ?) ORDER BY sent_at DESC LIMIT 1) AS last_msg,
    SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 AND m.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END THEN 1 ELSE 0 END) AS unread
  FROM messages m
  JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
  LEFT JOIN pg_listings pg ON pg.id = m.pg_id
  WHERE m.sender_id = ? OR m.receiver_id = ?
  GROUP BY partner_id, u.name, u.profile_photo, m.pg_id
  ORDER BY last_message_time DESC
");
$stmt->execute([$current_uid, $current_uid, $current_uid, $current_uid, $current_uid, $current_uid, $current_uid]);
$conversations = $stmt->fetchAll();

// Load active conversation if specified
$active_partner_id = (int)($_GET['with'] ?? ($conversations[0]['partner_id'] ?? 0));
$active_pg_id      = (int)($_GET['pg_id'] ?? ($conversations[0]['pg_id'] ?? 0));

$active_conv = null;
if ($active_partner_id) {
    // Try to find in existing conversations list
    foreach ($conversations as $c) {
        if ($c['partner_id'] == $active_partner_id && ($active_pg_id == 0 || $c['pg_id'] == $active_pg_id)) {
            $active_conv = $c;
            break;
        }
    }

    // If not found (new conversation), fetch partner info manually
    if (!$active_conv) {
        $stmt_u = $pdo_chat->prepare("SELECT name AS partner_name, profile_photo FROM users WHERE id = ?");
        $stmt_u->execute([$active_partner_id]);
        $u_info = $stmt_u->fetch();
        if ($u_info) {
            $active_conv = $u_info;
            $active_conv['partner_id'] = $active_partner_id;
            $active_conv['pg_id'] = $active_pg_id;
            $active_conv['pg_title'] = '';
            if ($active_pg_id) {
                $stmt_pg = $pdo_chat->prepare("SELECT title FROM pg_listings WHERE id = ?");
                $stmt_pg->execute([$active_pg_id]);
                $active_conv['pg_title'] = $stmt_pg->fetchColumn() ?: '';
            }
        }
    }
}

$messages = [];
if ($active_partner_id) {
    $stmt2 = $pdo_chat->prepare("
        SELECT m.*, u.name AS sender_name
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
        " . ($active_pg_id ? "AND m.pg_id = ?" : "") . "
        ORDER BY m.sent_at ASC LIMIT 100
    ");
    $params = [$current_uid, $active_partner_id, $active_partner_id, $current_uid];
    if ($active_pg_id) $params[] = $active_pg_id;
    $stmt2->execute($params);
    $messages = $stmt2->fetchAll();

    // Mark as read
    $pdo_chat->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0")
            ->execute([$active_partner_id, $current_uid]);
}

$last_msg_id = !empty($messages) ? end($messages)['id'] : 0;
?>
<div class="chat-layout">
  <!-- Conversation List -->
  <div class="chat-list">
    <div class="chat-list-header">
      <i class="fas fa-comments" style="color:var(--accent)"></i> Messages
    </div>
    <div class="chat-search-wrap" style="padding:10px 16px;border-bottom:1px solid var(--border);position:relative">
      <div class="input-group">
        <i class="fas fa-search input-icon" style="top:50%;left:12px;transform:translateY(-50%);font-size:13px"></i>
        <input type="text" id="owner-search" class="form-control" placeholder="Search owner name..." style="padding-left:34px;height:36px;font-size:13px;border-radius:20px">
      </div>
      <div id="search-results-list" style="display:none;position:absolute;left:16px;right:16px;top:100%;z-index:1000;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow-lg);margin-top:4px;overflow:hidden"></div>
    </div>
    <?php if (empty($conversations)): ?>
      <div style="padding:40px;text-align:center;color:var(--text-muted);font-size:14px">
        <div style="font-size:40px;margin-bottom:12px">💬</div>
        No conversations yet
      </div>
    <?php else: ?>
      <?php foreach ($conversations as $conv): ?>
      <a class="chat-item <?= $conv['partner_id'] == $active_partner_id ? 'active' : '' ?>"
         href="?with=<?= $conv['partner_id'] ?>&pg_id=<?= $conv['pg_id'] ?>"
         data-user-id="<?= $conv['partner_id'] ?>"
         data-user-name="<?= htmlspecialchars($conv['partner_name']) ?>"
         data-pg-id="<?= $conv['pg_id'] ?>">
        <div class="chat-avatar" style="background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;font-size:14px;font-weight:700">
          <?= strtoupper(mb_substr($conv['partner_name'], 0, 1)) ?>
        </div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;justify-content:space-between;align-items:baseline">
            <div class="chat-item-name"><?= htmlspecialchars($conv['partner_name']) ?></div>
            <div style="font-size:11px;color:var(--text-light)"><?= time_ago($conv['last_message_time']) ?></div>
          </div>
          <?php if ($conv['pg_title']): ?>
          <div style="font-size:11px;color:var(--accent);margin:1px 0">🏠 <?= htmlspecialchars(truncate($conv['pg_title'], 30)) ?></div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div class="chat-item-preview"><?= htmlspecialchars(truncate($conv['last_msg'] ?? '', 35)) ?></div>
            <?php if ((int)$conv['unread'] > 0): ?>
              <span class="chat-item-badge badge badge-danger" style="font-size:10px"><?= $conv['unread'] ?></span>
            <?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Chat Window -->
  <div class="chat-window">
    <?php if ($active_partner_id && $active_conv): ?>
    <div class="chat-header">
      <div class="chat-avatar" style="background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;font-size:14px;font-weight:700;width:40px;height:40px">
        <?= strtoupper(mb_substr($active_conv['partner_name'], 0, 1)) ?>
      </div>
      <div>
        <div class="chat-header-name" style="font-weight:700;color:var(--primary)"><?= htmlspecialchars($active_conv['partner_name']) ?></div>
        <?php if ($active_conv['pg_title']): ?>
          <div style="font-size:12px;color:var(--text-muted)">🏠 <?= htmlspecialchars($active_conv['pg_title']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="chat-messages" id="chat-messages" data-last-id="<?= $last_msg_id ?>"
         data-receiver-id="<?= $active_partner_id ?>" data-pg-id="<?= $active_pg_id ?>">
      <?php if (empty($messages)): ?>
        <div style="text-align:center;color:var(--text-muted);margin:auto;padding:40px">
          <div style="font-size:40px;margin-bottom:8px">👋</div>
          Start the conversation!
        </div>
      <?php else: ?>
        <?php foreach ($messages as $msg):
          $is_sent = $msg['sender_id'] == $current_uid;
        ?>
        <div class="message-wrap" style="display:flex;flex-direction:column;<?= $is_sent ? 'align-items:flex-end' : 'align-items:flex-start' ?>">
          <div class="message-bubble <?= $is_sent ? 'sent' : 'received' ?>">
            <?= nl2br(htmlspecialchars($msg['message_text'])) ?>
          </div>
          <div class="message-time" style="font-size:11px;color:var(--text-light);margin:2px 8px">
            <?= date('h:i A', strtotime($msg['sent_at'])) ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="chat-footer">
      <form id="chat-form" style="display:flex;gap:8px;flex:1" data-receiver="<?= $active_partner_id ?>">
        <input type="text" id="chat-input" class="form-control"
               placeholder="Type a message..." autocomplete="off"
               style="border-radius:999px"
               data-receiver-id="<?= $active_partner_id ?>"
               data-pg-id="<?= $active_pg_id ?>">
        <button type="submit" class="btn btn-primary" style="border-radius:50%;width:44px;height:44px;padding:0;flex-shrink:0">
          <i class="fas fa-paper-plane"></i>
        </button>
      </form>
    </div>

    <?php else: ?>
    <div style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;color:var(--text-muted)">
      <div style="font-size:60px;margin-bottom:16px">💬</div>
      <h3 style="color:var(--primary)">Select a conversation</h3>
      <p>Choose a conversation from the left to start chatting</p>
    </div>
    <?php endif; ?>
  </div>
</div>
