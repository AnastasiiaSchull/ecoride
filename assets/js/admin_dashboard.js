(() => {

    const container = document.getElementById('charts-data');
    if (!container) return;

    // -----------------------------
    // DATA SAFE PARSING
    // -----------------------------
    const parse = (value) => {
        try {
            return JSON.parse(value || '{}');
        } catch (e) {
            console.error('JSON invalide dans charts-data', e);
            return {};
        }
    };

    const charttrajetsData = parse(container.dataset.trajets);
    const chartcreditsData = parse(container.dataset.credits);

    // -----------------------------
    // CANVAS
    // -----------------------------
    const trajetsCanvas = document.getElementById('trajetsChart');
    const creditsCanvas = document.getElementById('creditsChart');

    // -----------------------------
    // SAFE OBJECT
    // -----------------------------
    const safeObj = (data) =>
        (data && typeof data === 'object' && !Array.isArray(data)) ? data : {};

    const tData = safeObj(charttrajetsData);
    const cData = safeObj(chartcreditsData);

    // -----------------------------
    // CHART TRAJETS
    // -----------------------------
    if (trajetsCanvas && Object.keys(tData).length > 0) {
        new Chart(trajetsCanvas, {
            type: 'bar',
            data: {
                labels: Object.keys(tData),
                datasets: [{
                    label: 'Trajets par jour',
                    data: Object.values(tData),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            }
        });
    }

    // -----------------------------
    // CHART CREDITS
    // -----------------------------
    if (creditsCanvas && Object.keys(cData).length > 0) {
        new Chart(creditsCanvas, {
            type: 'line',
            data: {
                labels: Object.keys(cData),
                datasets: [{
                    label: 'Crédits gagnés',
                    data: Object.values(cData),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.3,
                    fill: false
                }]
            }
        });
    }

})();