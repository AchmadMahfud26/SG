function initSoilMoistureChart(sensorData, pumpData) {
  const ctx = document.getElementById("soilMoistureChart").getContext("2d");

  // Ambil 1000 data terakhir apa adanya
  let filteredSensorData = sensorData.length > 1000 ? sensorData.slice(-1000) : sensorData;

  // Prepare labels and data (jam saja)
  const labels = filteredSensorData.map((d) => {
    const date = new Date(d.waktu);
    return date.toLocaleTimeString('id-ID', { hour12: false });
  });
  const soilMoistureValues = filteredSensorData.map((d) => d.kelembaban_tanah);

  // Prepare pump status data as background color or markers
  // We'll mark pump ON times on the chart as vertical lines or points
  const pumpOnTimes = pumpData
    .filter((d) => d.status === "ON")
    .map((d) => new Date(d.waktu).toLocaleString());

  const data = {
    labels: labels,
    datasets: [
      {
        label: "Kelembaban Tanah (%)",
        data: soilMoistureValues,
        borderColor: "rgba(75, 192, 192, 1)",
        backgroundColor: "rgba(75, 192, 192, 0.2)",
        fill: true,
        tension: 0.3,
        yAxisID: "y",
      },
    ],
  };

  const config = {
    type: "line",
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: "index",
        intersect: false,
      },
      stacked: false,
      scales: {
        y: {
          type: "linear",
          display: true,
          position: "left",
          min: 0,
          max: 100,
          ticks: {
            stepSize: 10,
            color: "#666",
            font: {
              size: 14,
              weight: "bold",
            },
            autoSkip: false,
            callback: function(value) {
              // Debug: log value yang diproses
              // console.log('Y tick:', value);
              // Tampilkan hanya label 0, 10, 20, ..., 100
              if (value >= 0 && value <= 100 && value % 10 === 0) {
                return value;
              }
              return null;
            }
          },
          grid: {
            color: "#ddd",
            borderColor: "#bbb",
            borderWidth: 2,
            drawBorder: true,
            drawTicks: true,
            tickLength: 10,
          },
          title: {
            display: true,
            text: "Kelembaban (%)",
            color: "#222",
            font: {
              size: 16,
              weight: "bold",
            },
          },
        },
        x: {
          display: true,
          title: {
            display: true,
            text: "Waktu",
            color: "#222",
            font: {
              size: 16,
              weight: "bold",
            },
          },
          ticks: {
            maxRotation: 45,
            minRotation: 45,
            autoSkip: true,
            maxTicksLimit: 10,
            color: "#666",
            font: {
              size: 12,
            },
          },
          grid: {
            display: false,
          },
        },
      },
      plugins: {
        tooltip: {
          enabled: true,
        },
        legend: {
          display: true,
        },
      },
    },
  };

  const soilMoistureChart = new Chart(ctx, config);

  // Resize chart on sidebar toggle
  const sidebarToggleBtn = document.getElementById("sidebarToggle");
  sidebarToggleBtn.addEventListener("click", () => {
    setTimeout(() => {
      soilMoistureChart.resize();
    }, 310); // wait for CSS transition to complete
  });

  // Optional: Add pump ON markers on chart (advanced)
  // This requires plugin or custom drawing, omitted for simplicity
  return soilMoistureChart;
}

// Fungsi untuk update data grafik secara dinamis
function updateChartData(chart, newData) {
  // Ambil 1000 data terakhir apa adanya
  let filteredSensorData = newData.length > 1000 ? newData.slice(-1000) : newData;
  chart.data.labels = filteredSensorData.map(d => {
    const date = new Date(d.waktu);
    return date.toLocaleTimeString('id-ID', { hour12: false });
  });
  chart.data.datasets[0].data = filteredSensorData.map(d => d.kelembaban_tanah);
  chart.update();
}
