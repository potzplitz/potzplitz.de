<div class="error-container <-DISP_ERROR->" role="alert">
  <p><-ERROR-></p>
</div>

<main class="card" role="main" aria-labelledby="title">
    <header>
        <span class="kicker">Welcome back</span>
        <h1 id="title">Sign in</h1>
        <p class="sub">Enter your credentials to continue.</p>
    </header>

    <form action="/account/login/check" method="post" novalidate>
        <div class="field">
            <label for="username" class="label">Username or Email</label>
            <input class="input" type="text" id="username" name="username" placeholder="Username or Email" autocomplete="username" required>
        </div>

        <div class="field">
            <label for="password" class="label">Password</label>
            <input class="input" type="password" id="password" name="password" placeholder="••••••••" minlength="8" autocomplete="current-password" required>
            <p class="help">Min. 8 characters</p>
        </div>

        <button class="btn" type="submit">Sign in</button>
        <p class="alt">Don’t have an account? <a href="<-URL_HTBASE->account/create">Create one</a></p>
    </form>
</main>
