/**
 * charts-config.js — Engajex Chart.js Global Defaults
 * Carregue após Chart.js e antes dos scripts de cada página.
 */
(function () {
    if (typeof Chart === 'undefined') return;

    // ── Paleta global ────────────────────────────────────────
    window.EC = {
        color: {
            primary:  '#6366f1',
            violet:   '#8b5cf6',
            cyan:     '#06b6d4',
            emerald:  '#10b981',
            amber:    '#f59e0b',
            red:      '#ef4444',
            orange:   '#f97316',
            slate:    '#64748b',
            mood:     ['#ef4444','#f97316','#eab308','#84cc16','#22c55e'],
            moodBg:   ['rgba(239,68,68,.15)','rgba(249,115,22,.15)','rgba(234,179,8,.15)','rgba(132,204,22,.15)','rgba(34,197,94,.15)'],
        },

        /** Cria gradiente vertical para áreas de linha */
        linearGradient: function (ctx, hexColor, alphaTop, alphaBot) {
            var h = ctx.canvas.offsetHeight || 200;
            var g = ctx.createLinearGradient(0, 0, 0, h);
            g.addColorStop(0, hexColor + Math.round(alphaTop * 255).toString(16).padStart(2,'0'));
            g.addColorStop(1, hexColor + Math.round(alphaBot * 255).toString(16).padStart(2,'0'));
            return g;
        },
    };

    // ── Tipografia ───────────────────────────────────────────
    Chart.defaults.font.family  = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size    = 12;
    Chart.defaults.color        = '#94a3b8';

    // ── Legenda ──────────────────────────────────────────────
    Chart.defaults.plugins.legend.labels.color          = '#cbd5e1';
    Chart.defaults.plugins.legend.labels.padding        = 16;
    Chart.defaults.plugins.legend.labels.usePointStyle  = true;
    Chart.defaults.plugins.legend.labels.pointStyleWidth = 10;
    Chart.defaults.plugins.legend.labels.font           = { size: 12, weight: '500' };

    // ── Tooltip ──────────────────────────────────────────────
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(2, 6, 23, 0.92)';
    Chart.defaults.plugins.tooltip.titleColor       = '#f8fafc';
    Chart.defaults.plugins.tooltip.bodyColor        = '#94a3b8';
    Chart.defaults.plugins.tooltip.borderColor      = 'rgba(255,255,255,0.08)';
    Chart.defaults.plugins.tooltip.borderWidth      = 1;
    Chart.defaults.plugins.tooltip.padding          = { x: 14, y: 10 };
    Chart.defaults.plugins.tooltip.cornerRadius     = 10;
    Chart.defaults.plugins.tooltip.displayColors    = true;
    Chart.defaults.plugins.tooltip.boxPadding       = 5;
    Chart.defaults.plugins.tooltip.titleFont        = { size: 13, weight: '600' };
    Chart.defaults.plugins.tooltip.bodyFont         = { size: 12 };

    // ── Grade e eixos ────────────────────────────────────────
    Chart.defaults.scale.grid.color        = 'rgba(255,255,255,0.04)';
    Chart.defaults.scale.grid.borderColor  = 'rgba(255,255,255,0.04)';
    Chart.defaults.scale.ticks.color       = '#475569';
    Chart.defaults.scale.ticks.padding     = 8;

    // ── Animação ─────────────────────────────────────────────
    Chart.defaults.animation.duration = 700;
    Chart.defaults.animation.easing   = 'easeInOutQuart';

    // ── Elementos ────────────────────────────────────────────
    Chart.defaults.elements.line.tension      = 0.38;
    Chart.defaults.elements.line.borderWidth  = 2.5;
    Chart.defaults.elements.point.radius      = 4;
    Chart.defaults.elements.point.hoverRadius = 7;
    Chart.defaults.elements.point.borderWidth = 2;
    Chart.defaults.elements.bar.borderRadius  = 6;
    Chart.defaults.elements.bar.borderSkipped = false;
    Chart.defaults.elements.arc.borderWidth   = 0;
    Chart.defaults.elements.arc.hoverOffset   = 6;
})();
