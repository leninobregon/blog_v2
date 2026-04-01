<?php
$basePath = dirname(__DIR__);
define('CONFIG', include $basePath . '/config.php');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $db = CONFIG['db'];
        $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

function getThemeColors($theme) {
    $themes = [
        'white' => ['bg'=>'#ffffff','bg-secondary'=>'#f8f9fa','text'=>'#2d3748','text-secondary'=>'#718096','primary'=>'#3182ce','secondary'=>'#4a5568','accent'=>'#38a169','border'=>'#e2e8f0','code-bg'=>'#1a202c','code-text'=>'#90cdf4','link'=>'#3182ce','header-bg'=>'#2b6cb0','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'blue' => ['bg'=>'#f0f4ff','bg-secondary'=>'#e8eeff','text'=>'#1a365d','text-secondary'=>'#4a6fa5','primary'=>'#2563eb','secondary'=>'#64748b','accent'=>'#0ea5e9','border'=>'#c7d2fe','code-bg'=>'#1e293b','code-text'=>'#e2e8f0','link'=>'#2563eb','header-bg'=>'#1e40af','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'dark-blue' => ['bg'=>'#0f172a','bg-secondary'=>'#1e293b','text'=>'#e2e8f0','text-secondary'=>'#94a3b8','primary'=>'#3b82f6','secondary'=>'#64748b','accent'=>'#06b6d4','border'=>'#334155','code-bg'=>'#020617','code-text'=>'#22d3ee','link'=>'#60a5fa','header-bg'=>'#1e3a8a','header-text'=>'#f1f5f9','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'black' => ['bg'=>'#121212','bg-secondary'=>'#1e1e1e','text'=>'#e0e0e0','text-secondary'=>'#9e9e9e','primary'=>'#bb86fc','secondary'=>'#03dac6','accent'=>'#cf6679','border'=>'#333333','code-bg'=>'#2d2d2d','code-text'=>'#ffb74d','link'=>'#bb86fc','header-bg'=>'#1f1f1f','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'green' => ['bg'=>'#f0fff4','bg-secondary'=>'#c6f6d5','text'=>'#22543d','text-secondary'=>'#48bb78','primary'=>'#38a169','secondary'=>'#68d391','accent'=>'#ed8936','border'=>'#9ae6b4','code-bg'=>'#22543d','code-text'=>'#9ae6b4','link'=>'#38a169','header-bg'=>'#276749','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'red' => ['bg'=>'#fff5f5','bg-secondary'=>'#fed7d7','text'=>'#742a2a','text-secondary'=>'#c53030','primary'=>'#e53e3e','secondary'=>'#fc8181','accent'=>'#ed8936','border'=>'#feb2b2','code-bg'=>'#742a2a','code-text'=>'#feb2b2','link'=>'#e53e3e','header-bg'=>'#c53030','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'purple' => ['bg'=>'#faf5ff','bg-secondary'=>'#e9d8fd','text'=>'#44337a','text-secondary'=>'#805ad5','primary'=>'#805ad5','secondary'=>'#b794f4','accent'=>'#ed8936','border'=>'#d6bcfa','code-bg'=>'#44337a','code-text'=>'#d6bcfa','link'=>'#805ad5','header-bg'=>'#6b46c1','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'orange' => ['bg'=>'#fff8f0','bg-secondary'=>'#fff0e0','text'=>'#431407','text-secondary'=>'#7c2d12','primary'=>'#ea580c','secondary'=>'#9a3412','accent'=>'#059669','border'=>'#fed7aa','code-bg'=>'#431407','code-text'=>'#fed7aa','link'=>'#c2410c','header-bg'=>'#9a3412','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'pink' => ['bg'=>'#fff5f7','bg-secondary'=>'#fed7e2','text'=>'#702459','text-secondary'=>'#b83280','primary'=>'#d53f8c','secondary'=>'#f687b3','accent'=>'#38b2ac','border'=>'#fbb6ce','code-bg'=>'#702459','code-text'=>'#fbb6ce','link'=>'#d53f8c','header-bg'=>'#97266d','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'teal' => ['bg'=>'#f0fdfa','bg-secondary'=>'#ccfbf1','text'=>'#134e4a','text-secondary'=>'#0d9488','primary'=>'#0d9488','secondary'=>'#2dd4bf','accent'=>'#f59e0b','border'=>'#99f6e4','code-bg'=>'#134e4a','code-text'=>'#99f6e4','link'=>'#0d9488','header-bg'=>'#0f766e','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'yellow' => ['bg'=>'#fefce8','bg-secondary'=>'#fef9c3','text'=>'#713f12','text-secondary'=>'#a16207','primary'=>'#eab308','secondary'=>'#facc15','accent'=>'#f97316','border'=>'#fef08a','code-bg'=>'#713f12','code-text'=>'#fef08a','link'=>'#ca8a04','header-bg'=>'#a16207','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'cyan' => ['bg'=>'#ecfeff','bg-secondary'=>'#cffafe','text'=>'#164e63','text-secondary'=>'#0891b2','primary'=>'#06b6d4','secondary'=>'#22d3ee','accent'=>'#f59e0b','border'=>'#a5f3fc','code-bg'=>'#164e63','code-text'=>'#a5f3fc','link'=>'#0891b2','header-bg'=>'#0e7490','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'brown' => ['bg'=>'#fef3c7','bg-secondary'=>'#fde68a','text'=>'#78350f','text-secondary'=>'#b45309','primary'=>'#d97706','secondary'=>'#f59e0b','accent'=>'#84cc16','border'=>'#fcd34d','code-bg'=>'#78350f','code-text'=>'#fcd34d','link'=>'#b45309','header-bg'=>'#92400e','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'indigo' => ['bg'=>'#eef2ff','bg-secondary'=>'#e0e7ff','text'=>'#312e81','text-secondary'=>'#4f46e5','primary'=>'#6366f1','secondary'=>'#818cf8','accent'=>'#f472b6','border'=>'#c7d2fe','code-bg'=>'#312e81','code-text'=>'#c7d2fe','link'=>'#4f46e5','header-bg'=>'#4338ca','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'lime' => ['bg'=>'#f7fee7','bg-secondary'=>'#ecfccb','text'=>'#365314','text-secondary'=>'#65a30d','primary'=>'#84cc16','secondary'=>'#a3e635','accent'=>'#f59e0b','border'=>'#d9f99d','code-bg'=>'#365314','code-text'=>'#d9f99d','link'=>'#65a30d','header-bg'=>'#4d7c0f','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'amber' => ['bg'=>'#fffbeb','bg-secondary'=>'#fef3c7','text'=>'#78350f','text-secondary'=>'#d97706','primary'=>'#f59e0b','secondary'=>'#fbbf24','accent'=>'#10b981','border'=>'#fde68a','code-bg'=>'#78350f','code-text'=>'#fde68a','link'=>'#d97706','header-bg'=>'#b45309','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'rose' => ['bg'=>'#fff1f2','bg-secondary'=>'#ffe4e6','text'=>'#881337','text-secondary'=>'#be123c','primary'=>'#f43f5e','secondary'=>'#fb7185','accent'=>'#06b6d4','border'=>'#fecdd3','code-bg'=>'#881337','code-text'=>'#fecdd3','link'=>'#be123c','header-bg'=>'#be123c','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'slate' => ['bg'=>'#f8fafc','bg-secondary'=>'#f1f5f9','text'=>'#334155','text-secondary'=>'#64748b','primary'=>'#475569','secondary'=>'#64748b','accent'=>'#0ea5e9','border'=>'#cbd5e1','code-bg'=>'#1e293b','code-text'=>'#e2e8f0','link'=>'#475569','header-bg'=>'#334155','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'emerald' => ['bg'=>'#ecfdf5','bg-secondary'=>'#d1fae5','text'=>'#064e3b','text-secondary'=>'#059669','primary'=>'#10b981','secondary'=>'#34d399','accent'=>'#f59e0b','border'=>'#a7f3d0','code-bg'=>'#064e3b','code-text'=>'#a7f3d0','link'=>'#059669','header-bg'=>'#047857','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'sky' => ['bg'=>'#f0f9ff','bg-secondary'=>'#e0f2fe','text'=>'#0c4a6e','text-secondary'=>'#0284c7','primary'=>'#0ea5e9','secondary'=>'#38bdf8','accent'=>'#f472b6','border'=>'#bae6fd','code-bg'=>'#0c4a6e','code-text'=>'#bae6fd','link'=>'#0284c7','header-bg'=>'#0369a1','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
        'violet' => ['bg'=>'#f5f3ff','bg-secondary'=>'#ede9fe','text'=>'#4c1d95','text-secondary'=>'#7c3aed','primary'=>'#8b5cf6','secondary'=>'#a78bfa','accent'=>'#f472b6','border'=>'#ddd6fe','code-bg'=>'#4c1d95','code-text'=>'#ddd6fe','link'=>'#7c3aed','header-bg'=>'#6d28d9','header-text'=>'#ffffff','font-main'=>'Poppins, sans-serif','font-code'=>'Fira Code, monospace'],
    ];
    return $themes[$theme] ?? $themes['blue'];
}

function getActiveTheme() {
    $validThemes = ['white','blue','dark-blue','black','green','red','purple','orange','pink','teal','yellow','cyan','brown','indigo','lime','amber','rose','slate','emerald','sky','violet'];
    if(isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $validThemes)) return $_COOKIE['theme'];
    return CONFIG['theme'] ?? 'blue';
}

