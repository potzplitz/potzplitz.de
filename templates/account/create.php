<div class="error-container <-DISP_ERROR->" role="alert">
    <p><-ERROR-></p>
</div>

<main class="card" role="main" aria-labelledby="title">
    <header>
        <span class="kicker">Welcome</span>
        <h1 id="title">Create Account</h1>
        <p class="sub">Fill out the Form to begin.</p>
    </header>

    <form action="/account/create/new" method="post" novalidate>
        <div class="field">
            <label for="username" class="label">Username</label>
            <input class="input" type="text" id="username" name="username" placeholder="Username" minlength="3" maxlength="24" autocomplete="username" required>
            <p class="help">Max 20 Characters</p>
        </div>

        <div class="field">
            <label for="email" class="label">Email</label>
            <input class="input" type="email" id="email" name="email" placeholder="Email" autocomplete="email" required>
        </div>

        <div class="row">
            <div class="field">
                <label for="password" class="label">Password</label>
                <input class="input" type="password" id="password" name="password" placeholder="••••••••" minlength="8" autocomplete="new-password" required>
                <p class="help">Min. 8 characters</p>
            </div>
            <div class="field">
                <label for="password2" class="label">Confirm Password</label>
                <input class="input" type="password" id="password2" name="password2" placeholder="••••••••" minlength="8" autocomplete="new-password" required>
                <p class="help">Confirm password</p>
            </div>
        </div>
        <button class="btn" type="submit">Create Account</button>
        <p class="alt">Already have an account? <a href="<-URL_HTBASE->account/login">Sign in</a></p>
    </form>
</main>