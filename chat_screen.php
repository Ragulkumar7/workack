<?php
// chat_screen.php - Latest messages at bottom + functional search

session_start();
include 'include/db_connect.php';

// Current user
$current_user_id = $_SESSION['emp_id'] ?? 1;

// Fetch current user name
$sql_user = "SELECT name FROM employees WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $current_user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$current_name = $user_data['name'] ?? 'You';
$stmt_user->close();

// Encryption / Decryption
function encrypt_message($text) {
    $key = CHAT_ENCRYPTION_KEY;
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, 0, $iv);
    return ['encrypted' => $encrypted, 'iv' => $iv];
}

function decrypt_message($encrypted, $iv) {
    $key = CHAT_ENCRYPTION_KEY;
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    return $decrypted !== false ? $decrypted : '[Decryption failed]';
}

// Handle send message + redirect to force refresh
$alert_msg = '';
$alert_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $message_text = trim($_POST['message'] ?? '');
    $file_path = null;

    // File upload
    if (isset($_FILES['attach_file']) && $_FILES['attach_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/chat_files/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_name = time() . '_' . basename($_FILES['attach_file']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['attach_file']['tmp_name'], $file_path)) {
            $message_text .= "\n[Attached: $file_name]";
        } else {
            $alert_msg = "File upload failed.";
            $alert_type = 'danger';
        }
    }

    if ($receiver_id > 0 && (!empty($message_text) || $file_path)) {
        $enc_data = encrypt_message($message_text);

        $sql = "INSERT INTO messages (sender_id, receiver_id, encrypted_message, iv, file_path, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissb", $current_user_id, $receiver_id, $enc_data['encrypted'], $enc_data['iv'], $file_path);

        if ($stmt->execute()) {
            // Redirect with timestamp to force fresh load
            header("Location: chat_screen.php?chat_with=$receiver_id&t=" . time());
            exit;
        } else {
            $alert_msg = "Error: " . $conn->error;
            $alert_type = 'danger';
        }
        $stmt->close();
    } else {
        $alert_msg = "Please type a message or attach a file.";
        $alert_type = 'warning';
    }
}

// Get chat partner
$chat_with = (int)($_GET['chat_with'] ?? 0);
$partner_name = 'Select a contact';
$partner_status = 'Offline';

if ($chat_with > 0) {
    $sql_partner = "SELECT name FROM employees WHERE id = ?";
    $stmt_partner = $conn->prepare($sql_partner);
    $stmt_partner->bind_param("i", $chat_with);
    $stmt_partner->execute();
    $partner = $stmt_partner->get_result()->fetch_assoc();
    $partner_name = $partner['name'] ?? 'Unknown';
    $partner_status = 'Online';
    $stmt_partner->close();
}

// Fetch contacts with last message preview
$sql_users = "SELECT e.id, e.name, 
              MAX(m.created_at) as last_time,
              (SELECT encrypted_message FROM messages m2 WHERE m2.id = MAX(m.id) LIMIT 1) as last_enc,
              (SELECT iv FROM messages m2 WHERE m2.id = MAX(m.id) LIMIT 1) as last_iv
              FROM employees e
              LEFT JOIN messages m ON (m.sender_id = e.id OR m.receiver_id = e.id) AND (m.sender_id = ? OR m.receiver_id = ?)
              WHERE e.id != ?
              GROUP BY e.id
              ORDER BY last_time DESC";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt_users->execute();
$users_list = $stmt_users->get_result();

