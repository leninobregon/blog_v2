<?php
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
if(empty($_SESSION['logged'])) { header('Location: login.php'); exit; }
include '../includes/functions.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if($action === 'delete' && $id) {
    deletePost($id);
    header('Location: index.php'); exit;
}

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
    $currentTheme = getActiveTheme();
    $colors = getThemeColors($currentTheme);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= $action === 'edit' ? 'Editar' : 'Nueva' ?> Publicación</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 40px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }
        
        .navbar {
            background: var(--header-bg);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        .navbar nav { display: flex; gap: 1rem; }
        .navbar nav a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); transition: all 0.3s; }
        .navbar nav a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }
        
        .card h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 0.8rem 1rem; 
            border: 2px solid var(--border); 
            border-radius: var(--radius-sm); 
            background: var(--bg); 
            color: var(--text); 
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
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
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
        .post-content-preview ul, .post-content-preview ol { margin-left: 1.5rem; }
        .post-content-preview blockquote { border-left: 4px solid var(--primary); padding: 0.5rem 1rem; background: var(--bg-secondary); border-radius: 0 8px 8px 0; }
        .post-content-preview img { max-width: 100%; border-radius: 8px; }
        
        .current-img { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem; }
        .current-img img { max-width: 100px; border-radius: var(--radius-sm); }
        
        /* Theme switcher */
        .theme-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            background: var(--bg-secondary);
            padding: 8px 12px;
            border-radius: 50px;
            box-shadow: var(--shadow-lg);
            z-index: 999;
        }
        .theme-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .theme-dot:hover, .theme-dot.active { transform: scale(1.2); border-color: var(--text); }
        .theme-white { background: linear-gradient(135deg, #fff, #e2e8f0); }
        .theme-blue { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .theme-dark-blue { background: linear-gradient(135deg, #3b82f6, #1e3a8a); }
        .theme-black { background: linear-gradient(135deg, #333, #000); }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><a href="dashboard.php"><i class="fas fa-arrow-left"></i> Admin</a></h1>
        <nav>
            <a href="../index.php"><i class="fas fa-eye"></i> Ver Blog</a>
            <a href="config.php"><i class="fas fa-cog"></i> Config</a>
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
                            <div class="help-item">
                                <code>**texto**</code>
                                <span><strong>texto</strong></span>
                            </div>
                            <div class="help-item">
                                <code>*texto*</code>
                                <span><em>texto</em></span>
                            </div>
                            <div class="help-item">
                                <code>`código`</code>
                                <span><code style="background:var(--code-bg);padding:2px 6px;border-radius:3px;">código</code></span>
                            </div>
                            <div class="help-item">
                                <code>```bash</code>
                                <span>Bloque de código</span>
                            </div>
                            <div class="help-item">
                                <code>## Título</code>
                                <span>Subtítulo</span>
                            </div>
                            <div class="help-item">
                                <code># Título</code>
                                <span>Título principal</span>
                            </div>
                            <div class="help-item">
                                <code>- item</code>
                                <span>Lista</span>
                            </div>
                            <div class="help-item">
                                <code>> texto</code>
                                <span>Cita</span>
                            </div>
                            <div class="help-item">
                                <code>![alt](url)</code>
                                <span>Imagen</span>
                            </div>
                            <div class="help-item">
                                <code>[texto](url)</code>
                                <span>Enlace</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Imagen</label>
                    <?php if($post && $post['image']): ?>
                    <div class="current-img">
                        <img src="../<?= htmlspecialchars($post['image']) ?>" alt="Actual">
                        <span>Imagen actual</span>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-video"></i> Video (URL YouTube)</label>
                    <input type="text" name="video" value="<?= $post ? htmlspecialchars($post['video']) : '' ?>" placeholder="https://www.youtube.com/watch?v=xxxx o https://youtu.be/xxxx">
                    <p class="help"><i class="fas fa-info-circle"></i> Puedes pegar el enlace normal de YouTube, se convertirá automáticamente</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><i class="fas fa-save"></i> <?= $post ? 'Actualizar' : 'Publicar' ?></button>
                    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </main>
    
    <div class="theme-float">
        <div class="theme-dot" onclick="setTheme('white')" title="Blanco" style="background: linear-gradient(135deg,#fff,#e2e8f0)"></div>
        <div class="theme-dot" onclick="setTheme('blue')" title="Azul" style="background: linear-gradient(135deg,#2563eb,#1e40af)"></div>
        <div class="theme-dot" onclick="setTheme('dark-blue')" title="Azul Oscuro" style="background: linear-gradient(135deg,#3b82f6,#1e3a8a)"></div>
        <div class="theme-dot" onclick="setTheme('black')" title="Negro" style="background: linear-gradient(135deg,#333,#000)"></div>
        <div class="theme-dot" onclick="setTheme('green')" title="Verde" style="background: linear-gradient(135deg,#38a169,#276749)"></div>
        <div class="theme-dot" onclick="setTheme('red')" title="Rojo" style="background: linear-gradient(135deg,#e53e3e,#c53030)"></div>
        <div class="theme-dot" onclick="setTheme('purple')" title="Morado" style="background: linear-gradient(135deg,#805ad5,#6b46c1)"></div>
        <div class="theme-dot" onclick="setTheme('orange')" title="Naranja" style="background: linear-gradient(135deg,#ea580c,#9a3412)"></div>
        <div class="theme-dot" onclick="setTheme('pink')" title="Rosa" style="background: linear-gradient(135deg,#d53f8c,#97266d)"></div>
        <div class="theme-dot" onclick="setTheme('teal')" title="Verde Azulado" style="background: linear-gradient(135deg,#0d9488,#0f766e)"></div>
        <div class="theme-dot" onclick="setTheme('yellow')" title="Amarillo" style="background: linear-gradient(135deg,#eab308,#a16207)"></div>
        <div class="theme-dot" onclick="setTheme('cyan')" title="Cian" style="background: linear-gradient(135deg,#06b6d4,#0e7490)"></div>
        <div class="theme-dot" onclick="setTheme('brown')" title="Marrón" style="background: linear-gradient(135deg,#d97706,#92400e)"></div>
        <div class="theme-dot" onclick="setTheme('indigo')" title="Índigo" style="background: linear-gradient(135deg,#6366f1,#4338ca)"></div>
        <div class="theme-dot" onclick="setTheme('lime')" title="Lima" style="background: linear-gradient(135deg,#84cc16,#4d7c0f)"></div>
        <div class="theme-dot" onclick="setTheme('amber')" title="Ámbar" style="background: linear-gradient(135deg,#f59e0b,#b45309)"></div>
        <div class="theme-dot" onclick="setTheme('rose')" title="Rojo Rosa" style="background: linear-gradient(135deg,#f43f5e,#be123c)"></div>
        <div class="theme-dot" onclick="setTheme('slate')" title="Pizarra" style="background: linear-gradient(135deg,#475569,#334155)"></div>
        <div class="theme-dot" onclick="setTheme('emerald')" title="Esmeralda" style="background: linear-gradient(135deg,#10b981,#047857)"></div>
        <div class="theme-dot" onclick="setTheme('sky')" title="Cielo" style="background: linear-gradient(135deg,#0ea5e9,#0369a1)"></div>
        <div class="theme-dot" onclick="setTheme('violet')" title="Violeta" style="background: linear-gradient(135deg,#8b5cf6,#6d28d9)"></div>
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
            
            // Escape HTML
            content = content.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            // Code blocks
            content = content.replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
            
            // Inline code
            content = content.replace(/`([^`]+)`/g, '<code class="inline">$1</code>');
            
            // Headers
            content = content.replace(/^### (.+)$/gm, '<h3>$1</h3>');
            content = content.replace(/^## (.+)$/gm, '<h2>$1</h2>');
            content = content.replace(/^# (.+)$/gm, '<h1>$1</h1>');
            
            // Bold and italic
            content = content.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            content = content.replace(/\*([^*]+)\*/g, '<em>$1</em>');
            
            // Lists
            content = content.replace(/^- (.+)$/gm, '<li>$1</li>');
            content = content.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
            
            // Blockquote
            content = content.replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>');
            
            // Images
            content = content.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1">');
            
            // Links
            content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
            
            // Line breaks
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

header('Location: dashboard.php');
