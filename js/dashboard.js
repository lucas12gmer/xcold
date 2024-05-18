var fromDate = document.getElementById('from-date');
var toDate = document.getElementById('to-date');
var lastMonth = new Date();
lastMonth.setDate(lastMonth.getDate() - 30);
var today = new Date();
today.setDate(today.getDate() + 1);
fromDate.valueAsDate = lastMonth;
toDate.valueAsDate = today;

fromDate.addEventListener('change', updateCharts);
toDate.addEventListener('change', updateCharts);

updateCharts();


function updateCharts() {
    /* AJAX REQUEST */
    var xhr1 = new XMLHttpRequest();
    xhr1.open('POST', 'mysql/get_bans_per_day.php', true);
    xhr1.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr1.onload = function () {
        if (this.status === 200) {
            var data = JSON.parse(this.responseText);
            bansOverTimeChart.data.labels = data.labels;
            bansOverTimeChart.data.datasets[0].data = data.data;
            bansOverTimeChart.update();
        }
    }
    xhr1.send('fromDate=' + fromDate.value + '&toDate=' + toDate.value);

    var xhr2 = new XMLHttpRequest();
    xhr2.open('POST', 'mysql/get_ban_reasons.php', true);
    xhr2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr2.onload = function () {
        if (this.status === 200) {
            var data = JSON.parse(this.responseText);
            banReasonsChart.data.labels = data.labels;
            banReasonsChart.data.datasets[0].data = data.data;
            banReasonsChart.update();
        }
    }
    xhr2.send('fromDate=' + fromDate.value + '&toDate=' + toDate.value);
}

// initialize charts
var bansOverTimeChart = new Chart(document.getElementById("bansOverTimeChart"), {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Bans at this day',
            data: [],
            borderColor: 'rgb(142, 210, 205)',
            fill: true
                }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        scales: {
            y: {
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            title: {
                display: true,
                text: 'Amount of bans',
                font: {
                    size: 24
                }
            }
        }
    }
});
var banReasonsChart = new Chart(document.getElementById('banReasonsChart'), {
    type: 'pie',
    data: {
        labels: [],
        datasets: [{
            label: 'Bans with this reason',
            data: [],
            backgroundColor: [
                'rgb(142, 210, 205)',
                'rgb(152, 169, 215)',
                'rgb(245, 155, 124)',
                'rgb(194, 237, 152)',
                'rgb(241, 244, 135)',
                'rgb(254, 215, 118)',
            ]
        }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: "Ban reasons",
                font: {
                    size: 24
                }
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return ' Bans: ' + context.formattedValue;
                    }
                }
            },
            datalabels: {
                formatter: (value, categories) => {
                    let percentage = (value * 100 / categories.chart.data.datasets[0].data.reduce((a, b) => parseInt(a) + parseInt(b))).toFixed(2) + "%";
                    return percentage;


                },
                color: '#fff',
            }
        }
    },
    plugins: [ChartDataLabels],
});