// Fetch & decrypt messages
$messages = [];
if ($chat_with > 0) {
    $sql_msg = "SELECT m.*, e.name AS sender_name 
                FROM messages m 
                LEFT JOIN employees e ON m.sender_id = e.id 
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?) 
                ORDER BY m.created_at ASC";
    $stmt_msg = $conn->prepare($sql_msg);
    $stmt_msg->bind_param("iiii", $current_user_id, $chat_with, $chat_with, $current_user_id);
    $stmt_msg->execute();
    $messages_result = $stmt_msg->get_result();

    while ($msg = $messages_result->fetch_assoc()) {
        $decrypted = decrypt_message($msg['encrypted_message'], $msg['iv']);
        $msg['message'] = $decrypted;
        $messages[] = $msg;
    }
    $stmt_msg->close();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Workack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #FF9B44;
            --primary-dark: #e88b3a;
            --light-bg: #f8f9fc;
            --sidebar-bg: #ffffff;
            --bubble-sent: #FF9B44;
            --bubble-received: #e5e7eb;
            --online: #22c55e;
            --text-muted: #64748b;
        }
        body { background: var(--light-bg); font-family: 'Segoe UI', sans-serif; height: 100vh; overflow: hidden; margin: 0; }
        .chat-wrapper { display: flex; height: 100vh; }
        .chat-sidebar { width: 340px; background: var(--sidebar-bg); border-right: 1px solid #e2e8f0; overflow-y: auto; }
        .chat-main { flex: 1; display: flex; flex-direction: column; }
        .sidebar-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: white; }
        .search-box { border-radius: 50px; padding: 0.75rem 1.25rem; border: 1px solid #d1d5db; background: #f8f9fc; width: 100%; margin-top: 0.5rem; }
        .chat-list { padding: 0.5rem 0; }
        .chat-contact { padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: all 0.2s; text-decoration: none; color: inherit; }
        .chat-contact:hover, .chat-contact.active { background: rgba(255,155,68,0.1); }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--primary); position: relative; }
        .online-dot { position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background: var(--online); border: 2px solid white; border-radius: 50%; }
        .contact-info { flex: 1; }
        .contact-name { font-weight: 600; font-size: 0.95rem; }
        .contact-preview { font-size: 0.85rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
        .contact-time { font-size: 0.75rem; color: var(--text-muted); }
        .chat-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: white; display: flex; align-items: center; gap: 1rem; }
        .chat-messages { flex: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem; background: #f8f9fc; }
        .message { max-width: 65%; padding: 0.9rem 1.3rem; border-radius: 18px; line-height: 1.4; position: relative; }
        .message-received { background: var(--bubble-received); align-self: flex-start; border-bottom-left-radius: 4px; }
        .message-sent { background: var(--bubble-sent); color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
        .msg-time { font-size: 0.7rem; color: #94a3b8; margin-top: 0.3rem; display: block; }
        .chat-input-area { background: white; border-top: 1px solid #e2e8f0; padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .chat-input { flex: 1; border: 1px solid #d1d5db; border-radius: 999px; padding: 0.75rem 1.25rem; outline: none; }
        .chat-input:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(255,155,68,0.15); }
        .action-btn { width: 44px; height: 44px; border-radius: 999px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: color 0.2s; display: flex; align-items: center; justify-content: center; }
        .action-btn:hover { color: var(--primary); }
        .send-btn { background: var(--primary); color: white; border: none; border-radius: 999px; padding: 0 1.5rem; font-weight: 600; height: 44px; display: flex; align-items: center; justify-content: center; }
        .send-btn:hover { background: var(--primary-dark); }
        .empty-chat { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); text-align: center; }
        .empty-icon { font-size: 5rem; opacity: 0.3; margin-bottom: 1.5rem; }
        .alert-floating { position: fixed; top: 1rem; right: 1rem; z-index: 1050; min-width: 320px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
    </style>
</head>
<body>

<?php include 'include/sidebar.php'; ?>

<div class="chat-wrapper">
    <!-- Sidebar -->
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">Chats</h5>
        </div>

        <input type="text" class="search-box mb-3" placeholder="Search contacts or messages" id="searchInput">

        <div class="chat-list">
            <?php while ($user = $users_list->fetch_assoc()): ?>
                <a href="?chat_with=<?= $user['id'] ?>" class="chat-contact <?= ($chat_with == $user['id']) ? 'active' : '' ?>" style="text-decoration: none;">
                    <div class="avatar">
                        <?= substr($user['name'], 0, 1) ?>
                        <span class="online-dot"></span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="contact-name"><?= htmlspecialchars($user['name']) ?></div>
                        <small class="contact-preview text-muted">Tap to chat</small>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Main Chat -->
    <div class="chat-main">
        <div class="chat-header">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar">
                    <?= substr($partner_name, 0, 1) ?>
                    <span class="online-dot" style="background: <?= $partner_status === 'Online' ? 'var(--online)' : '#ef4444' ?>;"></span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($partner_name) ?></h6>
                    <small class="text-muted"><?= $partner_status ?></small>
                </div>
            </div>
            <div class="ms-auto">
                <i class="fas fa-ellipsis-v text-muted" style="cursor:pointer;"></i>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <?php if ($chat_with > 0): ?>
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?= ($msg['sender_id'] == $current_user_id) ? 'message-sent' : 'message-received' ?>">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                            <?php if (!empty($msg['file_path'])): ?>
                                <div class="mt-2">
                                    <a href="<?= htmlspecialchars($msg['file_path']) ?>" target="_blank" class="small text-white">
                                        <i class="fas fa-paperclip me-1"></i> Attached file
                                    </a>
                                </div>
                            <?php endif; ?>
                            <small class="msg-time"><?= date('h:i A', strtotime($msg['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-chat">
                        <div class="empty-icon"><i class="far fa-comment-dots"></i></div>
                        <h5>No messages yet</h5>
                        <p>Start the conversation below.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-chat">
                    <div class="empty-icon"><i class="fas fa-users"></i></div>
                    <h5>Select a contact</h5>
                    <p>Choose someone from the left to chat.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Input Area -->
        <?php if ($chat_with > 0): ?>
            <form method="POST" action="chat_screen.php?chat_with=<?= $chat_with ?>&t=<?= time() ?>" class="chat-input-area" enctype="multipart/form-data">
                <input type="hidden" name="send_message" value="1">
                <input type="hidden" name="receiver_id" value="<?= $chat_with ?>">

                <button type="button" class="action-btn" onclick="document.getElementById('emojiPicker').classList.toggle('d-none')">
                    <i class="far fa-smile"></i>
                </button>

                <div id="emojiPicker" class="emoji-picker d-none">
                    <span class="emoji" onclick="insertEmoji('😊')">😊</span>
                    <span class="emoji" onclick="insertEmoji('😂')">😂</span>
                    <span class="emoji" onclick="insertEmoji('❤️')">❤️</span>
                    <span class="emoji" onclick="insertEmoji('👍')">👍</span>
                    <span class="emoji" onclick="insertEmoji('🔥')">🔥</span>
                    <span class="emoji" onclick="insertEmoji('🎉')">🎉</span>
                </div>

                <input type="text" name="message" id="messageInput" class="chat-input" placeholder="Type your message..." required autofocus>

                <label class="action-btn" for="attachFile" style="display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" name="attach_file" id="attachFile" style="display:none;">
                </label>

                <button type="submit" class="send-btn px-4">
                    Send
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Alert -->
<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show alert-floating" role="alert">
        <?= htmlspecialchars($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-scroll to bottom on load and after send
function scrollToBottom() {
    const messagesDiv = document.getElementById('chatMessages');
    if (messagesDiv) messagesDiv.scrollTop = messagesDiv.scrollHeight;
}
window.onload = scrollToBottom;

// Search filter for contacts
document.getElementById('searchInput').addEventListener('input', function(e) {
    const filter = e.target.value.toLowerCase();
    const contacts = document.querySelectorAll('.chat-contact');

    contacts.forEach(contact => {
        const name = contact.querySelector('.contact-name').textContent.toLowerCase();
        contact.style.display = name.includes(filter) ? '' : 'none';
    });
});

function insertEmoji(emoji) {
    const input = document.getElementById('messageInput');
    input.value += emoji;
    input.focus();
}

document.addEventListener('click', function(e) {
    const picker = document.getElementById('emojiPicker');
    if (!e.target.closest('.action-btn') && !e.target.closest('.emoji-picker')) {
        picker.classList.add('d-none');
    }
});
</script>
</body>
</html>

<?php $conn->close(); ?>