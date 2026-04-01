<?php
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
if(empty($_SESSION['user_id'])) { header('Location: ../auth.php'); exit; }
include '../includes/functions.php';

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);
$user = getUser($_SESSION['user_id']);

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if($action === 'delete' && $id) {
    deletePost($id);
    header('Location: index.php'); exit;
}

if(canPost($user)) {
    if($action === 'new' || $action === 'edit') {
        if($action === 'edit' && $id) {
            $post = getPost($id);
        } else {
            $post = null;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $category = $_POST['category'] ?? '';
            $content = $_POST['content'] ?? '';
            $video = $_POST['video'] ?? '';
            $image = null;
            
            if(!empty($_FILES['image']['name'])) {
                $image = uploadImage($_FILES['image']);
            }
            
            if($id && !empty($_POST['update'])) {
                updatePost($id, $title, $category, $content, $image, $video);
            } else {
                savePost($title, $category, $content, $image, $video, $_SESSION['user_id']);
            }
            header('Location: index.php'); exit;
        }
        
        $categories = getCategories();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'edit' ? 'Editar' : 'Nueva' ?> Publicación</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08); --shadow-md: 0 4px 20px rgba(0,0,0,0.12); --shadow-lg: 0 8px 40px rgba(0,0,0,0.15); --radius-sm: 8px; --radius-md: 12px; --radius-lg: 20px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }
        .navbar { background: var(--header-bg); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        .navbar nav { display: flex; gap: 1rem; }
        .navbar nav a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); transition: all 0.3s; }
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        .card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm); }
        .card h2 { color: var(--primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.8rem 1rem; border: 2px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text); font-family: inherit; font-size: 1rem; transition: all 0.3s; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        .form-group textarea { min-height: 300px; font-family: 'Fira Code', monospace; text-align: justify; text-justify: inter-word; }
        
        .editor-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            padding: 0.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-bottom: none;
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        }
        .editor-toolbar button {
            width: 36px;
            height: 36px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .editor-toolbar button:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .toolbar-divider {
            width: 1px;
            background: var(--border);
            margin: 0 0.3rem;
        }
        
        .editor-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .tab-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        .tab-btn.active, .tab-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .markdown-help {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-top: 0.5rem;
        }
        .help-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
        }
        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.5rem;
        }
        .help-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            padding: 0.3rem;
            background: var(--bg-secondary);
            border-radius: 4px;
        }
        .help-item code {
            background: var(--code-bg);
            color: var(--code-text);
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: 'Fira Code', monospace;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        .post-content-preview {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            min-height: 300px;
            font-family: 'Georgia', serif;
            line-height: 1.8;
            text-align: justify;
            text-justify: inter-word;
        }
        .post-content-preview h1, .post-content-preview h2, .post-content-preview h3 { color: var(--primary); margin: 1rem 0 0.5rem; font-family: 'Poppins', sans-serif; }
        .post-content-preview pre { background: var(--code-bg); color: var(--code-text); padding: 1rem; border-radius: 8px; overflow-x: auto; font-family: 'Fira Code', monospace; font-size: 0.9rem; }
        .post-content-preview code { background: var(--code-bg); color: var(--code-text); padding: 0.2rem 0.4rem; border-radius: 4px; font-family: 'Fira Code', monospace; }
        .post-content-preview ul { margin-left: 1.5rem; }
        .post-content-preview blockquote { border-left: 4px solid var(--primary); padding: 0.5rem 1rem; background: var(--bg-secondary); border-radius: 0 8px 8px 0; }
        
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.2rem; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius-sm); border: none; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary); }
        .theme-float { position: fixed; bottom: 20px; right: 20px; display: flex; gap: 8px; background: var(--bg-secondary); padding: 8px 12px; border-radius: 50px; box-shadow: var(--shadow-lg); z-index: 999; }
        .theme-dot { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: all 0.3s; }
        .theme-dot:hover, .theme-dot.active { transform: scale(1.2); border-color: var(--text); }
        .theme-white { background: linear-gradient(135deg, #fff, #e2e8f0); }
        .theme-blue { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .theme-dark-blue { background: linear-gradient(135deg, #3b82f6, #1e3a8a); }
        .theme-black { background: linear-gradient(135deg, #333, #000); }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><a href="index.php"><i class="fas fa-arrow-left"></i> Mi Panel</a></h1>
        <nav>
            <a href="../index.php"><i class="fas fa-home"></i> Blog</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </nav>
    </nav>
    <main class="container">
        <div class="card">
            <h2><i class="fas fa-<?= $action === 'edit' ? 'edit' : 'plus' ?>"></i> <?= $post ? 'Editar' : 'Nueva' ?> Publicación</h2>
            <form method="post" enctype="multipart/form-data">
                <?php if($post): ?><input type="hidden" name="update" value="1"><?php endif; ?>
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Título</label>
                    <input type="text" name="title" value="<?= $post ? htmlspecialchars($post['title']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-folder"></i> Categoría</label>
                    <input type="text" name="category" value="<?= $post ? htmlspecialchars($post['category']) : '' ?>" list="cats" required>
                    <datalist id="cats">
                        <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Contenido</label>
                    <div class="editor-toolbar">
                        <button type="button" onclick="insertFormat('**', '**')" title="Negrita"><i class="fas fa-bold"></i></button>
                        <button type="button" onclick="insertFormat('*', '*')" title="Cursiva"><i class="fas fa-italic"></i></button>
                        <button type="button" onclick="insertFormat('`', '`')" title="Código"><i class="fas fa-code"></i></button>
                        <button type="button" onclick="insertFormat('```\n', '\n```')" title="Bloque de código"><i class="fas fa-terminal"></i></button>
                        <span class="toolbar-divider"></span>
                        <button type="button" onclick="insertLine('# ')" title="Título"><i class="fas fa-heading"></i></button>
                        <button type="button" onclick="insertLine('## ')" title="Subtítulo"><i class="fas fa-h2"></i></button>
                        <button type="button" onclick="insertLine('### ')" title="Sub-subtítulo"><i class="fas fa-h3"></i></button>
                        <span class="toolbar-divider"></span>
                        <button type="button" onclick="insertLine('- ')" title="Lista"><i class="fas fa-list-ul"></i></button>
                        <button type="button" onclick="insertLine('> ')" title="Cita"><i class="fas fa-quote-right"></i></button>
                        <span class="toolbar-divider"></span>
                        <button type="button" onclick="insertFormat('[', '](url)')" title="Enlace"><i class="fas fa-link"></i></button>
                        <button type="button" onclick="insertFormat('![alt](', ')')" title="Imagen"><i class="fas fa-image"></i></button>
                    </div>
                    <div class="editor-tabs">
                        <button type="button" class="tab-btn active" onclick="showTab('editor')"><i class="fas fa-edit"></i> Editor</button>
                        <button type="button" class="tab-btn" onclick="showTab('preview')"><i class="fas fa-eye"></i> Vista Previa</button>
                    </div>
                    <div id="editor-tab">
                        <textarea name="content" id="content-editor" required><?= $post ? htmlspecialchars($post['content']) : '' ?></textarea>
                    </div>
                    <div id="preview-tab" style="display:none;">
                        <div id="preview-content" class="post-content-preview"></div>
                    </div>
                    <div class="markdown-help">
                        <p class="help-title"><i class="fas fa-info-circle"></i> Formato Markdown</p>
                        <div class="help-grid">
                            <div class="help-item"><code>**texto**</code><span>Negrita</span></div>
                            <div class="help-item"><code>*texto*</code><span>Cursiva</span></div>
                            <div class="help-item"><code>`código`</code><span>Código</span></div>
                            <div class="help-item"><code>```bash</code><span>Bloque</span></div>
                            <div class="help-item"><code>## Título</code><span>Título</span></div>
                            <div class="help-item"><code>- item</code><span>Lista</span></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Imagen</label>
                    <?php if($post && $post['image']): ?><p>Actual: <img src="../<?= htmlspecialchars($post['image']) ?>" style="max-width: 100px;"></p><?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-video"></i> Video YouTube</label>
                    <input type="text" name="video" value="<?= $post ? htmlspecialchars($post['video']) : '' ?>" placeholder="https://www.youtube.com/watch?v=xxxx o https://youtu.be/xxxx">
                    <p class="help" style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.3rem;">Puedes pegar el enlace normal de YouTube</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><i class="fas fa-save"></i> <?= $post ? 'Actualizar' : 'Publicar' ?></button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </main>
    <div class="theme-float">
        <div class="theme-dot" onclick="setTheme('white')" title="Blanco" style="background: linear-gradient(135deg,#fff,#f0f0f0)"></div>
        <div class="theme-dot" onclick="setTheme('blue')" title="Azul" style="background: linear-gradient(135deg,#2563eb,#1e40af)"></div>
        <div class="theme-dot" onclick="setTheme('dark-blue')" title="Azul Oscuro" style="background: linear-gradient(135deg,#3b82f6,#1e3a8a)"></div>
        <div class="theme-dot" onclick="setTheme('black')" title="Negro" style="background: linear-gradient(135deg,#333,#000)"></div>
        <div class="theme-dot" onclick="setTheme('green')" title="Verde" style="background: linear-gradient(135deg,#38a169,#276749)"></div>
        <div class="theme-dot" onclick="setTheme('red')" title="Rojo" style="background: linear-gradient(135deg,#e53e3e,#c53030)"></div>
        <div class="theme-dot" onclick="setTheme('purple')" title="Morado" style="background: linear-gradient(135deg,#805ad5,#6b46c1)"></div>
        <div class="theme-dot" onclick="setTheme('orange')" title="Naranja" style="background: linear-gradient(135deg,#ea580c,#9a3412)"></div>
        <div class="theme-dot" onclick="setTheme('pink')" title="Rosa" style="background: linear-gradient(135deg,#d53f8c,#97266d)"></div>
        <div class="theme-dot" onclick="setTheme('teal')" title="Teal" style="background: linear-gradient(135deg,#0d9488,#0f766e)"></div>
    </div>
    <script>
        function setTheme(theme) {
            document.cookie = 'theme=' + theme + '; path=/; max-age=31536000';
            location.reload();
        }
        
        function insertFormat(before, after) {
            const textarea = document.getElementById('content-editor');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.substring(start, end);
            const replacement = before + (selected || 'texto') + after;
            textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
            textarea.focus();
            textarea.selectionStart = start + before.length;
            textarea.selectionEnd = start + before.length + (selected || 'texto').length;
        }
        
        function insertLine(prefix) {
            const textarea = document.getElementById('content-editor');
            const start = textarea.selectionStart;
            const lineStart = textarea.value.lastIndexOf('\n', start - 1) + 1;
            textarea.value = textarea.value.substring(0, lineStart) + prefix + textarea.value.substring(lineStart);
            textarea.focus();
        }
        
        function showTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('editor-tab').style.display = 'none';
            document.getElementById('preview-tab').style.display = 'none';
            
            if(tab === 'editor') {
                document.querySelector('.tab-btn:first-child').classList.add('active');
                document.getElementById('editor-tab').style.display = 'block';
            } else {
                document.querySelector('.tab-btn:last-child').classList.add('active');
                document.getElementById('preview-tab').style.display = 'block';
                renderPreview();
            }
        }
        
        function renderPreview() {
            let content = document.getElementById('content-editor').value;
            content = content.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            content = content.replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
            content = content.replace(/`([^`]+)`/g, '<code class="inline">$1</code>');
            content = content.replace(/^### (.+)$/gm, '<h3>$1</h3>');
            content = content.replace(/^## (.+)$/gm, '<h2>$1</h2>');
            content = content.replace(/^# (.+)$/gm, '<h1>$1</h1>');
            content = content.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            content = content.replace(/\*([^*]+)\*/g, '<em>$1</em>');
            content = content.replace(/^- (.+)$/gm, '<li>$1</li>');
            content = content.replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>');
            content = content.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1">');
            content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
            content = content.replace(/\n\n/g, '</p><p>');
            content = '<p>' + content + '</p>';
            document.getElementById('preview-content').innerHTML = content;
        }
    </script>
</body>
</html>
<?php
        exit;
    }
}

$userPosts = [];
if(canPost($user)) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE author_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $userPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - <?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08); --shadow-md: 0 4px 20px rgba(0,0,0,0.12); --shadow-lg: 0 8px 40px rgba(0,0,0,0.15); --radius-sm: 8px; --radius-md: 12px; --radius-lg: 20px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }
        .navbar { background: var(--header-bg); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .navbar h1 { color: var(--header-text); display: flex; align-items: center; gap: 0.5rem; }
        .navbar nav { display: flex; gap: 1rem; }
        .navbar a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); transition: all 0.3s; }
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        .user-card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem; box-shadow: var(--shadow-sm); }
        .user-avatar { width: 70px; height: 70px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: white; }
        .user-info h2 { color: var(--primary); margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.5rem; }
        .user-info p { color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem; }
        .role-badge { display: inline-block; padding: 0.3rem 0.8rem; background: var(--accent); color: white; border-radius: 20px; font-size: 0.8rem; margin-left: 0.5rem; }
        .toolbar { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.2rem; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius-sm); border: none; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary); }
        .btn-danger { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; background: var(--bg-secondary); border-radius: var(--radius-md); overflow: hidden; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: var(--primary); color: white; }
        tr:hover { background: var(--bg); }
        .actions { display: flex; gap: 0.5rem; }
        .no-access { background: var(--bg-secondary); padding: 3rem; border-radius: var(--radius-lg); text-align: center; border: 1px solid var(--border); }
        .no-access i { font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem; }
        .theme-float { position: fixed; bottom: 20px; right: 20px; display: flex; gap: 8px; background: var(--bg-secondary); padding: 8px 12px; border-radius: 50px; box-shadow: var(--shadow-lg); z-index: 999; }
        .theme-dot { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: all 0.3s; }
        .theme-dot:hover, .theme-dot.active { transform: scale(1.2); border-color: var(--text); }
        .theme-white { background: linear-gradient(135deg, #fff, #e2e8f0); }
        .theme-blue { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .theme-dark-blue { background: linear-gradient(135deg, #3b82f6, #1e3a8a); }
        .theme-black { background: linear-gradient(135deg, #333, #000); }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-user"></i> Mi Panel</h1>
        <nav>
            <a href="../index.php"><i class="fas fa-home"></i> Blog</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </nav>
    </nav>
    <main class="container">
        <div class="user-card">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <div class="user-info">
                <h2><?= htmlspecialchars($user['username']) ?><span class="role-badge"><?= htmlspecialchars($user['role']) ?></span></h2>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <p><i class="fas fa-calendar"></i> Miembro desde: <?= strftime('%d/%m/%Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>
        
        <?php if(canPost($user)): ?>
        <div class="toolbar">
            <a href="index.php?action=new" class="btn"><i class="fas fa-plus"></i> Nueva Publicación</a>
        </div>
        <h3 style="margin-bottom: 1rem; color: var(--primary);"><i class="fas fa-file-alt"></i> Mis Publicaciones (<?= count($userPosts) ?>)</h3>
        <?php if(empty($userPosts)): ?>
        <p style="text-align:center;padding:2rem;color:var(--text-secondary);">No has publicado nada aún.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($userPosts as $post): ?>
                <tr>
                    <td><?= $post['id'] ?></td>
                    <td><?= htmlspecialchars($post['title']) ?></td>
                    <td><?= htmlspecialchars($post['category']) ?></td>
                    <td><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                    <td class="actions">
                        <a href="../post.php?id=<?= $post['id'] ?>" class="btn btn-secondary" target="_blank"><i class="fas fa-eye"></i></a>
                        <a href="index.php?action=edit&id=<?= $post['id'] ?>" class="btn btn-secondary"><i class="fas fa-edit"></i></a>
                        <a href="index.php?action=delete&id=<?= $post['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <?php else: ?>
        <div class="no-access">
            <i class="fas fa-lock"></i>
            <h3>Sin permisos para publicar</h3>
            <p>Tu cuenta no tiene permisos para crear publicaciones.</p>
            <p>Contacta al administrador para solicitar acceso de autor.</p>
        </div>
        <?php endif; ?>
    </main>
    
    <div class="theme-float">
        <div class="theme-dot" onclick="setTheme('white')" title="Blanco" style="background: linear-gradient(135deg,#fff,#f0f0f0)"></div>
        <div class="theme-dot" onclick="setTheme('blue')" title="Azul" style="background: linear-gradient(135deg,#2563eb,#1e40af)"></div>
        <div class="theme-dot" onclick="setTheme('dark-blue')" title="Azul Oscuro" style="background: linear-gradient(135deg,#3b82f6,#1e3a8a)"></div>
        <div class="theme-dot" onclick="setTheme('black')" title="Negro" style="background: linear-gradient(135deg,#333,#000)"></div>
        <div class="theme-dot" onclick="setTheme('green')" title="Verde" style="background: linear-gradient(135deg,#38a169,#276749)"></div>
        <div class="theme-dot" onclick="setTheme('red')" title="Rojo" style="background: linear-gradient(135deg,#e53e3e,#c53030)"></div>
        <div class="theme-dot" onclick="setTheme('purple')" title="Morado" style="background: linear-gradient(135deg,#805ad5,#6b46c1)"></div>
        <div class="theme-dot" onclick="setTheme('orange')" title="Naranja" style="background: linear-gradient(135deg,#ea580c,#9a3412)"></div>
        <div class="theme-dot" onclick="setTheme('pink')" title="Rosa" style="background: linear-gradient(135deg,#d53f8c,#97266d)"></div>
        <div class="theme-dot" onclick="setTheme('teal')" title="Teal" style="background: linear-gradient(135deg,#0d9488,#0f766e)"></div>
    </div>
    
    <script>
        function setTheme(theme) {
            document.cookie = 'theme=' + theme + '; path=/; max-age=31536000';
            location.reload();
        }
    </script>
</body>
</html>
