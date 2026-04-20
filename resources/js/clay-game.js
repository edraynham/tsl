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
    const ratingEl = document.getElementById('clay-game-rating');
    const overMsgEl = document.getElementById('clay-game-over-message');
    const overHitsEl = document.getElementById('clay-game-stats-hits');
    const overMissedEl = document.getElementById('clay-game-stats-missed');
    const overRateEl = document.getElementById('clay-game-stats-rate');
    const releasedEl = document.getElementById('clay-game-released');
    const clayTypeEl = document.getElementById('clay-game-type');
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
     * @typedef {'crosser_ltr' | 'crosser_rtl' | 'looper_ltr' | 'looper_rtl' | 'teal' | 'rabbit'} ClayFlightPath
     * @type {{
     *   x: number;
     *   y: number;
     *   vx: number;
     *   vy: number;
     *   r: number;
     *   active: boolean;
     *   depth: number;
     *   presentation: ClayPresentation;
     *   quarteringTilt: number;
     *   flightPath: ClayFlightPath;
     *   gravityMul: number;
     *   age: number;
     *   maxAge: number;
     *   rabbitGroundY: number | null;
     *   shotsAtClay: number;
     * }[]}
     */
    let targets = [];
    /** Clay shards after a hit: outward motion + spin + fade */
    /** @type {{ x: number; y: number; vx: number; vy: number; rot: number; dRot: number; r: number; life: number; maxLife: number; kind: number }[]} */
    let debris = [];
    /** Rotate through all flight paths so each round includes a mix. */
    const FLIGHT_PATTERN = ['crosser_ltr', 'crosser_rtl', 'looper_ltr', 'looper_rtl', 'teal', 'rabbit'];
    let nextFlightPatternIndex = 0;
    let score = 0;
    let lastSpawn = 0;
    let spawnIntervalMs = 2000;
    let raf = 0;
    let running = true;
    /** Game loop and shooting only after explicit “Play”. */
    let started = false;
    /** True after all 25 clays have been released and the last one is gone. */
    let gameFinished = false;
    /** Timestamp when delayed game-over should fire after final clay clears. */
    let pendingGameOverAt = null;
    /** How many clays have been released this round (max {@link TOTAL_CLAYS}). */
    let claysReleased = 0;
    /** @type {{ until: number; x: number; y: number } | null} */
    let hitFlash = null;
    /** @type {{ until: number; x0: number; y0: number; x1: number; y1: number } | null} */
    let shotLine = null;
    /** @type {{ until: number; text: string; x: number; y: number } | null} */
    let missNote = null;
    /** Impact marks where a shot missed an active clay (shown ~2s). */
    const MISS_SHOT_SPOT_MS = 2000;
    /** @type {{ until: number; x: number; y: number }[]} */
    let missShotSpots = [];
    /** Frozen clay at miss time (~2s, same window as miss shot spots). */
    /** @type {{ until: number; x: number; y: number; r: number; depth: number; presentation: ClayPresentation; quarteringTilt: number; vx: number }[]} */
    let missClayGhosts = [];
    /** Aiming reticle position (logical px), updated by pointer move / tap. */
    let aimX = 0;
    let aimY = 0;

    const TARGET_R = 22;
    const HIT_PAD = 1.75;
    const GRAVITY = 0.22;
    /** Shot travel in logical px per game frame; scales with canvas so lead stays similar across sizes. */
    function shotSpeedPpf() {
        return Math.max(26, Math.min(52, logicalW * 0.046 + logicalH * 0.016));
    }
    const MAX_TARGETS = 1;
    const TOTAL_CLAYS = 25;
    const MAX_SHOTS_PER_CLAY = 2;

    /** Visual + hit shape: edge-on (narrow ellipse), full face (disc square-on), quartering away (angled ellipse). */
    const CLAY_PRESENTATION = {
        edge: { rxMul: 0.44, ryMul: 1.0, spin: 0.06 },
        full: { rxMul: 1.0, ryMul: 1.0, spin: 0.07 },
        quartering: { rxMul: 0.62, ryMul: 0.9, spin: 0.055 },
    };

    /** @type {{ x: number; scale: number; variant: number; widthMul: number; heightMul: number }[]} */
    let treeLayout = [];

    function rebuildTreeLayout() {
        treeLayout = [];
        const step = Math.max(52, logicalW / 10);
        let x = step * 0.15;
        let i = 0;
        /** Per-index multipliers so silhouettes read as squat, tall, narrow, wide, etc. */
        const heightMulByIdx = [1.24, 0.76, 1.06, 1.34, 0.84, 1.12, 0.7, 0.98, 1.2, 0.88, 1.08, 0.8, 1.16, 0.92];
        const widthMulByIdx = [0.8, 1.18, 0.94, 1.1, 0.72, 1.2, 1.02, 0.86, 1.06, 0.9, 1.14, 0.78, 0.96, 1.04];
        while (x < logicalW + step) {
            treeLayout.push({
                x: x + (Math.sin(i * 1.7) * 10 + ((i * 17) % 13) - 6),
                scale: 0.62 + ((i * 13) % 14) * 0.048 + ((i * 7) % 6) * 0.022,
                variant: i % 3,
                widthMul: widthMulByIdx[i % widthMulByIdx.length],
                heightMul: heightMulByIdx[(i * 3 + 2) % heightMulByIdx.length],
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
            drawTreeSilhouette(t.x, horizonY, t.scale, t.variant, t.widthMul, t.heightMul);
        }
    }

    /** Tapered trunk with bark-like gradient and slight flare at ground */
    function drawTreeTrunk(trunkW, trunkH) {
        const wBot = trunkW * 1.15;
        const wTop = trunkW * 0.68;
        const flare = trunkW * 0.22;
        ctx.beginPath();
        ctx.moveTo(-wBot / 2 - flare * 0.35, 0);
        ctx.quadraticCurveTo(-wBot / 2, -trunkH * 0.08, -wTop / 2, -trunkH * 0.92);
        ctx.quadraticCurveTo(0, -trunkH * 1.02, wTop / 2, -trunkH * 0.92);
        ctx.quadraticCurveTo(wBot / 2, -trunkH * 0.08, wBot / 2 + flare * 0.35, 0);
        ctx.closePath();
        const g = ctx.createLinearGradient(-wBot * 0.6, -trunkH, wBot * 0.55, trunkH * 0.15);
        g.addColorStop(0, '#5c4a38');
        g.addColorStop(0.35, '#3d3024');
        g.addColorStop(0.55, '#2a2218');
        g.addColorStop(1, '#1f1812');
        ctx.fillStyle = g;
        ctx.fill();
    }

    /** Curved pine tier: soft cone with light top / shadow under overlap */
    function drawPineTier(apexY, baseY, halfWidth, fillTop, fillBot) {
        ctx.beginPath();
        ctx.moveTo(0, apexY);
        ctx.quadraticCurveTo(-halfWidth * 0.55, apexY + (baseY - apexY) * 0.45, -halfWidth, baseY);
        ctx.lineTo(halfWidth, baseY);
        ctx.quadraticCurveTo(halfWidth * 0.55, apexY + (baseY - apexY) * 0.45, 0, apexY);
        ctx.closePath();
        const g = ctx.createLinearGradient(0, apexY, 0, baseY);
        g.addColorStop(0, fillTop);
        g.addColorStop(0.55, fillBot);
        g.addColorStop(1, '#0f1f14');
        ctx.fillStyle = g;
        ctx.fill();
    }

    /** Overlapping circles for lumpy deciduous canopy (back to front) */
    function drawDeciduousCanopy(cx, cy, s) {
        const blobs = [
            { ox: 0, oy: 0, r: 1.02, c0: '#142818', c1: '#1c3826' },
            { ox: -0.42, oy: 0.12, r: 0.58, c0: '#1a3022', c1: '#244a34' },
            { ox: 0.38, oy: 0.1, r: 0.52, c0: '#162a1e', c1: '#203d2c' },
            { ox: 0.05, oy: -0.35, r: 0.48, c0: '#1e3a28', c1: '#2a5238' },
            { ox: -0.28, oy: -0.22, r: 0.4, c0: '#183222', c1: '#254a32' },
            { ox: 0.32, oy: -0.18, r: 0.36, c0: '#1a3624', c1: '#264236' },
            { ox: -0.08, oy: 0.28, r: 0.32, c0: '#122418', c1: '#1c3424' },
        ];
        for (const b of blobs) {
            const bx = cx + b.ox * s;
            const by = cy + b.oy * s;
            const r = b.r * s;
            const rg = ctx.createRadialGradient(bx - r * 0.35, by - r * 0.4, r * 0.1, bx, by, r);
            rg.addColorStop(0, b.c1);
            rg.addColorStop(0.55, b.c0);
            rg.addColorStop(1, '#0d1810');
            ctx.beginPath();
            ctx.arc(bx, by, r, 0, Math.PI * 2);
            ctx.fillStyle = rg;
            ctx.fill();
        }
    }

    /** Pine / deciduous / tall conifer silhouettes with volume and subtle lean */
    function drawTreeSilhouette(x, baseY, scale, variant, widthMul = 1, heightMul = 1) {
        const s = scale * Math.min(logicalH * 0.062, 34);
        ctx.save();
        const lean = Math.sin(x * 0.073 + variant * 1.1) * 0.042;
        ctx.translate(x, baseY);
        ctx.scale(widthMul, heightMul);
        ctx.rotate(lean);

        const trunkW = s * 0.34;
        const trunkH = s * 0.52;
        drawTreeTrunk(trunkW, trunkH);

        const crownBase = -trunkH;

        if (variant === 0) {
            const layers = 4;
            for (let i = 0; i < layers; i++) {
                const w = s * (1.38 - i * 0.24);
                const tierH = s * (0.46 + (i === layers - 1 ? 0.1 : 0));
                const baseYTier = crownBase - s * 0.06 - i * s * 0.36;
                const apexY = baseYTier - tierH;
                const topG = i >= layers - 2 ? '#2f5c3e' : i === 0 ? '#243c2c' : '#284a34';
                const botG = i <= 1 ? '#1a3224' : '#15261a';
                drawPineTier(apexY, baseYTier + s * 0.1, w, topG, botG);
            }
        } else if (variant === 1) {
            drawDeciduousCanopy(0, crownBase - s * 0.95, s);
        } else {
            const segs = 5;
            for (let i = 0; i < segs; i++) {
                const t = segs > 1 ? i / (segs - 1) : 0;
                const w = s * (0.7 - t * 0.42);
                const segH = s * 0.42;
                const baseYTier = crownBase - i * s * 0.34;
                const apexY = baseYTier - segH;
                const topG = i >= segs - 2 ? '#2c5238' : '#243e2e';
                const botG = '#15261a';
                drawPineTier(apexY, baseYTier + s * 0.06, w, topG, botG);
            }
            const tipY = crownBase - segs * s * 0.34 - s * 0.32;
            ctx.beginPath();
            ctx.moveTo(0, tipY);
            ctx.lineTo(-s * 0.11, tipY + s * 0.42);
            ctx.lineTo(s * 0.11, tipY + s * 0.42);
            ctx.closePath();
            const tipG = ctx.createLinearGradient(0, tipY, 0, tipY + s * 0.45);
            tipG.addColorStop(0, '#356a44');
            tipG.addColorStop(1, '#1a3324');
            ctx.fillStyle = tipG;
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

    /**
     * One frame of movement / bounce (same rules as {@link step}); does not touch `age` or `active`.
     * @param {{ x: number; y: number; vx: number; vy: number; gravityMul: number; flightPath: ClayFlightPath; rabbitGroundY: number | null; r: number }} t
     */
    function advanceClayMotion(t) {
        if (t.gravityMul > 0) {
            t.vy += GRAVITY * t.gravityMul;
        }
        t.x += t.vx;
        t.y += t.vy;

        if (t.flightPath === 'rabbit' && t.rabbitGroundY !== null && t.y > t.rabbitGroundY) {
            t.y = t.rabbitGroundY;
            t.vy = -Math.abs(t.vy) * 0.42;
            t.vx *= 0.994;
            if (Math.abs(t.vy) < 0.15) {
                t.vy = 0;
            }
        }

        const minY = t.r + 4;
        const maxY = logicalH - t.r - 4;

        if (t.y < minY) {
            t.y = minY;
            if (t.vy < 0) {
                t.vy *= -0.78;
            }
        }
        if (t.y > maxY) {
            t.y = maxY;
            if (t.vy > 0) {
                t.vy = 0;
            }
        }
    }

    /**
     * @param {typeof targets[number]} t
     */
    function copyClayForProjection(t) {
        return {
            x: t.x,
            y: t.y,
            vx: t.vx,
            vy: t.vy,
            gravityMul: t.gravityMul,
            flightPath: t.flightPath,
            rabbitGroundY: t.rabbitGroundY,
            r: t.r,
            presentation: t.presentation,
            quarteringTilt: t.quarteringTilt,
        };
    }

    /**
     * Ray from muzzle through aim vs clay path: hit if shot pellet meets clay ellipse at same frame.
     * @returns {{ t: typeof targets[number]; frames: number; clayX: number; clayY: number; shotX: number; shotY: number } | null}
     */
    function findLeadInterceptHit(gunX, gunY, aimX, aimY, activeTargets) {
        let ux = aimX - gunX;
        let uy = aimY - gunY;
        const ulen = Math.hypot(ux, uy);
        if (ulen < 4) {
            ux = 0;
            uy = -1;
        } else {
            ux /= ulen;
            uy /= ulen;
        }
        const spd = shotSpeedPpf();
        const maxK = Math.min(120, Math.ceil((logicalW + logicalH) / spd) + 22);
        let best = null;

        for (const t of activeTargets) {
            if (!t.active) {
                continue;
            }
            const p = copyClayForProjection(t);
            for (let k = 1; k <= maxK; k++) {
                advanceClayMotion(p);
                const shotX = gunX + ux * spd * k;
                const shotY = gunY + uy * spd * k;
                if (pointInClay(p, shotX, shotY)) {
                    const cand = { t, frames: k, clayX: p.x, clayY: p.y, shotX, shotY };
                    if (!best || cand.frames < best.frames) {
                        best = cand;
                    }
                    break;
                }
                const minX = p.r + 4;
                const maxX = logicalW - p.r - 4;
                if (p.x < minX || p.x > maxX) {
                    break;
                }
            }
        }

        return best;
    }

    function updateReleasedHud() {
        if (releasedEl) {
            releasedEl.textContent = String(claysReleased);
        }
    }

    /**
     * @param {ClayFlightPath | null} flightPath
     */
    function updateClayTypeHud(flightPath) {
        if (!clayTypeEl) {
            return;
        }
        const labels = {
            crosser_ltr: 'Left crosser',
            crosser_rtl: 'Right crosser',
            looper_ltr: 'Left looper',
            looper_rtl: 'Right looper',
            teal: 'Teal',
            rabbit: 'Rabbit',
        };
        clayTypeEl.textContent = flightPath ? labels[flightPath] : 'Waiting...';
    }

    function spawnTarget() {
        if (claysReleased >= TOTAL_CLAYS) {
            return;
        }
        const active = targets.filter((t) => t.active).length;
        if (active >= MAX_TARGETS) {
            return;
        }

        const flightPath = FLIGHT_PATTERN[nextFlightPatternIndex % FLIGHT_PATTERN.length];
        nextFlightPatternIndex += 1;
        const fromLeft = flightPath === 'crosser_ltr' || flightPath === 'looper_ltr' || flightPath === 'rabbit';

        // depth: 0 = far (small, higher, slower) → 1 = near (larger, lower, faster)
        const depth = 0.4 + Math.random() * 0.6;
        const r = TARGET_R * (0.52 + depth * 0.48);
        const grassH = Math.min(72, logicalH * 0.14);
        const horizonY = logicalH - grassH;

        // Defaults; each flight path overrides as needed.
        let x = fromLeft ? r + 6 : logicalW - r - 6;
        let y = logicalH * (0.18 + (1 - depth) * 0.24 + Math.random() * 0.06) + r * 0.15;
        let vx = fromLeft ? 5.6 + Math.random() * 1.6 : -(5.6 + Math.random() * 1.6);
        let vy = (Math.random() - 0.5) * 0.2;
        let gravityMul = 0;
        let maxAge = 220;
        let rabbitGroundY = null;

        /** @type {ClayPresentation} */
        let presentation = 'full';

        if (flightPath === 'crosser_ltr' || flightPath === 'crosser_rtl') {
            // Flat crossing line.
            y = logicalH * (0.2 + (1 - depth) * 0.22 + Math.random() * 0.06);
            vx = fromLeft ? 5.4 + Math.random() * 1.4 : -(5.4 + Math.random() * 1.4);
            vy = (Math.random() - 0.5) * 0.14;
            gravityMul = 0;
            maxAge = 230;
            presentation = Math.random() < 0.55 ? 'edge' : 'full';
        } else if (flightPath === 'looper_ltr' || flightPath === 'looper_rtl') {
            // Side-launched high arc that spans edge-to-edge across the canvas.
            const edgeInset = r + 6;
            const usableWidth = Math.max(80, logicalW - edgeInset * 2);
            const looperFrames = 112 + Math.floor(Math.random() * 24);
            const g = GRAVITY * 0.45;
            y = logicalH * (0.64 + Math.random() * 0.08);
            x = fromLeft ? edgeInset : logicalW - edgeInset;
            vx = (usableWidth / looperFrames) * (fromLeft ? 1 : -1);
            vy = -(0.5 * g * looperFrames) * (0.94 + Math.random() * 0.12);
            gravityMul = 0.45;
            maxAge = looperFrames + 8;
            presentation = 'quartering';
        } else if (flightPath === 'teal') {
            // Mostly vertical pop-up and drop.
            x = logicalW * (0.32 + Math.random() * 0.36);
            y = horizonY - (10 + Math.random() * 22);
            vx = (Math.random() - 0.5) * 0.8;
            vy = -(6.8 + Math.random() * 1.2);
            gravityMul = 1.05;
            maxAge = 210;
            presentation = Math.random() < 0.5 ? 'edge' : 'full';
        } else if (flightPath === 'rabbit') {
            // Ground roller with small bounces.
            const rabbitFromLeft = Math.random() < 0.5;
            rabbitGroundY = horizonY - (6 + Math.random() * 8);
            y = rabbitGroundY;
            x = rabbitFromLeft ? r + 6 : logicalW - r - 6;
            // Variable rabbit pace: mix slower rollers and faster skitters.
            const rabbitSpeed = 4.8 + Math.random() * 3.6;
            vx = rabbitFromLeft ? rabbitSpeed : -rabbitSpeed;
            vy = -0.5 - Math.random() * 0.5;
            gravityMul = 0.3;
            maxAge = 240;
            presentation = 'full';
        }

        // Quartering away: disc angled so the face opens “away” from the crossing direction
        const quarteringTilt = presentation === 'quartering' ? (fromLeft ? -0.5 : 0.5) : 0;

        targets.push({
            x,
            y,
            vx,
            vy,
            r,
            depth,
            presentation,
            quarteringTilt,
            flightPath,
            gravityMul,
            age: 0,
            maxAge,
            rabbitGroundY,
            active: true,
            shotsAtClay: 0,
        });
        claysReleased += 1;
        updateReleasedHud();
        updateClayTypeHud(flightPath);
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

    /**
     * @param {{ x: number; y: number; r: number; depth?: number; presentation?: ClayPresentation; quarteringTilt?: number; vx: number }} t
     * @param {number} [alphaMul=1] overall opacity multiplier (live targets use 1; ghosts use less)
     */
    function renderClayAppearance(t, alphaMul = 1) {
        const pres = t.presentation ?? 'full';
        const cfg = CLAY_PRESENTATION[pres];
        const rx = t.r * cfg.rxMul;
        const ry = t.r * cfg.ryMul;
        const rot = getTargetDrawRotation(t);

        ctx.save();
        ctx.translate(t.x, t.y);
        ctx.rotate(rot);
        ctx.globalAlpha = alphaMul * (0.78 + (t.depth ?? 0.5) * 0.22);

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

    function drawTarget(t) {
        if (!t.active) {
            return;
        }
        renderClayAppearance(t, 1);
    }

    function drawClayGhosts(now) {
        missClayGhosts = missClayGhosts.filter((g) => now < g.until);
        for (const g of missClayGhosts) {
            const phase = (g.until - now) / MISS_SHOT_SPOT_MS;
            const alphaMul = 0.14 + phase * 0.38;
            renderClayAppearance(g, alphaMul);
        }
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

        missShotSpots = missShotSpots.filter((s) => now < s.until);
        for (const s of missShotSpots) {
            const phase = (s.until - now) / MISS_SHOT_SPOT_MS;
            const r = Math.max(5, Math.min(11, logicalW * 0.016));
            ctx.save();
            ctx.lineCap = 'round';
            ctx.globalAlpha = 0.12 + phase * 0.38;
            ctx.fillStyle = 'rgba(254, 252, 248, 0.95)';
            ctx.beginPath();
            ctx.arc(s.x, s.y, r * 0.32, 0, Math.PI * 2);
            ctx.fill();
            ctx.globalAlpha = 0.2 + phase * 0.55;
            ctx.strokeStyle = 'rgba(251, 191, 36, 0.55)';
            ctx.lineWidth = 1.25;
            ctx.setLineDash([3, 4]);
            ctx.beginPath();
            ctx.arc(s.x, s.y, r * 1.65, 0, Math.PI * 2);
            ctx.stroke();
            ctx.globalAlpha = 0.08 + phase * 0.22;
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.lineWidth = 1;
            ctx.setLineDash([]);
            ctx.beginPath();
            ctx.arc(s.x, s.y, r * 2.35, 0, Math.PI * 2);
            ctx.stroke();
            ctx.restore();
        }

        if (missNote && now < missNote.until) {
            const fade = Math.max(0, (missNote.until - now) / 950);
            const padX = 10;
            const padY = 6;
            ctx.save();
            ctx.font = '600 12px "DM Sans", sans-serif';
            const textW = ctx.measureText(missNote.text).width;
            const boxW = textW + padX * 2;
            const boxH = 24;
            const bx = Math.min(Math.max(missNote.x - boxW / 2, 8), logicalW - boxW - 8);
            const by = Math.min(Math.max(missNote.y - 38, 8), logicalH - boxH - 8);
            ctx.globalAlpha = 0.2 + fade * 0.78;
            ctx.fillStyle = 'rgba(120, 53, 15, 0.94)';
            ctx.beginPath();
            ctx.roundRect(bx, by, boxW, boxH, 8);
            ctx.fill();
            ctx.globalAlpha = Math.min(1, 0.35 + fade * 0.8);
            ctx.fillStyle = '#ffedd5';
            ctx.textBaseline = 'middle';
            ctx.fillText(missNote.text, bx + padX, by + boxH / 2);
            ctx.restore();
        }
    }

    function step(now) {
        for (const t of targets) {
            if (!t.active) {
                continue;
            }
            t.age += 1;
            advanceClayMotion(t);

            const minX = t.r + 4;
            const maxX = logicalW - t.r - 4;

            if (t.x < minX || t.x > maxX) {
                t.active = false;
            }
            if (t.age > t.maxAge) {
                t.active = false;
            }
        }
        targets = targets.filter((t) => t.active);
        if (targets.length === 0) {
            updateClayTypeHud(null);
        }

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
            spawnIntervalMs = 1750 + Math.random() * 1650;
        }

        checkGameOver(now);
    }

    function checkGameOver(now) {
        if (!started || gameFinished) {
            return;
        }
        if (claysReleased < TOTAL_CLAYS) {
            pendingGameOverAt = null;
            return;
        }
        if (targets.length > 0) {
            pendingGameOverAt = null;
            return;
        }
        if (pendingGameOverAt === null) {
            pendingGameOverAt = now + 2000;
            return;
        }
        if (now >= pendingGameOverAt) {
            endGame();
        }
    }

    function endGame() {
        gameFinished = true;
        running = false;
        cancelAnimationFrame(raf);
        started = false;
        const hits = score;
        const misses = Math.max(0, TOTAL_CLAYS - hits);
        const pct = Math.round((hits / TOTAL_CLAYS) * 100);
        const result =
            pct >= 88
                ? {
                      rating: 'Master Gun',
                      message: 'Outstanding round. Smooth mount, sharp eyes, and great timing.',
                  }
                : pct >= 72
                  ? {
                        rating: 'Hot Streak',
                        message: 'Strong shooting. You are reading lines well and finishing cleanly.',
                    }
                  : pct >= 52
                    ? {
                          rating: 'Dialed In',
                          message: 'Solid foundations. Keep your muzzle moving through the target.',
                      }
                    : {
                          rating: 'Keep At It',
                          message: 'Good effort. Focus on lead and rhythm; the hits will come.',
                      };
        if (finalScoreEl) {
            finalScoreEl.textContent = String(hits);
        }
        if (overHitsEl) {
            overHitsEl.textContent = String(hits);
        }
        if (overMissedEl) {
            overMissedEl.textContent = String(misses);
        }
        if (overRateEl) {
            overRateEl.textContent = `${pct}%`;
        }
        if (ratingEl) {
            ratingEl.textContent = result.rating;
        }
        if (overMsgEl) {
            overMsgEl.textContent = result.message;
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
        drawClayGhosts(now);
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

        let nearest = null;
        let nearestDist = Infinity;
        for (const t of targets) {
            if (!t.active) {
                continue;
            }
            const nd = Math.hypot(cx - t.x, cy - t.y);
            if (nd < nearestDist) {
                nearestDist = nd;
                nearest = t;
            }
        }

        const intercept = findLeadInterceptHit(gunX, gunY, cx, cy, targets);
        if (intercept) {
            const hitT = intercept.t;
            spawnClayBreak(intercept.clayX, intercept.clayY, hitT.r, hitT.depth);
            hitT.active = false;
            score += 1;
            scoreEl.textContent = String(score);
            hitFlash = { until: now + 180, x: intercept.clayX, y: intercept.clayY };
            missNote = null;
            return;
        }

        if (!nearest) {
            missNote = { until: now + 950, text: 'No target up', x: cx, y: cy };
            return;
        }

        missShotSpots.push({ x: cx, y: cy, until: now + MISS_SHOT_SPOT_MS });
        if (missShotSpots.length > 24) {
            missShotSpots.splice(0, missShotSpots.length - 24);
        }

        missClayGhosts.push({
            until: now + MISS_SHOT_SPOT_MS,
            x: nearest.x,
            y: nearest.y,
            r: nearest.r,
            depth: nearest.depth,
            presentation: nearest.presentation,
            quarteringTilt: nearest.quarteringTilt,
            vx: nearest.vx,
        });
        if (missClayGhosts.length > 24) {
            missClayGhosts.splice(0, missClayGhosts.length - 24);
        }

        nearest.shotsAtClay += 1;
        const lostThisClay = nearest.shotsAtClay >= MAX_SHOTS_PER_CLAY;
        if (lostThisClay) {
            nearest.active = false;
        }

        const rdx = cx - nearest.x;
        const rdy = cy - nearest.y;
        const vx = nearest.vx;
        const vy = nearest.vy;
        const vlen = Math.hypot(vx, vy) || 1;
        const along = (rdx * vx + rdy * vy) / vlen;
        const perp = Math.abs(rdx * -vy + rdy * vx) / vlen;
        let reason = 'Just off line';
        if (perp > nearest.r * 1.85 && Math.abs(along) < nearest.r * 1.35) {
            reason = 'Wide of line';
        } else if (along > nearest.r * 1.0) {
            reason = 'Too far ahead';
        } else if (along < -nearest.r * 1.0) {
            reason = 'Behind — more lead';
        } else if (Math.abs(rdy) > Math.abs(rdx) * 1.25) {
            reason = rdy > 0 ? 'Shot under' : 'Shot over';
        } else if (nearestDist > nearest.r * 2.4) {
            reason = 'Off pattern';
        }
        let noteText = reason;
        if (lostThisClay) {
            noteText = 'Clay away — both shots used';
        } else {
            const left = MAX_SHOTS_PER_CLAY - nearest.shotsAtClay;
            noteText = `${reason} — ${left} shot${left === 1 ? '' : 's'} left`;
        }
        missNote = { until: now + 950, text: noteText, x: cx, y: cy };
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
        nextFlightPatternIndex = 0;
        score = 0;
        scoreEl.textContent = '0';
        claysReleased = 0;
        updateReleasedHud();
        updateClayTypeHud(null);
        gameFinished = false;
        pendingGameOverAt = null;
        lastSpawn = performance.now();
        spawnIntervalMs = 1000;
        hitFlash = null;
        shotLine = null;
        missNote = null;
        missShotSpots = [];
        missClayGhosts = [];
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
        canvas.setAttribute(
            'aria-label',
            'Clay shooting in progress. Aim ahead of the target so your shot meets the clay in flight.',
        );
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
            canvas.setAttribute(
                'aria-label',
                'Clay shooting in progress. Aim ahead of the target so your shot meets the clay in flight.',
            );
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
