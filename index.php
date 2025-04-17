<?php include "partials/header.php" ?>

<div class="hero">
    <h1>Welcome to Chirpify</h1>
    <p class="hero-subtitle">Share your thoughts with the world in a simple and engaging way.</p>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="hero-buttons">
            <a href="register-form.php" class="call-to-action">Get Started</a>
            <a href="login.php" class="btn btn-secondary">Sign In</a>
        </div>
    <?php endif; ?>
</div>

<div class="features">
    <div class="container">
        <div class="feature-grid">
            <div class="feature-card">
                <h3>Share</h3>
                <p>Express your thoughts and ideas instantly</p>
            </div>
            <div class="feature-card">
                <h3>Connect</h3>
                <p>Engage with other users in the community</p>
            </div>
            <div class="feature-card">
                <h3>Discover</h3>
                <p>Find interesting conversations and people</p>
            </div>
        </div>
    </div>
</div>

<?php include "partials/footer.php" ?>
