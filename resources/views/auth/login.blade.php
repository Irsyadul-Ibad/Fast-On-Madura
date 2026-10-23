<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - FAST ON</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root{--blue:#0d1b8c;--blue-dark:#091267}
        *{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;background:linear-gradient(135deg,#091267 0%,#0d1b8c 55%,#123fa8 100%);font-family:Inter,Arial,sans-serif;color:#fff}
        .bg-logo{position:fixed;inset:0;z-index:0;display:flex;align-items:center;justify-content:center;pointer-events:none;will-change:transform;transition:transform .12s ease-out}.bg-logo img{width:100%;height:100%;object-fit:cover;opacity:.33;mix-blend-mode:screen;filter:brightness(1.8) saturate(1.4);transform-origin:center;animation:floatBg 9s ease-in-out infinite,glowPulse 4s ease-in-out infinite}.bg-logo:after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,transparent 0%,transparent 30%,rgba(255,255,255,.06) 45%,rgba(100,200,255,.1) 50%,rgba(255,255,255,.06) 55%,transparent 70%);background-size:250% 100%;animation:shimmer 5s linear infinite}
        .bg-glow{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 65% 55% at 50% 50%,rgba(0,115,204,.25),transparent 70%);animation:glowMove 6s ease-in-out infinite}.login-wrapper{position:relative;z-index:1;width:100%;padding:1.5rem;display:flex;flex-direction:column;align-items:center;animation:fadeInScale .8s cubic-bezier(.16,1,.3,1) forwards;opacity:0;transform:scale(.96) translateY(16px)}.top-brand{margin-bottom:1.5rem;color:rgba(255,255,255,.55);font-family:"Space Grotesk",Inter,sans-serif;font-size:.85rem;font-weight:700;letter-spacing:2px;text-transform:uppercase}
        .login-card{width:100%;max-width:420px;padding:3rem 2.75rem;border:1px solid rgba(255,255,255,.2);border-radius:28px;background:rgba(255,255,255,.1);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 32px 64px rgba(0,0,0,.35),0 0 0 1px rgba(255,255,255,.08) inset;transition:transform .4s ease,box-shadow .4s ease}.login-card:hover{transform:translateY(-6px);box-shadow:0 48px 80px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.12) inset}.login-header{text-align:center;margin-bottom:2.25rem}.login-header h1{margin-bottom:.5rem;color:#fff;font-size:2.4rem;font-weight:700;line-height:1.3}.login-header p{color:rgba(255,255,255,.6);font-size:.9rem}
        .form-group{position:relative;margin-bottom:1.25rem}.input-wrapper{position:relative;width:100%}.input-icon{position:absolute;left:1.25rem;top:50%;z-index:2;width:20px;height:20px;display:flex;align-items:center;justify-content:center;transform:translateY(-50%);color:rgba(255,255,255,.7);pointer-events:none;transition:color .3s,transform .3s}.input-icon svg{width:20px;height:20px}.form-group input{width:100%;padding:1rem 1.25rem 1rem 3.5rem;border:1.5px solid rgba(255,255,255,.2);border-radius:9999px;background:rgba(255,255,255,.12);color:#fff;font:500 1rem Inter,Arial,sans-serif;outline:none;transition:all .3s}.form-group input::placeholder{color:rgba(255,255,255,.5)}.form-group input:focus{background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.45);box-shadow:0 0 0 4px rgba(0,115,204,.3)}.form-group input:focus~.input-icon{color:#fff;transform:translateY(-50%) scale(1.1)}
        .error-message{display:block;margin:.4rem 0 0 1.25rem;color:#fca5a5;font-size:.82rem;font-weight:500;animation:fadeIn .3s ease-in-out}.btn-submit{position:relative;width:100%;margin-top:1rem;padding:1.05rem;border:0;border-radius:9999px;background:linear-gradient(135deg,var(--blue),#0093e9);color:#fff;font:600 1.05rem Inter,Arial,sans-serif;cursor:pointer;overflow:hidden;box-shadow:0 4px 20px rgba(0,115,204,.4);transition:all .3s}.btn-submit:hover{background:linear-gradient(135deg,var(--blue-dark),#0073cc);transform:translateY(-2px);box-shadow:0 10px 30px rgba(0,115,204,.6)}.btn-submit:active{transform:translateY(1px)}.btn-submit:after{content:"";position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:rgba(255,255,255,.12);transform:rotate(45deg) scale(0);transition:transform .6s}.btn-submit:hover:after{transform:rotate(45deg) scale(1)}.card-footer{text-align:center;margin-top:1.75rem}.card-footer span{color:rgba(255,255,255,.35);font-size:.75rem;letter-spacing:1px;text-transform:uppercase}
        @media(max-width:480px){.login-card{padding:2.25rem 1.75rem;border-radius:22px}.login-header h1{font-size:2rem}}@media(max-height:600px){.login-card{padding:1.75rem 2rem}.login-header{margin-bottom:1.25rem}.form-group{margin-bottom:.85rem}}@media(prefers-reduced-motion:reduce){*,*:before,*:after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}}
        @keyframes fadeInScale{0%{opacity:0;transform:scale(.95) translateY(20px)}100%{opacity:1;transform:scale(1) translateY(0)}}@keyframes fadeIn{0%{opacity:0}100%{opacity:1}}@keyframes floatBg{0%,100%{transform:translateY(0) scale(1)}25%{transform:translateY(-14px) scale(1.015)}50%{transform:translateY(-22px) scale(1.025)}75%{transform:translateY(-10px) scale(1.01)}}@keyframes glowPulse{0%,100%{filter:brightness(1.6) saturate(1.2) drop-shadow(0 0 30px rgba(0,115,204,.5))}50%{filter:brightness(2.4) saturate(1) drop-shadow(0 0 80px rgba(200,240,255,.6))}}@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}@keyframes glowMove{0%,100%{opacity:.8;transform:scale(1)}50%{opacity:1;transform:scale(1.15)}}
    </style>
</head>
<body>
    <div class="bg-logo">
        <img src="{{ asset('images/faston.png') }}" alt="">
    </div>
    <div class="bg-glow"></div>
    <div class="login-wrapper">
        <div class="top-brand">FAST ON &trade;</div>
        <div class="login-card">
            <div class="login-header">
                <h1>Login</h1>
                <p>Silakan masuk dengan akun yang diberikan</p>
            </div>
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="form-group">
                    <div class="input-wrapper">
                        <input id="user_id" name="user_id" value="{{ old('user_id') }}" placeholder="User ID" autofocus required autocomplete="username">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-3.75V8.25m-6 3.75h6m-6 0a3 3 0 116 0 3 3 0 01-6 0z"/>
                            </svg>
                        </span>
                    </div>
                    @error('user_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <div class="input-wrapper">
                        <input id="password" type="password" name="password" placeholder="Password" required autocomplete="current-password">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                        </span>
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn-submit">Masuk</button>
                @if (session('error') || $errors->has('error'))
                    <div class="error-message" style="text-align:center;margin-top:1rem;margin-left:0">
                        {{ session('error') ?? $errors->first('error') }}
                    </div>
                @endif
            </form>
            <div class="card-footer">
                <span>Fast Acceleration &amp; Service Tracking ON 360&deg;</span>
            </div>
        </div>
    </div>
    <script>const bgLogo=document.querySelector('.bg-logo');let targetX=0,targetY=0,currentX=0,currentY=0;const strength=18;document.addEventListener('mousemove',e=>{const cx=innerWidth/2,cy=innerHeight/2;targetX=((e.clientX-cx)/cx)*strength;targetY=((e.clientY-cy)/cy)*strength});(function loop(){currentX+=(targetX-currentX)*.06;currentY+=(targetY-currentY)*.06;if(bgLogo)bgLogo.style.transform=`translate(${currentX}px,${currentY}px)`;requestAnimationFrame(loop)})();</script>
</body>
</html>