function renderStyles() {
    $theme = getActiveTheme();
    $colors = getThemeColors($theme);
    $css = ":root {";
    foreach ($colors as $key => $val) $css .= "--$key: $val;";
    $css .= "}";
    return $css;
}

function getPosts($category = null, $limit = 10, $offset = 0) {
    $pdo = getDB();
    $limit = (int)$limit;
    $offset = (int)$offset;
    if ($category) {
        $sql = "SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.category = ? ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
    } else {
        $sql = "SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchPosts($query, $limit = 10, $offset = 0) {
    $pdo = getDB();
    $search = "%$query%";
    $limit = (int)$limit;
    $offset = (int)$offset;
    $sql = "SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.title LIKE ? OR p.content LIKE ? OR p.category LIKE ? ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$search, $search, $search]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPostsByMonth($yearMonth, $limit = 10, $offset = 0) {
    $pdo = getDB();
    $limit = (int)$limit;
    $offset = (int)$offset;
    $sql = "SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE DATE_FORMAT(p.created_at, '%Y-%m') = ? ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$yearMonth]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPost($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategories() {
    $pdo = getDB();
    return $pdo->query("SELECT DISTINCT category FROM posts ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
}

function parseMarkdown($text) {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    // Convertir URLs de YouTube a embed
    $text = preg_replace('/https?:\/\/(www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', 'https://www.youtube.com/embed/$2', $text);
    $text = preg_replace('/https?:\/\/youtu\.be\/([a-zA-Z0-9_-]+)/', 'https://www.youtube.com/embed/$1', $text);
    
    // Convertir URLs de video embed a iframe
    $text = preg_replace('/https:\/\/www\.youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', '<div class="video-container"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></div>', $text);
    
    $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code class="inline">$1</code>', $text);
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $text);
    $text = preg_replace('/\!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="post-image">', $text);
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $text);
    $text = preg_replace('/\[video\]([^[]+)\[\/video\]/', '<div class="video-container"><iframe src="$1" frameborder="0" allowfullscreen></iframe></div>', $text);
    
    return nl2br($text);
}

