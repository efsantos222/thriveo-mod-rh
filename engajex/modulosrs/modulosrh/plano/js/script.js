document.addEventListener('DOMContentLoaded', function() {
    
    // Only initialize if the canvas exists
    const ctx = document.getElementById('portfolioMatrix');
    if (!ctx) return;

    // Define Quadrant Boxes for Annotation
    const annotations = {
        star: {
            type: 'box',
            xMin: 5, xMax: 10,
            yMin: 5, yMax: 10,
            backgroundColor: 'rgba(52, 211, 153, 0.2)', // Green
            borderColor: 'rgba(52, 211, 153, 0.0)',
            label: {
                display: true,
                content: ['ESTRELAS', 'Integrar e Liderar'],
                position: 'center',
                color: '#065f46',
                font: { size: 14, weight: 'bold' }
            }
        },
        cash: {
            type: 'box',
            xMin: 0, xMax: 5,
            yMin: 5, yMax: 10,
            backgroundColor: 'rgba(96, 165, 250, 0.2)', // Blue
            borderColor: 'transparent',
            label: {
                display: true,
                content: ['GERADORES DE CAIXA', 'Otimizar e Colher'],
                position: 'center',
                color: '#1e40af',
                font: { size: 14, weight: 'bold' }
            }
        },
        motor: {
            type: 'box',
            xMin: 5, xMax: 10,
            yMin: 0, yMax: 5,
            backgroundColor: 'rgba(248, 113, 113, 0.2)', // Red
            borderColor: 'transparent',
            label: {
                display: true,
                content: ['MOTORES', 'Estruturar e Padronizar'],
                position: 'center',
                color: '#b91c1c',
                font: { size: 14, weight: 'bold' }
            }
        },
        explorer: {
            type: 'box',
            xMin: 0, xMax: 5,
            yMin: 0, yMax: 5,
            backgroundColor: 'rgba(252, 211, 77, 0.2)', // Yellow/Amber
            borderColor: 'transparent',
            label: {
                display: true,
                content: ['EXPLORADORES', 'Investir ou Descontinuar'],
                position: 'center',
                color: '#b45309',
                font: { size: 14, weight: 'bold' }
            }
        }
    };

    new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Serviços',
                data: servicesData, // Defined in index.php
                backgroundColor: '#2563EB',
                borderColor: '#1e40af',
                pointHoverRadius: 10,
                pointRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'linear',
                    position: 'bottom',
                    min: 0,
                    max: 10,
                    title: {
                        display: true,
                        text: 'Sinergia Estratégica ->',
                        font: { size: 14, weight: 'bold' },
                        color: '#475569'
                    },
                    grid: {
                        color: '#cbd5e1'
                    }
                },
                y: {
                    min: 0,
                    max: 10,
                    title: {
                        display: true,
                        text: 'Maturidade Operacional ->',
                        font: { size: 14, weight: 'bold' },
                        color: '#475569'
                    },
                    grid: {
                        color: '#cbd5e1'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw.label + ` (X: ${context.raw.x}, Y: ${context.raw.y})`;
                        }
                    }
                },
                legend: {
                    display: false
                },
                annotation: {
                    annotations: annotations
                }
            }
        }
    });

});
