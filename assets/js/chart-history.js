function initSoilMoistureChart(sensorData, pumpData) {
  const ctx = document.getElementById("soilMoistureChart").getContext("2d");

  // Prepare labels and data
  const labels = sensorData.map((d) => new Date(d.waktu).toLocaleString());
  const soilMoistureValues = sensorData.map((d) => d.kelembaban_tanah);

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
          title: {
            display: true,
            text: "Kelembaban (%)",
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
}