function getAbout() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM about WHERE id = 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateAbout($title, $subtitle, $description, $experience, $goals, $youtube, $facebook, $twitter, $telegram, $email, $photo = null) {
    $pdo = getDB();
    if($photo) {
        $stmt = $pdo->prepare("UPDATE about SET title=?, subtitle=?, description=?, experience=?, goals=?, youtube_url=?, facebook_url=?, twitter_url=?, telegram_url=?, email=?, photo=? WHERE id=1");
        return $stmt->execute([$title, $subtitle, $description, $experience, $goals, $youtube, $facebook, $twitter, $telegram, $email, $photo]);
    } else {
        $stmt = $pdo->prepare("UPDATE about SET title=?, subtitle=?, description=?, experience=?, goals=?, youtube_url=?, facebook_url=?, twitter_url=?, telegram_url=?, email=? WHERE id=1");
        return $stmt->execute([$title, $subtitle, $description, $experience, $goals, $youtube, $facebook, $twitter, $telegram, $email]);
    }
}

function incrementViews($postId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
    return $stmt->execute([$postId]);
}

function incrementTotalHits() {
    $pdo = getDB();
    $pdo->exec("UPDATE site_stats SET total_hits = total_hits + 1 WHERE id = 1");
}

function getTotalHits() {
    $pdo = getDB();
    return $pdo->query("SELECT total_hits FROM site_stats WHERE id = 1")->fetchColumn();
}

