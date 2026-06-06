fetch('lerCSV.php')
    .then(response => response.json())
    .then(dados => {
        Object.keys(dados).forEach((arquivo, index) => {
            const canvas = document.createElement('canvas');
            canvas.id = `grafico-${index}`;
            document.getElementById('graficos').appendChild(canvas);
            
            new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: ['Águia', 'Gato', 'Tubarão', 'Lobo'],
                    datasets: [{
                        data: Object.values(dados[arquivo]),
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: arquivo
                    }
                }
            });
        });
    });
