<?php include "partials/header.php"; ?>

<div class="form-wrapper">
    <div class="form-container">
        <h2>Create Account</h2>
        <form action="register-database.php" method="post">
            <div class="input-group">
                <label for="gebruikersnaam">Username</label>
                <input type="text" name="gebruikersnaam" id="gebruikersnaam" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="wachtwoord">Password</label>
                <input type="password" name="wachtwoord" id="wachtwoord" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>
        <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
</div>

<?php include "partials/footer.php"; ?>