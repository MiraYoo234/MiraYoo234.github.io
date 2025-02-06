<?php
session_start();
include '../db.php';

// Ensure the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all users except the current user
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.pfpimg 
    FROM users u
    INNER JOIN friendships f ON (f.user_id = u.id OR f.friend_id = u.id)
    WHERE (f.user_id = ? OR f.friend_id = ?)
    AND u.id != ?
    AND f.status = 'accepted'
    ORDER BY u.username
");
$stmt->bindParam(1, $user_id);
$stmt->bindParam(2, $user_id);
$stmt->bindParam(3, $user_id);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected contact if any
$selected_contact = null;
if (isset($_GET['contact_id'])) {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->bindParam(1, $_GET['contact_id']);
    $stmt->execute();
    $selected_contact = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - My Blog</title>
    <link rel="stylesheet" href="chat-styles.css?v=<?php echo filemtime('chat-styles.css'); ?>">
</head>
<body class="dark-theme">
    

    <div class="chat-layout">
        <div class="users-sidebar">
        <div class="sidebar-header">
    <button class="back-button" id="back-button">
        <img src="./necessary/back.png" alt="Back" class="back-icon">
    </button>
    <div class="search-box">
        <input type="text" id="search-input" placeholder="Search chats..." class="search-input">
        <img src="./necessary/search.png" alt="Search" class="search-icon">
    </div>
</div>

            <div class="users-list">
                <?php foreach ($users as $user): ?>
                    <a href="?contact_id=<?php echo $user['id']; ?>" 
                       class="user-card <?php echo isset($_GET['contact_id']) && $_GET['contact_id'] == $user['id'] ? 'active' : ''; ?>">
                        <img 
                            src="../dashboard/<?php echo !empty($user['pfpimg']) ? htmlspecialchars($user['pfpimg']) : '../dashboard/profile/user.png'; ?>" 
                            alt="<?php echo htmlspecialchars($user['username']); ?>'s avatar" 
                            class="user-avatar"
                        >
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="chat-area">
            <?php if ($selected_contact): ?>
                <div class="chat-header">
                    <h2>Chat with <?php echo htmlspecialchars($selected_contact['username']); ?></h2>
                    <button id="delete-conversation" class="delete-btn">
                        <img class="trash" src="./necessary/trash-bin.png" alt="Delete">
                    </button>
                </div>

                <div id="chat-window"></div>

                <form id="chat-form" method="POST" enctype="multipart/form-data" class="floating-chat-form">
                    <input type="hidden" id="receiver_id" name="receiver_id" value="<?php echo $selected_contact['id']; ?>" />
                    <div class="message-input-wrapper">
                        <div class="input-actions">
                            <button type="button" class="emoji-button">😊</button>
                            <div class="emoji-panel"></div>
                            
                            <textarea 
                                id="message-input" 
                                name="message" 
                                placeholder="Type your message..." 
                                rows="1"
                            ></textarea>
                            
                            <button type="button" id="attach-image" class="attach-button">
                                <img src="./necessary/attach.png" alt="Attach" style="width: 20px; height: 20px;">
                            </button>
                            <button type="submit" class="send-button">
                                <img class="send_icn" src="./necessary/send.png" alt="Send" style="width: 20px; height: 20px;">
                            </button>
                        </div>
                        <input type="file" id="image-input" name="image" accept="image/*" style="display: none;">
                    </div>
                </form>
            <?php else: ?>
                <div class="no-chat-selected">
                    <h3>Select a user to start chatting</h3>
                    <p>Choose from the list of users on the left to begin a conversation.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        <?php if ($selected_contact): ?>
            const receiverId = document.getElementById('receiver_id').value;

            function formatTimestamp(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            function loadMessages() {
                fetch(`get_messages.php?contact_id=${receiverId}`)
                    .then(response => response.json())
                    .then(messages => {
                        const chatWindow = document.getElementById('chat-window');
                        chatWindow.innerHTML = '';
                        
                        messages.forEach(message => {
                            const msgElement = document.createElement('div');
                            msgElement.className = `message ${message.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received'}`;
                            
                            const contentElement = document.createElement('div');
                            contentElement.className = 'message-content';
                            
                            if (message.content) {
                                contentElement.textContent = message.content;
                            }
                            
                            if (message.image_path) {
                                const img = document.createElement('img');
                                img.src = message.image_path;
                                img.alt = 'Shared image';
                                contentElement.appendChild(img);
                            }
                            
                            const timeElement = document.createElement('div');
                            timeElement.className = 'message-time';
                            timeElement.textContent = formatTimestamp(message.created_at);
                            
                            msgElement.appendChild(contentElement);
                            msgElement.appendChild(timeElement);
                            chatWindow.appendChild(msgElement);
                        });

                        chatWindow.scrollTop = chatWindow.scrollHeight;
                    })
                    .catch(error => console.error('Error fetching messages:', error));
            }

            setInterval(loadMessages, 100000);

            // Handle form submission
            document.getElementById('chat-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);

                fetch('send_message.php?contact_id=' + receiverId, {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    document.getElementById('message-input').value = '';
                    document.getElementById('image-input').value = '';
                    loadMessages();
                })
                .catch(error => console.error('Error sending message:', error));
            });

            // Handle Enter key to send message
            document.getElementById('message-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                }
            });

            // Load initial messages
            loadMessages();

            // Delete conversation handler
            document.getElementById('delete-conversation').addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this entire conversation? This action cannot be undone.')) {
                    fetch(`delete_conversation.php?contact_id=${receiverId}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadMessages();
                            alert('Conversation deleted successfully');
                        } else {
                            alert('Error deleting conversation');
                        }
                    })
                    .catch(error => console.error('Error deleting conversation:', error));
                }
            });

            // Image attachment handlers
            document.getElementById('attach-image').addEventListener('click', function() {
                document.getElementById('image-input').click();
            });

            document.getElementById('image-input').addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                }
            });

            // Emoji panel setup
            const emojiButton = document.querySelector('.emoji-button');
            const emojiPanel = document.querySelector('.emoji-panel');
            const messageInput = document.getElementById('message-input');

            // Common emojis array
            const emojis = [
                '😊', '😂', '🤣', '❤️', '😍', '😒', '😘', '👍', '😭', '😩',
                '😔', '😏', '😌', '😳', '🥺', '😎', '🤔', '😅', '😀', '😃',
                '😄', '🙂', '🙃', '😉', '😇', '🥰', '😋', '😆', '👋', '🎉',
                '✨', '💕', '💖', '💫', '🌟', '🔥', '👏', '🙌', '💪', '🤝'
            ];

            // Clear existing emoji panel content and repopulate
            emojiPanel.innerHTML = '';
            emojis.forEach(emoji => {
                const span = document.createElement('span');
                span.textContent = emoji;
                span.onclick = () => {
                    messageInput.value += emoji;
                    messageInput.focus();
                    emojiPanel.classList.remove('active');
                };
                emojiPanel.appendChild(span);
            });

            // Toggle emoji panel with stopPropagation
            emojiButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                emojiPanel.classList.toggle('active');
            });

            // Close emoji panel when clicking outside
            document.addEventListener('click', (e) => {
                if (!emojiButton.contains(e.target) && !emojiPanel.contains(e.target)) {
                    emojiPanel.classList.remove('active');
                }
            });
        <?php endif; ?>
        const back_btn = document.getElementById('back-button');
        back_btn.addEventListener('click', () => {
            window.location.href = '../dashboard/dashboard.php';
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const username = card.querySelector('.user-name').textContent.toLowerCase();
                if (username.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