function savePost($title, $category, $content, $image = null, $video = null, $authorId = null) {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO posts (title, category, content, image, video, author_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    return $stmt->execute([$title, $category, $content, $image, $video, $authorId]);
}

function updatePost($id, $title, $category, $content, $image = null, $video = null) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, category = ?, content = ?, image = COALESCE(?, image), video = COALESCE(?, video) WHERE id = ?");
    return $stmt->execute([$title, $category, $content, $image, $video, $id]);
}

function deletePost($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    return $stmt->execute([$id]);
}

function uploadImage($file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid() . '.' . $ext;
        $uploadDir = dirname(__DIR__) . '/uploads/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        move_uploaded_file($file['tmp_name'], $uploadDir . $name);
        return 'uploads/' . $name;
    }
    return null;
}

function registerUser($username, $email, $password, $firstName = '', $lastName = '', $phone = '', $recoveryQuestion = '', $recoveryAnswer = '') {
    $pdo = getDB();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, phone, recovery_question, recovery_answer) VALUES (?, ?, ?, 'user', ?, ?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hash, $firstName, $lastName, $phone, $recoveryQuestion, $recoveryAnswer]);
    } catch (Exception $e) { return false; }
}

function loginUser($emailOrUsername, $password) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$emailOrUsername, $emailOrUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) return $user;
    return null;
}

