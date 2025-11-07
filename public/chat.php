<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'farmer' && $_SESSION['user_role'] !== 'vendor')) {
    header('Location: index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiverId = intval($_POST['receiver_id']);
    $message = sanitizeInput($_POST['message']);
    
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $receiverId, $message]);
    }
    
    header("Location: index.php?page=chat&user_id=" . $receiverId);
    exit;
}

$stmt = $pdo->prepare("
    WITH contact_list AS (
        SELECT DISTINCT 
            CASE 
                WHEN m.sender_id = ? THEN m.receiver_id 
                ELSE m.sender_id 
            END as contact_id
        FROM messages m
        WHERE m.sender_id = ? OR m.receiver_id = ?
    )
    SELECT 
        cl.contact_id,
        u.name as contact_name,
        u.role as contact_role,
        (SELECT message FROM messages 
         WHERE (sender_id = ? AND receiver_id = cl.contact_id) 
            OR (sender_id = cl.contact_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages 
         WHERE (sender_id = ? AND receiver_id = cl.contact_id) 
            OR (sender_id = cl.contact_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = cl.contact_id AND receiver_id = ? AND is_read = FALSE) as unread_count
    FROM contact_list cl
    JOIN users u ON u.id = cl.contact_id
    ORDER BY last_message_time DESC NULLS LAST
");
$stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$conversations = $stmt->fetchAll();

$selectedUser = null;
$messages = [];

if (isset($_GET['user_id'])) {
    $selectedUserId = intval($_GET['user_id']);
    
    $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $selectedUser = $stmt->fetch();
    
    if ($selectedUser) {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   sender.name as sender_name,
                   receiver.name as receiver_name
            FROM messages m
            JOIN users sender ON m.sender_id = sender.id
            JOIN users receiver ON m.receiver_id = receiver.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$userId, $selectedUserId, $selectedUserId, $userId]);
        $messages = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE sender_id = ? AND receiver_id = ?");
        $stmt->execute([$selectedUserId, $userId]);
    }
}

$allUsers = $pdo->prepare("SELECT id, name, role FROM users WHERE id != ? AND role IN ('farmer', 'vendor') ORDER BY name ASC");
$allUsers->execute([$userId]);
$availableUsers = $allUsers->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - AgriIntel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌱 AgriIntel</div>
            <ul class="nav-links">
                <li><a href="index.php?page=<?= $userRole ?>">Dashboard</a></li>
                <?php if ($userRole === 'farmer'): ?>
                    <li><a href="index.php?page=weather">Weather</a></li>
                    <li><a href="index.php?page=ai-insights">AI Insights</a></li>
                <?php endif; ?>
                <li><a href="index.php?page=chat">💬 Messages</a></li>
                <li><span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
                <li><a href="index.php?page=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>💬 Messages</h1>
        
        <div class="chat-container">
            <div class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h3>Conversations</h3>
                    <button class="btn btn-sm btn-primary" onclick="showNewChatModal()">+ New Chat</button>
                </div>
                
                <div class="conversations-list">
                    <?php if (count($conversations) > 0): ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="index.php?page=chat&user_id=<?= $conv['contact_id'] ?>" 
                               class="conversation-item <?= isset($_GET['user_id']) && $_GET['user_id'] == $conv['contact_id'] ? 'active' : '' ?>">
                                <div class="conversation-avatar">
                                    <?= $conv['contact_role'] === 'farmer' ? '👨‍🌾' : '💼' ?>
                                </div>
                                <div class="conversation-info">
                                    <div class="conversation-name">
                                        <?= htmlspecialchars($conv['contact_name']) ?>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="badge"><?= $conv['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-preview">
                                        <?= htmlspecialchars(substr($conv['last_message'], 0, 50)) . (strlen($conv['last_message']) > 50 ? '...' : '') ?>
                                    </div>
                                    <div class="conversation-time">
                                        <?= date('M d, g:i A', strtotime($conv['last_message_time'])) ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No conversations yet.</p>
                            <p>Start a new chat to connect with <?= $userRole === 'farmer' ? 'vendors' : 'farmers' ?>!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="chat-main">
                <?php if ($selectedUser): ?>
                    <div class="chat-header">
                        <div class="chat-header-info">
                            <div class="chat-header-avatar">
                                <?= $selectedUser['role'] === 'farmer' ? '👨‍🌾' : '💼' ?>
                            </div>
                            <div>
                                <div class="chat-header-name"><?= htmlspecialchars($selectedUser['name']) ?></div>
                                <div class="chat-header-role"><?= ucfirst($selectedUser['role']) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <?php if (count($messages) > 0): ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?= $msg['sender_id'] == $userId ? 'message-sent' : 'message-received' ?>">
                                    <div class="message-bubble">
                                        <div class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                        <div class="message-time"><?= date('M d, g:i A', strtotime($msg['created_at'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chat-input-container">
                        <form method="POST" class="chat-input-form">
                            <input type="hidden" name="send_message" value="1">
                            <input type="hidden" name="receiver_id" value="<?= $selectedUser['id'] ?>">
                            <textarea name="message" placeholder="Type your message..." rows="2" required></textarea>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="height: 100%; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center;">
                            <h2>💬 Select a conversation</h2>
                            <p>Choose a conversation from the left or start a new chat</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="newChatModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeNewChatModal()">&times;</span>
            <h2>Start New Conversation</h2>
            <div class="users-list">
                <?php foreach ($availableUsers as $user): ?>
                    <a href="index.php?page=chat&user_id=<?= $user['id'] ?>" class="user-item">
                        <div class="user-avatar">
                            <?= $user['role'] === 'farmer' ? '👨‍🌾' : '💼' ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                            <div class="user-role"><?= ucfirst($user['role']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
        .chat-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 0;
            height: calc(100vh - 200px);
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chat-sidebar {
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }
        
        .chat-sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-sidebar-header h3 {
            margin: 0;
        }
        
        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-item {
            display: flex;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .conversation-item:hover {
            background: #f8f9fa;
        }
        
        .conversation-item.active {
            background: #e3f2fd;
            border-left: 3px solid #667eea;
        }
        
        .conversation-avatar {
            font-size: 2rem;
            margin-right: 1rem;
        }
        
        .conversation-info {
            flex: 1;
        }
        
        .conversation-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .conversation-preview {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .conversation-time {
            color: #999;
            font-size: 0.8rem;
        }
        
        .badge {
            background: #667eea;
            color: white;
            padding: 0.1rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
        }
        
        .chat-main {
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
        }
        
        .chat-header-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .chat-header-avatar {
            font-size: 2.5rem;
        }
        
        .chat-header-name {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .chat-header-role {
            color: #666;
            font-size: 0.9rem;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: #fafafa;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
        }
        
        .message-sent {
            justify-content: flex-end;
        }
        
        .message-received {
            justify-content: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
        }
        
        .message-sent .message-bubble {
            background: #667eea;
            color: white;
        }
        
        .message-received .message-bubble {
            background: white;
            border: 1px solid #e0e0e0;
        }
        
        .message-text {
            margin-bottom: 0.25rem;
            word-wrap: break-word;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        
        .chat-input-container {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e0e0e0;
            background: white;
        }
        
        .chat-input-form {
            display: flex;
            gap: 1rem;
        }
        
        .chat-input-form textarea {
            flex: 1;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 0.75rem 1rem;
            resize: none;
            font-family: inherit;
        }
        
        .chat-input-form button {
            border-radius: 20px;
            padding: 0.75rem 2rem;
        }
        
        .users-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .user-item {
            display: flex;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .user-item:hover {
            background: #f8f9fa;
        }
        
        .user-avatar {
            font-size: 2rem;
            margin-right: 1rem;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .user-role {
            color: #666;
            font-size: 0.9rem;
        }
    </style>

    <script>
        function showNewChatModal() {
            document.getElementById('newChatModal').style.display = 'block';
        }
        
        function closeNewChatModal() {
            document.getElementById('newChatModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('newChatModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html>
