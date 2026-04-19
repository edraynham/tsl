/**
 * Simple clay shooting mini-game: click flying targets to score.
 */
export function initClayGame() {
    const canvas = document.getElementById('clay-game-canvas');
    const scoreEl = document.getElementById('clay-game-score');
    const restartBtn = document.getElementById('clay-game-restart');
    const introEl = document.getElementById('clay-game-intro');
    const playBtn = document.getElementById('clay-game-play');
    const gameOverEl = document.getElementById('clay-game-over');
    const finalScoreEl = document.getElementById('clay-game-final-score');
    const releasedEl = document.getElementById('clay-game-released');
    const playAgainBtn = document.getElementById('clay-game-play-again');
    const container = canvas?.closest('[data-clay-game]');
    if (!canvas || !container || !scoreEl) {
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    let logicalW = 800;
    let logicalH = 450;
    let dpr = 1;

    /**
     * @typedef {'edge' | 'full' | 'quartering'} ClayPresentation
     * @type {{ x: number; y: number; vx: number; vy: number; r: number; active: boolean; depth: number; presentation: ClayPresentation; quarteringTilt: number }[]}
     */
    let targets = [];
    /** Clay shards after a hit: outward motion + spin + fade */
    /** @type {{ x: number; y: number; vx: number; vy: number; rot: number; dRot: number; r: number; life: number; maxLife: number; kind: number }[]} */
    let debris = [];
    /** Alternate crossing direction: true = left→right, false = right→left */
    let nextFromLeft = true;
    let score = 0;
    let lastSpawn = 0;
    let spawnIntervalMs = 2000;
    let raf = 0;
    let running = true;
    /** Game loop and shooting only after explicit “Play”. */
    let started = false;
    /** True after all 25 clays have been released and the last one is gone. */
    let gameFinished = false;
    /** How many clays have been released this round (max {@link TOTAL_CLAYS}). */
    let claysReleased = 0;
    /** @type {{ until: number; x: number; y: number } | null} */
    let hitFlash = null;
    /** @type {{ until: number; x0: number; y0: number; x1: number; y1: number } | null} */
    let shotLine = null;
    /** Aiming reticle position (logical px), updated by pointer move / tap. */
    let aimX = 0;
    let aimY = 0;

    const TARGET_R = 22;
    const HIT_PAD = 1.4;
    const GRAVITY = 0.22;
    const MAX_TARGETS = 1;
    const TOTAL_CLAYS = 25;

    /** Visual + hit shape: edge-on (narrow ellipse), full face (disc square-on), quartering away (angled ellipse). */
    const CLAY_PRESENTATION = {
        edge: { rxMul: 0.44, ryMul: 1.0, spin: 0.06 },
        full: { rxMul: 1.0, ryMul: 1.0, spin: 0.07 },
        quartering: { rxMul: 0.62, ryMul: 0.9, spin: 0.055 },
    };

    /** @type {{ x: number; scale: number; variant: number }[]} */
    let treeLayout = [];

    function rebuildTreeLayout() {
        treeLayout = [];
        const step = Math.max(52, logicalW / 10);
        let x = step * 0.15;
        let i = 0;
        while (x < logicalW + step) {
            treeLayout.push({
                x: x + (Math.sin(i * 1.7) * 10 + ((i * 17) % 13) - 6),
                scale: 0.88 + ((i * 13) % 10) * 0.055,
                variant: i % 3,
            });
            x += step * (0.88 + (i % 4) * 0.05);
            i += 1;
        }
    }

    function resize() {
        const rect = container.getBoundingClientRect();
        logicalW = Math.max(320, Math.floor(rect.width));
        logicalH = Math.min(480, Math.max(280, Math.floor(logicalW * 0.52)));
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.floor(logicalW * dpr);
        canvas.height = Math.floor(logicalH * dpr);
        canvas.style.width = `${logicalW}px`;
        canvas.style.height = `${logicalH}px`;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        rebuildTreeLayout();
        aimX = Math.min(Math.max(aimX, 12), logicalW - 12);
        aimY = Math.min(Math.max(aimY, 12), logicalH - 12);
    }

    /**
     * Distant rolling hills (behind treeline) — layered for depth.
     */
    function drawBackgroundHills(horizonY) {
        ctx.save();
        const layers = [
            {
                y0: horizonY - 8,
                c1y: horizonY - 95,
                c2y: horizonY - 78,
                c3y: horizonY - 88,
                c4y: horizonY - 72,
                y1: horizonY - 6,
                fill: '#9bbbad',
                alpha: 0.42,
            },
            {
                y0: horizonY - 4,
                c1y: horizonY - 62,
                c2y: horizonY - 48,
                c3y: horizonY - 55,
                c4y: horizonY - 42,
                y1: horizonY - 2,
                fill: '#86a690',
                alpha: 0.55,
            },
            {
                y0: horizonY + 2,
                c1y: horizonY - 38,
                c2y: horizonY - 28,
                c3y: horizonY - 32,
                c4y: horizonY - 22,
                y1: horizonY + 4,
                fill: '#759680',
                alpha: 0.68,
            },
        ];

        for (const L of layers) {
            ctx.beginPath();
            ctx.moveTo(0, L.y0);
            ctx.bezierCurveTo(
                logicalW * 0.12,
                L.c1y,
                logicalW * 0.32,
                L.c2y,
                logicalW * 0.48,
                L.c3y,
            );
            ctx.bezierCurveTo(logicalW * 0.62, L.c4y, logicalW * 0.78, L.c2y - 6, logicalW, L.y1);
            ctx.lineTo(logicalW, horizonY + 28);
            ctx.lineTo(0, horizonY + 28);
            ctx.closePath();
            ctx.fillStyle = L.fill;
            ctx.globalAlpha = L.alpha;
            ctx.fill();
        }
        ctx.globalAlpha = 1;
        ctx.restore();
    }

    /**
     * Mid-ground ridge + near hill + treeline (grass drawn separately on top of bases).
     */
    function drawHillsAndTrees(horizonY) {
        ctx.save();

        // Far ridge — taller, clearer silhouette
        ctx.beginPath();
        ctx.moveTo(0, horizonY + 12);
        ctx.bezierCurveTo(
            logicalW * 0.18,
            horizonY - 42,
            logicalW * 0.36,
            horizonY - 18,
            logicalW * 0.5,
            horizonY - 32,
        );
        ctx.bezierCurveTo(
            logicalW * 0.66,
            horizonY - 14,
            logicalW * 0.8,
            horizonY - 28,
            logicalW,
            horizonY - 4,
        );
        ctx.lineTo(logicalW, horizonY + 26);
        ctx.lineTo(0, horizonY + 26);
        ctx.closePath();
        ctx.fillStyle = '#8fbf9c';
        ctx.globalAlpha = 0.95;
        ctx.fill();
        ctx.globalAlpha = 1;

        // Near hill — more pronounced roll
        ctx.beginPath();
        ctx.moveTo(0, horizonY + 22);
        ctx.bezierCurveTo(
            logicalW * 0.14,
            horizonY - 14,
            logicalW * 0.33,
            horizonY + 16,
            logicalW * 0.48,
            horizonY - 8,
        );
        ctx.bezierCurveTo(logicalW * 0.64, horizonY + 18, logicalW * 0.82, horizonY + 6, logicalW, horizonY + 20);
        ctx.lineTo(logicalW, horizonY + 44);
        ctx.lineTo(0, horizonY + 44);
        ctx.closePath();
        ctx.fillStyle = '#5d8568';
        ctx.fill();

        // Bushes along the rise (scaled up with larger trees)
        for (let bx = -24; bx < logicalW + 50; bx += 62) {
            ctx.beginPath();
            ctx.ellipse(bx, horizonY + 12, 34, 15, 0, 0, Math.PI * 2);
            ctx.fillStyle = '#4a7354';
            ctx.globalAlpha = 0.58;
            ctx.fill();
        }
        ctx.globalAlpha = 1;

        ctx.restore();

        for (const t of treeLayout) {
            drawTreeSilhouette(t.x, horizonY, t.scale, t.variant);
        }
    }

    /** Simple pine / round tree silhouette */
    function drawTreeSilhouette(x, baseY, scale, variant) {
        const s = scale * Math.min(logicalH * 0.062, 34);
        ctx.save();
        ctx.translate(x, baseY);

        const trunkW = s * 0.35;
        const trunkH = s * 0.55;
        ctx.fillStyle = '#2c2419';
        ctx.fillRect(-trunkW / 2, -trunkH, trunkW, trunkH);

        if (variant === 0) {
            // Stacked triangles (pine)
            const layers = 3;
            for (let i = 0; i < layers; i++) {
                const tier = i;
                const w = s * (1.35 - tier * 0.28);
                const ty = -trunkH - s * 0.15 - tier * s * 0.42;
                ctx.beginPath();
                ctx.moveTo(0, ty - s * 0.55);
                ctx.lineTo(-w, ty + s * 0.12);
                ctx.lineTo(w, ty + s * 0.12);
                ctx.closePath();
                ctx.fillStyle = tier === 0 ? '#1e3d28' : '#254a32';
                ctx.fill();
            }
        } else if (variant === 1) {
            // Round canopy
            ctx.beginPath();
            ctx.arc(0, -trunkH - s * 0.9, s * 1.05, 0, Math.PI * 2);
            ctx.fillStyle = '#1e3d28';
            ctx.fill();
            ctx.beginPath();
            ctx.arc(-s * 0.35, -trunkH - s * 1.05, s * 0.55, 0, Math.PI * 2);
            ctx.arc(s * 0.35, -trunkH - s * 1.0, s * 0.5, 0, Math.PI * 2);
            ctx.fillStyle = '#254a32';
            ctx.fill();
        } else {
            // Tall narrow pine
            ctx.beginPath();
            ctx.moveTo(0, -trunkH - s * 2.2);
            ctx.lineTo(-s * 0.55, -trunkH);
            ctx.lineTo(s * 0.55, -trunkH);
            ctx.closePath();
            ctx.fillStyle = '#1a3324';
            ctx.fill();
        }

        ctx.restore();
    }

    /**
     * @param {{ presentation?: ClayPresentation; quarteringTilt?: number; vx: number }} t
     */
    function getTargetDrawRotation(t) {
        const pres = t.presentation ?? 'full';
        const cfg = CLAY_PRESENTATION[pres];
        const tilt = pres === 'quartering' ? (t.quarteringTilt ?? 0) : 0;

        return tilt + t.vx * cfg.spin;
    }

    /**
     * @param {{ x: number; y: number; r: number; presentation?: ClayPresentation; quarteringTilt?: number; vx: number }} t
     */
    function pointInClay(t, px, py) {
        const pres = t.presentation ?? 'full';
        const cfg = CLAY_PRESENTATION[pres];
        const rx = t.r * cfg.rxMul * HIT_PAD;
        const ry = t.r * cfg.ryMul * HIT_PAD;
        const rot = getTargetDrawRotation(t);
        const dx = px - t.x;
        const dy = py - t.y;
        const c = Math.cos(-rot);
        const s = Math.sin(-rot);
        const lx = c * dx - s * dy;
        const ly = s * dx + c * dy;

        if (rx < 1e-6 || ry < 1e-6) {
            return false;
        }

        return (lx * lx) / (rx * rx) + (ly * ly) / (ry * ry) <= 1;
    }

    function updateReleasedHud() {
        if (releasedEl) {
            releasedEl.textContent = String(claysReleased);
        }
    }

    function spawnTarget() {
        if (claysReleased >= TOTAL_CLAYS) {
            return;
        }
        const active = targets.filter((t) => t.active).length;
        if (active >= MAX_TARGETS) {
            return;
        }

        const fromLeft = nextFromLeft;
        nextFromLeft = !nextFromLeft;

        // depth: 0 = far (small, higher, slower) → 1 = near (larger, lower, faster)
        const depth = 0.4 + Math.random() * 0.6;
        const r = TARGET_R * (0.52 + depth * 0.48);

        // Far clays higher in the sky; nearer clays closer to the horizon
        const y0 =
            logicalH * (0.07 + (1 - depth) * 0.32 + Math.random() * 0.06) + r * 0.15;

        // Horizontal crossing only (alternate left→right / right→left).
        // Speed (px/frame) stays in a 7–10 band so slowest clays are still brisk; varies per clay.
        const vxMag = 7 + Math.random() * 3;
        const vx = fromLeft ? vxMag : -vxMag;
        const vy = 0;

        /** @type {ClayPresentation} */
        let presentation;
        const pick = Math.random();
        if (pick < 1 / 3) {
            presentation = 'edge';
        } else if (pick < 2 / 3) {
            presentation = 'full';
        } else {
            presentation = 'quartering';
        }

        // Quartering away: disc angled so the face opens “away” from the crossing direction
        const quarteringTilt = presentation === 'quartering' ? (fromLeft ? -0.5 : 0.5) : 0;

        targets.push({
            x: fromLeft ? -r - 8 : logicalW + r + 8,
            y: y0,
            vx,
            vy,
            r,
            depth,
            presentation,
            quarteringTilt,
            active: true,
        });
        claysReleased += 1;
        updateReleasedHud();
    }

    function drawSky() {
        const g = ctx.createLinearGradient(0, 0, 0, logicalH);
        g.addColorStop(0, '#6ec8f5');
        g.addColorStop(0.35, '#9fdcf9');
        g.addColorStop(0.62, '#c8ebf8');
        g.addColorStop(0.82, '#d9f0e8');
        g.addColorStop(1, '#e8f5f0');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, logicalW, logicalH);

        const grassH = Math.min(72, logicalH * 0.14);
        const horizonY = logicalH - grassH;

        // Distant hills behind the scene (read as background terrain)
        drawBackgroundHills(horizonY);

        // Light haze at horizon — softer so hill layers stay visible
        const haze = ctx.createLinearGradient(0, horizonY - 100, 0, horizonY + 14);
        haze.addColorStop(0, 'rgba(255,255,255,0)');
        haze.addColorStop(0.55, 'rgba(230, 245, 238, 0.12)');
        haze.addColorStop(1, 'rgba(200, 230, 210, 0.32)');
        ctx.fillStyle = haze;
        ctx.fillRect(0, horizonY - 100, logicalW, 115);

        drawHillsAndTrees(horizonY);

        const gg = ctx.createLinearGradient(0, horizonY, 0, logicalH);
        gg.addColorStop(0, '#5d8f66');
        gg.addColorStop(0.45, '#3d6b47');
        gg.addColorStop(1, '#1e3a32');
        ctx.fillStyle = gg;
        ctx.fillRect(0, horizonY, logicalW, grassH);
    }

    function spawnClayBreak(cx, cy, baseR, depth) {
        const n = 18 + Math.floor(Math.random() * 8);
        for (let i = 0; i < n; i++) {
            const a = (i / n) * Math.PI * 2 + (Math.random() - 0.5) * 0.85;
            const sp = (2.2 + Math.random() * 5) * (0.75 + depth * 0.35);
            const life = 26 + Math.floor(Math.random() * 16);
            debris.push({
                x: cx + Math.cos(a) * baseR * 0.12,
                y: cy + Math.sin(a) * baseR * 0.12,
                vx: Math.cos(a) * sp,
                vy: Math.sin(a) * sp - (1.4 + Math.random() * 1.2),
                rot: Math.random() * Math.PI * 2,
                dRot: (Math.random() - 0.5) * 0.32,
                r: baseR * (0.09 + Math.random() * 0.16),
                life,
                maxLife: life,
                kind: i % 4,
            });
        }
        // A few fine "dust" specks
        for (let j = 0; j < 10; j++) {
            const a = Math.random() * Math.PI * 2;
            const sp = 3 + Math.random() * 6;
            const life = 12 + Math.floor(Math.random() * 10);
            debris.push({
                x: cx,
                y: cy,
                vx: Math.cos(a) * sp,
                vy: Math.sin(a) * sp - 0.8,
                rot: 0,
                dRot: 0,
                r: baseR * (0.04 + Math.random() * 0.05),
                life,
                maxLife: life,
                kind: 4,
            });
        }
    }

    function drawDebris() {
        for (const p of debris) {
            const fade = p.life / p.maxLife;
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot);
            ctx.globalAlpha = Math.min(1, 0.2 + fade * 0.8);

            if (p.kind === 4) {
                ctx.beginPath();
                ctx.arc(0, 0, p.r, 0, Math.PI * 2);
                ctx.fillStyle = fade > 0.4 ? '#fdba74' : '#9a3412';
                ctx.fill();
            } else if (p.kind === 0) {
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.arc(0, 0, p.r, -0.55, 0.55);
                ctx.closePath();
                const g = ctx.createRadialGradient(0, 0, 0, 0, 0, p.r);
                g.addColorStop(0, '#fde68a');
                g.addColorStop(0.45, '#f97316');
                g.addColorStop(1, '#9a3412');
                ctx.fillStyle = g;
                ctx.fill();
                ctx.strokeStyle = 'rgba(251, 191, 36, 0.7)';
                ctx.lineWidth = 1;
                ctx.stroke();
            } else if (p.kind === 1) {
                const w = p.r * 1.6;
                const h = p.r * 0.85;
                ctx.beginPath();
                ctx.ellipse(0, 0, w, h, 0.2, 0, Math.PI * 2);
                ctx.fillStyle = '#ea580c';
                ctx.fill();
                ctx.strokeStyle = '#fbbf24';
                ctx.lineWidth = 1;
                ctx.stroke();
            } else if (p.kind === 2) {
                ctx.beginPath();
                ctx.moveTo(-p.r, 0);
                ctx.lineTo(0, -p.r * 1.1);
                ctx.lineTo(p.r * 0.85, p.r * 0.35);
                ctx.closePath();
                ctx.fillStyle = '#c2410c';
                ctx.fill();
                ctx.strokeStyle = '#fcd34d';
                ctx.lineWidth = 0.8;
                ctx.stroke();
            } else {
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.arc(0, 0, p.r, 0.1, Math.PI * 0.85);
                ctx.closePath();
                ctx.fillStyle = '#fb923c';
                ctx.fill();
            }

            ctx.globalAlpha = 1;
            ctx.restore();
        }
    }

    function drawTarget(t) {
        if (!t.active) {
            return;
        }
        const pres = t.presentation ?? 'full';
        const cfg = CLAY_PRESENTATION[pres];
        const rx = t.r * cfg.rxMul;
        const ry = t.r * cfg.ryMul;
        const rot = getTargetDrawRotation(t);

        ctx.save();
        ctx.translate(t.x, t.y);
        ctx.rotate(rot);
        ctx.globalAlpha = 0.78 + (t.depth ?? 0.5) * 0.22;

        ctx.beginPath();
        ctx.ellipse(0, 0, rx, ry, 0, 0, Math.PI * 2);
        ctx.fillStyle = '#ea580c';
        ctx.fill();
        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = pres === 'edge' ? 2 : 3;
        ctx.stroke();

        if (pres === 'full') {
            ctx.beginPath();
            ctx.arc(-t.r * 0.25, -t.r * 0.2, t.r * 0.35, 0, Math.PI * 2);
            ctx.fillStyle = '#fb923c';
            ctx.fill();
        } else if (pres === 'edge') {
            ctx.strokeStyle = 'rgba(254, 243, 199, 0.7)';
            ctx.lineWidth = 1.25;
            ctx.beginPath();
            ctx.moveTo(0, -ry * 0.88);
            ctx.lineTo(0, ry * 0.88);
            ctx.stroke();
        } else {
            ctx.beginPath();
            ctx.ellipse(-rx * 0.38, -ry * 0.28, t.r * 0.3, t.r * 0.22, 0.25, 0, Math.PI * 2);
            ctx.fillStyle = '#fb923c';
            ctx.fill();
        }

        ctx.globalAlpha = 1;
        ctx.restore();
    }

    function drawGun() {
        const gx = logicalW / 2;
        const gy = logicalH - 18;
        ctx.fillStyle = '#292524';
        ctx.fillRect(gx - 28, gy - 6, 56, 10);
        ctx.fillStyle = '#44403c';
        ctx.fillRect(gx - 6, gy - 18, 12, 16);
    }

    /** On-canvas reticle (replaces the OS crosshair cursor). */
    function drawCrosshair() {
        if (!started) {
            return;
        }
        const cx = aimX;
        const cy = aimY;
        const arm = Math.max(12, Math.min(22, logicalW * 0.028));
        const lw = Math.max(1.25, arm * 0.1);
        ctx.save();
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = 'rgba(15, 23, 42, 0.78)';
        ctx.lineWidth = lw + 1.8;
        ctx.beginPath();
        ctx.moveTo(cx - arm, cy);
        ctx.lineTo(cx + arm, cy);
        ctx.moveTo(cx, cy - arm);
        ctx.lineTo(cx, cy + arm);
        ctx.stroke();
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.97)';
        ctx.lineWidth = lw;
        ctx.beginPath();
        ctx.moveTo(cx - arm, cy);
        ctx.lineTo(cx + arm, cy);
        ctx.moveTo(cx, cy - arm);
        ctx.lineTo(cx, cy + arm);
        ctx.stroke();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.92)';
        ctx.beginPath();
        ctx.arc(cx, cy, lw * 0.42, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    function drawFx(now) {
        if (hitFlash && now < hitFlash.until) {
            ctx.save();
            ctx.globalAlpha = 0.65;
            ctx.fillStyle = '#fef08a';
            ctx.beginPath();
            ctx.arc(hitFlash.x, hitFlash.y, 38, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }

        if (shotLine && now < shotLine.until) {
            ctx.save();
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.85)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(shotLine.x0, shotLine.y0);
            ctx.lineTo(shotLine.x1, shotLine.y1);
            ctx.stroke();
            ctx.restore();
        }
    }

    function step(now) {
        for (const t of targets) {
            if (!t.active) {
                continue;
            }
            t.x += t.vx;
            t.y += t.vy;
            if (t.x < -120 || t.x > logicalW + 120 || t.y > logicalH + 60 || t.y < -100) {
                t.active = false;
            }
        }
        targets = targets.filter((t) => t.active);

        for (const p of debris) {
            p.x += p.vx;
            p.y += p.vy;
            p.vy += GRAVITY * 0.92;
            p.vx *= 0.988;
            p.rot += p.dRot;
            p.life -= 1;
        }
        debris = debris.filter((p) => p.life > 0);

        if (claysReleased < TOTAL_CLAYS && now - lastSpawn > spawnIntervalMs) {
            spawnTarget();
            lastSpawn = now;
            spawnIntervalMs = 1500 + Math.random() * 1500;
        }

        checkGameOver();
    }

    function checkGameOver() {
        if (!started || gameFinished) {
            return;
        }
        if (claysReleased < TOTAL_CLAYS) {
            return;
        }
        if (targets.length > 0) {
            return;
        }
        endGame();
    }

    function endGame() {
        gameFinished = true;
        running = false;
        cancelAnimationFrame(raf);
        started = false;
        if (finalScoreEl) {
            finalScoreEl.textContent = String(score);
        }
        gameOverEl?.classList.remove('hidden');
        gameOverEl?.setAttribute('aria-hidden', 'false');
        canvas.style.cursor = '';
        canvas.setAttribute(
            'aria-label',
            `Game over. You hit ${score} out of ${TOTAL_CLAYS} clays. Press Play again for another round.`,
        );
    }

    function paintIdleFrame() {
        if (started) {
            return;
        }
        drawSky();
        drawGun();
    }

    function frame(now) {
        if (!running || !started) {
            return;
        }
        step(now);
        drawSky();
        for (const t of targets) {
            drawTarget(t);
        }
        drawDebris();
        drawGun();
        drawFx(now);
        drawCrosshair();
        raf = requestAnimationFrame(frame);
    }

    function getCanvasPos(clientX, clientY) {
        const rect = canvas.getBoundingClientRect();
        const x = ((clientX - rect.left) / rect.width) * logicalW;
        const y = ((clientY - rect.top) / rect.height) * logicalH;

        return { x, y };
    }

    function shoot(clientX, clientY) {
        const { x: cx, y: cy } = getCanvasPos(clientX, clientY);
        const gunX = logicalW / 2;
        const gunY = logicalH - 22;

        const now = performance.now();
        shotLine = {
            until: now + 100,
            x0: gunX,
            y0: gunY,
            x1: cx,
            y1: cy,
        };

        let best = Infinity;
        let hitT = null;

        for (const t of targets) {
            if (!t.active) {
                continue;
            }
            if (!pointInClay(t, cx, cy)) {
                continue;
            }
            const dx = cx - t.x;
            const dy = cy - t.y;
            const d = Math.hypot(dx, dy);
            if (d < best) {
                best = d;
                hitT = t;
            }
        }

        if (hitT) {
            spawnClayBreak(hitT.x, hitT.y, hitT.r, hitT.depth);
            hitT.active = false;
            score += 1;
            scoreEl.textContent = String(score);
            hitFlash = { until: now + 180, x: hitT.x, y: hitT.y };
        }
    }

    function syncAimFromClient(clientX, clientY) {
        const p = getCanvasPos(clientX, clientY);
        aimX = p.x;
        aimY = p.y;
    }

    function onPointerMove(e) {
        if (!started || gameFinished || e.target !== canvas) {
            return;
        }
        syncAimFromClient(e.clientX, e.clientY);
    }

    function onPointerDown(e) {
        if (!started || gameFinished || e.target !== canvas) {
            return;
        }
        e.preventDefault();
        syncAimFromClient(e.clientX, e.clientY);
        shoot(e.clientX, e.clientY);
    }

    function reset() {
        targets = [];
        debris = [];
        nextFromLeft = true;
        score = 0;
        scoreEl.textContent = '0';
        claysReleased = 0;
        updateReleasedHud();
        gameFinished = false;
        lastSpawn = performance.now();
        spawnIntervalMs = 1000;
        hitFlash = null;
        shotLine = null;
        gameOverEl?.classList.add('hidden');
        gameOverEl?.setAttribute('aria-hidden', 'true');
    }

    function startGame() {
        if (started && !gameFinished) {
            return;
        }
        reset();
        started = true;
        running = true;
        introEl?.classList.add('hidden');
        introEl?.setAttribute('aria-hidden', 'true');
        playBtn?.setAttribute('tabindex', '-1');
        canvas.setAttribute('aria-label', 'Clay shooting in progress. Click or tap to shoot toward that point.');
        canvas.style.cursor = 'none';
        aimX = logicalW / 2;
        aimY = logicalH / 2;
        lastSpawn = performance.now() - 500;
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(frame);
    }

    resize();
    window.addEventListener('resize', () => {
        resize();
        if (!started) {
            paintIdleFrame();
        }
    });

    canvas.addEventListener('pointerdown', onPointerDown, { passive: false });
    canvas.addEventListener('pointermove', onPointerMove, { passive: true });

    restartBtn?.addEventListener('click', () => {
        const midRound = started && !gameFinished;
        reset();
        if (midRound) {
            started = true;
            running = true;
            gameFinished = false;
            lastSpawn = performance.now() - 500;
            canvas.style.cursor = 'none';
            canvas.setAttribute('aria-label', 'Clay shooting in progress. Click or tap to shoot toward that point.');
            cancelAnimationFrame(raf);
            raf = requestAnimationFrame(frame);
        } else {
            started = false;
            running = true;
            paintIdleFrame();
        }
    });

    playBtn?.addEventListener('click', () => {
        startGame();
    });

    playAgainBtn?.addEventListener('click', () => {
        startGame();
    });

    paintIdleFrame();

    document.addEventListener('visibilitychange', () => {
        if (!started || gameFinished) {
            return;
        }
        if (document.hidden) {
            running = false;
            cancelAnimationFrame(raf);
        } else {
            running = true;
            raf = requestAnimationFrame(frame);
        }
    });
}
