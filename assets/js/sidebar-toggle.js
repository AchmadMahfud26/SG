document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggleBtn = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");
  const footer = document.querySelector(".site-footer");

  sidebarToggleBtn.addEventListener("click", function () {
    sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("expanded");
    if (footer) {
      footer.classList.toggle("footer-expanded");
    }
  });
});
