<?php
// FILE: pages/chi_siamo.php
?>

<style>
    /* Animazione Ingresso */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Hero Section (Titolo) */
    .about-hero {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 60px auto;
        animation: fadeInUp 0.6s ease-out;
    }
    .about-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 15px;
        background: linear-gradient(135deg, var(--text-main) 0%, var(--primary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .about-hero p {
        font-size: 1.1rem;
        color: var(--text-muted);
        line-height: 1.6;
    }

    /* Griglia Team */
    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Card Membro del Team */
    .team-card {
        background: white;
        border-radius: 20px;
        padding: 35px 25px;
        text-align: center;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.6s ease-out backwards;
    }

    /* Effetto Hover Card */
    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px -10px rgba(79, 70, 229, 0.15);
        border-color: var(--primary-light);
    }
    
    /* Linea decorativa superiore */
    .team-card::before {
        content: '';
        position: absolute; top: 0; left: 0; width: 100%; height: 6px;
        background: linear-gradient(90deg, var(--primary), #a855f7);
        opacity: 0; transition: opacity 0.3s;
    }
    .team-card:hover::before { opacity: 1; }

    /* Immagine Profilo */
    .member-img-wrapper {
        width: 120px; height: 120px;
        margin: 0 auto 20px auto;
        position: relative;
    }
    .member-img {
        width: 100%; height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    .team-card:hover .member-img { transform: scale(1.05); border-color: var(--primary-light); }

    /* Testi */
    .member-name {
        font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 5px;
    }
    .member-role {
        font-size: 0.9rem; font-weight: 600; color: var(--primary);
        text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;
        background: #eef2ff; padding: 5px 12px; border-radius: 20px; display: inline-block;
    }
    .member-desc {
        font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px;
    }

    /* Social Icons (Decorativi) */
    .social-links {
        display: flex; justify-content: center; gap: 15px;
    }
    .social-icon {
        color: #94a3b8; font-size: 1.1rem; transition: 0.2s; cursor: pointer;
    }
    .social-icon:hover { color: var(--primary); transform: scale(1.2); }

    /* Ritardi Animazione */
    .team-card:nth-child(1) { animation-delay: 0.1s; }
    .team-card:nth-child(2) { animation-delay: 0.2s; }
    .team-card:nth-child(3) { animation-delay: 0.3s; }
    .team-card:nth-child(4) { animation-delay: 0.4s; }

</style>

<div class="about-hero">
    <h1>Il Nostro Team</h1>
    <p>
        Siamo <strong>iFantastici4</strong>, un gruppo di sviluppatori appassionati dedicati a creare soluzioni web efficienti e scalabili. 
        Il nostro obiettivo è semplificare il supporto tecnico attraverso un'interfaccia intuitiva e potente.
    </p>
</div>

<div class="team-grid">

    <div class="team-card">
        <div class="member-img-wrapper">
            <img src="img/Mattia.png" alt="Mattia" class="member-img">
        </div>
        <h3 class="member-name">Mattia Letteriello</h3>
        <span class="member-role">Full Stack Dev</span>
        <p class="member-desc">
            Architetto del software e coordinatore del progetto. Si assicura che frontend e backend danzino in perfetta armonia.
        </p>
        <div class="social-links">
            <i class="fab fa-github social-icon"></i>
            <i class="fab fa-linkedin social-icon"></i>
            <i class="fas fa-envelope social-icon"></i>
        </div>
    </div>

    <div class="team-card">
        <div class="member-img-wrapper">
            <img src="img/Joanthan.png" alt="Jonathan" class="member-img">
        </div>
        <h3 class="member-name">Jonathan Punzo</h3>
        <span class="member-role">Frontend Designer</span>
        <p class="member-desc">
            Il mago della UI/UX. Trasforma requisiti complessi in interfacce pulite, accessibili e visivamente accattivanti.
        </p>
        <div class="social-links">
            <i class="fab fa-dribbble social-icon"></i>
            <i class="fab fa-figma social-icon"></i>
            <i class="fab fa-instagram social-icon"></i>
        </div>
    </div>

    <div class="team-card">
        <div class="member-img-wrapper">
            <img src="img/Antonia.png" alt="Antonia" class="member-img">
        </div>
        <h3 class="member-name">Antonia Lamberti</h3>
        <span class="member-role">DB Administrator</span>
        <p class="member-desc">
            Custode dei dati. Progetta schemi database ottimizzati garantendo integrità, sicurezza e performance delle query.
        </p>
        <div class="social-links">
            <i class="fas fa-database social-icon"></i>
            <i class="fab fa-linkedin social-icon"></i>
            <i class="fas fa-terminal social-icon"></i>
        </div>
    </div>

    <div class="team-card">
        <div class="member-img-wrapper">
            <img src="img/Vale.png" alt="Valentino" class="member-img">
        </div>
        <h3 class="member-name">Valentino Potapchuk</h3>
        <span class="member-role">Backend Developer</span>
        <p class="member-desc">
            Il motore sotto il cofano. Sviluppa API robuste e gestisce la logica di business server-side con precisione.
        </p>
        <div class="social-links">
            <i class="fab fa-php social-icon"></i>
            <i class="fab fa-docker social-icon"></i>
            <i class="fab fa-github social-icon"></i>
        </div>
    </div>

</div>