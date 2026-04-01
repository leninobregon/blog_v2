<?php 
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include 'header.php'; 

$about = getAbout();
$experience = !empty($about['experience']) ? explode('|', $about['experience']) : [];
$goals = !empty($about['goals']) ? explode('|', $about['goals']) : [];
incrementTotalHits();
?>
    <main class="container about-page">
        <div class="about-header">
            <div class="avatar-container">
                <?php if(!empty($about['photo'])): ?>
                <img src="<?= htmlspecialchars($about['photo']) ?>" alt="Foto" class="about-photo">
                <?php else: ?>
                <i class="fas fa-user-circle"></i>
                <?php endif; ?>
            </div>
            <h1><?= htmlspecialchars($about['title'] ?? 'Acerca de Mí') ?></h1>
            <p class="subtitle"><?= htmlspecialchars($about['subtitle'] ?? 'Ingeniero en Computación') ?></p>
            
            <?php if(!empty($about['youtube_url']) || !empty($about['facebook_url'])): ?>
            <div class="social-links">
                <?php if(!empty($about['youtube_url'])): ?>
                <a href="<?= htmlspecialchars($about['youtube_url']) ?>" target="_blank" class="social-link youtube"><i class="fab fa-youtube"></i></a>
                <?php endif; ?>
                <?php if(!empty($about['facebook_url'])): ?>
                <a href="<?= htmlspecialchars($about['facebook_url']) ?>" target="_blank" class="social-link facebook"><i class="fab fa-facebook"></i></a>
                <?php endif; ?>
                <?php if(!empty($about['twitter_url'])): ?>
                <a href="<?= htmlspecialchars($about['twitter_url']) ?>" target="_blank" class="social-link twitter"><i class="fab fa-twitter"></i></a>
                <?php endif; ?>
                <?php if(!empty($about['telegram_url'])): ?>
                <a href="<?= htmlspecialchars($about['telegram_url']) ?>" target="_blank" class="social-link telegram"><i class="fab fa-telegram"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="about-content">
            <div class="about-section">
                <h2><i class="fas fa-id-card"></i> ¿Quién soy?</h2>
                <p class="description-text"><?= nl2br(htmlspecialchars($about['description'] ?? '')) ?></p>
            </div>
            
            <?php if(!empty($experience)): ?>
            <div class="about-section">
                <h2><i class="fas fa-briefcase"></i> Experiencia</h2>
                <ul class="experience-list">
                    <?php foreach($experience as $exp): ?>
                    <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars(trim($exp)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if(!empty($goals)): ?>
            <div class="about-section">
                <h2><i class="fas fa-bullseye"></i> Objetivos</h2>
                <ul class="goals-list">
                    <?php foreach($goals as $goal): ?>
                    <li><i class="fas fa-angle-right"></i> <?= htmlspecialchars(trim($goal)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="about-section">
                <h2><i class="fas fa-globe-americas"></i> Mi País</h2>
                <div class="country-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <p><strong>Nicaragua</strong> - Tierra de lagos y volcanes</p>
                </div>
                <p>Un país hermoso de Centroamérica conocido por su naturaleza volcánica, lagos hermosos y gente amable.</p>
            </div>
            
            <?php if(!empty($about['email'])): ?>
            <div class="about-section contact-section">
                <h2><i class="fas fa-envelope"></i> Contacto</h2>
                <p>¿Tienes alguna pregunta o quieres colaborar? ¡Escríbeme!</p>
                <a href="mailto:<?= htmlspecialchars($about['email']) ?>" class="btn-contact"><i class="fas fa-paper-plane"></i> <?= htmlspecialchars($about['email']) ?></a>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <style>
        .about-page {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 2px solid var(--primary);
        }
        
        .avatar-container {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .about-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            box-shadow: var(--shadow-md);
        }
        
        .about-header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .about-header .subtitle {
            color: var(--text-secondary);
            font-size: 1.2rem;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .social-link {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .social-link:hover { transform: translateY(-3px); }
        .social-link.youtube { background: #ff0000; }
        .social-link.facebook { background: #1877f2; }
        .social-link.twitter { background: #1da1f2; }
        .social-link.telegram { background: #0088cc; }
        
        .about-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .about-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
        }
        
        .about-section h2 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--border);
        }
        
        .about-section h2 i { color: var(--accent); }
        
        .description-text {
            line-height: 1.8;
            text-align: justify;
        }
        
        .experience-list, .goals-list {
            list-style: none;
            padding: 0;
        }
        
        .experience-list li, .goals-list li {
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .experience-list li:last-child, .goals-list li:last-child { border-bottom: none; }
        .experience-list i { color: var(--accent); }
        .goals-list i { color: var(--primary); }
        
        .country-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg);
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
        }
        
        .country-info i { font-size: 2rem; color: var(--primary); }
        
        .contact-section {
            text-align: center;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border: none;
        }
        
        .contact-section h2 { color: white; border-bottom-color: rgba(255,255,255,0.3); }
        .contact-section p { color: rgba(255,255,255,0.9); }
        
        .btn-contact {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.8rem 2rem;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-contact:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        @media (max-width: 600px) {
            .about-header h1 { font-size: 1.8rem; }
            .about-section { padding: 1.5rem; }
            .social-links { flex-wrap: wrap; }
        }
    </style>
    
<?php include 'footer.php'; ?>