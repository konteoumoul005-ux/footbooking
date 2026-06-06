<style>
/* ===== RESET COMPLET DU HERO ===== */
#home,
#home.content,
#home.content.has-bg,
#home.content.home {
    height: auto !important;
    min-height: 100vh !important;
    max-height: none !important;
    display: flex !important;
    align-items: center !important;
    position: relative !important;
    overflow: hidden !important;
    padding: 0 !important;
    margin: 0 !important;
}

#home .content-bg {
    background-image: url('public/images/joueur.jpg') !important;
    background-size: cover !important;
    background-position: center center !important;
    opacity: 0.55 !important;
    position: absolute !important;
    inset: 0 !important;
}

#home::before {
    content: '' !important;
    position: absolute !important;
    inset: 0 !important;
    background: linear-gradient(
        to right,
        rgba(6,10,14,0.92) 0%,
        rgba(6,10,14,0.7) 45%,
        rgba(6,10,14,0.1) 100%
    ) !important;
    z-index: 1 !important;
}

#home::after { display: none !important; }

#home .container.home-content {
    position: relative !important;
    z-index: 5 !important;
    padding-top: 160px !important;
    padding-bottom: 100px !important;
    width: 100% !important;
    max-width: 1140px !important;
    margin: 0 auto !important;
}

/* Texte */
#home h1 {
    font-family: 'Bebas Neue', sans-serif !important;
    font-size: 72px !important;
    letter-spacing: 3px !important;
    line-height: 1 !important;
    color: #ffffff !important;
    margin: 0 0 20px 0 !important;
    padding: 0 !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    display: block !important;
    text-shadow: 0 4px 30px rgba(0,0,0,0.9) !important;
    max-width: 600px !important;
}

#home p {
    color: rgba(232,237,242,0.9) !important;
    font-family: 'Barlow', sans-serif !important;
    font-size: 16px !important;
    line-height: 1.7 !important;
    margin: 0 0 36px 0 !important;
    padding: 0 !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    display: block !important;
    text-shadow: 0 2px 12px rgba(0,0,0,0.9) !important;
    max-width: 500px !important;
}

#home p span {
    color: #00ff88 !important;
    font-weight: 700 !important;
}

/* Boutons */
#home a.btn,
#home a.btn-theme {
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    display: inline-flex !important;
    align-items: center !important;
    font-family: 'Barlow', sans-serif !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    letter-spacing: 1.5px !important;
    text-transform: uppercase !important;
    padding: 15px 35px !important;
    border-radius: 10px !important;
    text-decoration: none !important;
    margin-right: 14px !important;
    margin-bottom: 10px !important;
    transition: all 0.3s !important;
}

#home a.btn-primary {
    background: #00ff88 !important;
    color: #060a0e !important;
    border: none !important;
    box-shadow: 0 0 30px rgba(0,255,136,0.45) !important;
}

#home a.btn-primary:hover {
    background: #ffffff !important;
    transform: translateY(-3px) !important;
    box-shadow: 0 0 50px rgba(0,255,136,0.6) !important;
    color: #060a0e !important;
}

#home a.btn-outline-white {
    background: rgba(255,255,255,0.08) !important;
    color: #ffffff !important;
    border: 2px solid rgba(255,255,255,0.65) !important;
}

#home a.btn-outline-white:hover {
    background: rgba(255,255,255,0.18) !important;
    transform: translateY(-3px) !important;
    color: #ffffff !important;
}
</style>

<div id="home" class="content has-bg home">
    <div class="content-bg" style="background-image: url(public/images/joueur.jpg);"></div>

    <div class="container home-content">
        <h1>RESERVEZ VOTRE TERRAIN DE FOOT</h1>
        <p>
            Trouvez et réservez le terrain idéal prés de chez vous<br>
            Disponible <span>en temps réel,</span> paiement sécurisé, confirmation instantanée.
        </p>
        <a href="terrains" class="btn btn-theme btn-primary">
            <i class="fa fa-futbol mr-2"></i> Trouver un Terrain
        </a>
        <a href="creneaux" class="btn btn-theme btn-outline-white">
            <i class="fa fa-clock mr-2"></i> Voir les créneaux
        </a>
    </div>
</div>