function getUser($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getComments($postId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT c.*, u.username, u.avatar, u.first_name, u.last_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC");
    $stmt->execute([$postId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addComment($postId, $userId, $content) {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    return $stmt->execute([$postId, $userId, $content]);
}

function canPost($user) {
    return in_array($user['role'], ['admin', 'author']);
}

function getAllUsers() {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

function updateUser($id, $username, $email, $role, $firstName = '', $lastName = '', $bio = '', $facebook = '', $twitter = '', $telegram = '', $instagram = '', $youtube = '', $linkedin = '', $website = '') {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, first_name = ?, last_name = ?, bio = ?, facebook = ?, twitter = ?, telegram = ?, instagram = ?, youtube = ?, linkedin = ?, website = ? WHERE id = ?");
    return $stmt->execute([$username, $email, $role, $firstName, $lastName, $bio, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website, $id]);
}

function updateUserProfile($id, $firstName, $lastName, $bio, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, bio = ?, facebook = ?, twitter = ?, telegram = ?, instagram = ?, youtube = ?, linkedin = ?, website = ? WHERE id = ?");
    return $stmt->execute([$firstName, $lastName, $bio, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website, $id]);
}

function updateUserPassword($id, $newPassword) {
    $pdo = getDB();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hash, $id]);
}

function deleteUser($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

// Newsletter functions
function subscribeNewsletter($email, $name = '') {
    $pdo = getDB();
    $token = bin2hex(random_bytes(32));
    try {
        $stmt = $pdo->prepare("INSERT INTO newsletter (email, name, token) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE active=1, name=VALUES(name)");
        return $stmt->execute([$email, $name, $token]);
    } catch (Exception $e) { return false; }
}

function unsubscribeNewsletter($email) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE newsletter SET active = 0 WHERE email = ?");
    return $stmt->execute([$email]);
}

function getNewsletterSubscribers($activeOnly = true) {
    $pdo = getDB();
    if ($activeOnly) {
        return $pdo->query("SELECT * FROM newsletter WHERE active = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
    return $pdo->query("SELECT * FROM newsletter ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

function logVisit($page, $ip, $userAgent, $referer = '') {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO visit_logs (page, ip, user_agent, referer) VALUES (?, ?, ?, ?)");
    $stmt->execute([$page, $ip, $userAgent, $referer]);
}

function getVisitStats($days = 30) {
    $pdo = getDB();
    $sql = "SELECT DATE(created_at) as date, COUNT(*) as visits FROM visit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY DATE(created_at) ORDER BY date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalVisits() {
    $pdo = getDB();
    return $pdo->query("SELECT COUNT(*) FROM visit_logs")->fetchColumn();
}

function getTopPages($limit = 10) {
    $pdo = getDB();
    return $pdo->query("SELECT page, COUNT(*) as visits FROM visit_logs GROUP BY page ORDER BY visits DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalPosts() {
    $pdo = getDB();
    return $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
}

function getTotalUsers() {
    $pdo = getDB();
    return $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
}

function getTotalComments() {
    $pdo = getDB();
    return $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
}

function getPostsByCategory() {
    $pdo = getDB();
    return $pdo->query("SELECT category, COUNT(*) as count FROM posts GROUP BY category ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);
}

function getUsersByRole() {
    $pdo = getDB();
    return $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentPosts($limit = 5) {
    $pdo = getDB();
    return $pdo->query("SELECT p.*, u.username as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentUsers($limit = 5) {
    $pdo = getDB();
    return $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentComments($limit = 5) {
    $pdo = getDB();
    return $pdo->query("SELECT c.*, u.username, p.title as post_title FROM comments c JOIN users u ON c.user_id = u.id JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
}

function getVisitStatsByHour() {
    $pdo = getDB();
    return $pdo->query("SELECT HOUR(created_at) as hour, COUNT(*) as visits FROM visit_logs WHERE DATE(created_at) = CURDATE() GROUP BY HOUR(created_at) ORDER BY hour")->fetchAll(PDO::FETCH_ASSOC);
}

function getReferrerStats($limit = 10) {
    $pdo = getDB();
    return $pdo->query("SELECT referer, COUNT(*) as visits FROM visit_logs WHERE referer != '' GROUP BY referer ORDER BY visits DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
}

function backupDatabase() {
    $pdo = getDB();
    $dbName = CONFIG['db']['name'];
    $backupDir = dirname(__DIR__) . '/db/';
    
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    
    $filename = $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backupDir . $filename;
    
    $sql = "-- ============================================\n";
    $sql .= "-- BACKUP: $dbName\n";
    $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- ============================================\n\n";
    $sql .= "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    $sql .= "USE `$dbName`;\n\n";
    
    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        // Get create table statement
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sql .= "-- Table: $table\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $createTable['Create Table'] . ";\n\n";
        
        // Get data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $columns = implode('`, `', array_keys($rows[0]));
            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $pdo->quote($val);
                    }
                }
                $sql .= "INSERT INTO `$table` (`$columns`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }
    }
    
    $sql .= "-- ============================================\n";
    $sql .= "-- FIN DEL BACKUP\n";
    $sql .= "-- ============================================\n";
    
    file_put_contents($filepath, $sql);
    
    return "Backup creado: <strong>$filename</strong>";
}

// Language functions
function getActiveLanguage() {
    $validLangs = ['es', 'en'];
    if(isset($_COOKIE['language']) && in_array($_COOKIE['language'], $validLangs)) return $_COOKIE['language'];
    if(isset($_SESSION['language']) && in_array($_SESSION['language'], $validLangs)) return $_SESSION['language'];
    return 'es'; // Default Spanish
}

function getLanguageStrings($lang = null) {
    if(!$lang) $lang = getActiveLanguage();
    $file = dirname(__DIR__) . "/languages/$lang.php";
    if(file_exists($file)) return include $file;
    return include dirname(__DIR__) . "/languages/es.php";
}

function setLanguage($lang) {
    $validLangs = ['es', 'en'];
    if(in_array($lang, $validLangs)) {
        $_SESSION['language'] = $lang;
        setcookie('language', $lang, time() + 31536000, '/');
        return true;
    }
    return false;
}

function t($key, $lang = null) {
    static $strings = null;
    if($strings === null) $strings = getLanguageStrings($lang);
    return $strings[$key] ?? $key;
}
