// Document Object Model (DOM) Content Loaded Event -> encased inside DOM
document.addEventListener("DOMContentLoaded", function () {
    // Show Password Function
    window.showPass = function () {
        const input1 = document.getElementById("myInput");
        const input2 = document.getElementById("myInput2");

        if (input1) {
            if (input1.type === "password") {
                input1.type = "text";
            } else {
                input1.type = "password";
            }
        }

        if (input2) {
            if (input2.type === "password") {
                input2.type = "text";
            } else {
                input2.type = "password";
            }
        }
    };

    // Password Match Validation
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            const pass1 = document.getElementById("myInput").value;
            const pass2 = document.getElementById("myInput2").value;

            if (pass1 !== pass2) {
                e.preventDefault();
                alert("Passwords do not match.");
            }
        });
    }

    // Edit & Save Row (Transactions)
    window.editRow = function (id) {
        const row = document.getElementById(`row-${id}`);
        const date = row.querySelector(".date");
        const desc = row.querySelector(".desc");
        const amount = row.querySelector(".amount");
        const editBtn = row.querySelector(".edit");
        const saveBtn = row.querySelector(".save");

        const dateValue = date.innerText.trim();
        const formattedDate = new Date(dateValue).toISOString().split('T')[0];

        date.innerHTML = `<input type="date" value="${formattedDate}" />`;
        desc.innerHTML = `<input type="text" value="${desc.innerText.trim()}" />`;
        
        // Regex implementation to handle currency formatting.
        amount.innerHTML = `<input type="number" step="0.01" value="${parseFloat(amount.innerText.replace(/₱|,/g, ''))}" />`;

        editBtn.style.display = "none";
        saveBtn.style.display = "inline-block";
    };

    window.saveRow = function (id) {
        const row = document.getElementById(`row-${id}`);
        const date = row.querySelector(".date input").value;
        const desc = row.querySelector(".desc input").value;
        const amount = row.querySelector(".amount input").value;
        const category = document.getElementById(`category-${id}`).value;
        const editBtn = row.querySelector(".edit");
        const saveBtn = row.querySelector(".save");

        fetch("update.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `id=${id}&date=${encodeURIComponent(date)}&description=${encodeURIComponent(desc)}&amount=${amount}&category=${encodeURIComponent(category)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                window.location.reload();
            } else {
                alert("Update failed: " + data);
            }
        })
        .catch(error => {
            alert("Error: " + error);
        });

        editBtn.style.display = "inline-block";
        saveBtn.style.display = "none";
    };

    // Budget Slider Sync
    const slider = document.getElementById("budgetSlider");
    const input = document.getElementById("budgetInput");

    if (slider && input) {
        const syncFromSlider = () => input.value = slider.value;

        const syncFromInput = () => {
            let value = parseFloat(input.value);
            const min = parseFloat(slider.min);
            const max = parseFloat(slider.max);
            if (!isNaN(value)) {
                value = Math.min(Math.max(value, min), max);
                slider.value = value;
            }
        };

        slider.addEventListener("input", syncFromSlider);
        input.addEventListener("input", syncFromInput);

        syncFromSlider();
    }

    // Tabs Switching
    const tabLinks = document.querySelectorAll(".tabsContainer a");
    const tabContents = document.querySelectorAll(".tab-content");

    tabLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            tabLinks.forEach(l => l.classList.remove("active"));
            tabContents.forEach(c => c.classList.remove("active"));
            this.classList.add("active");
            const target = document.querySelector(this.getAttribute("href"));
            if (target) target.classList.add("active");
        });
    });

    // Drag & Drop (SortableJS)
    const grid = document.getElementById("gridDraggable");
    if (grid) {
        new Sortable(grid, {
            animation: 150,
            ghostClass: "dragGhost",
            handle: ".drag-handle",
            draggable: ".box"
        });
    }

    // Pie Chart Setup (Chart.js)
    const pieCanvas = document.getElementById("spendingPieChart");
    if (pieCanvas && window.categoryLabels && window.categoryValues) {
        new Chart(pieCanvas.getContext("2d"), {
            type: "pie",
            data: {
                labels: window.categoryLabels,
                datasets: [{
                    data: window.categoryValues,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#66BB6A', '#EF5350'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: "bottom" },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                return `${label}: ₱${parseFloat(value).toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Category Select Update
    document.querySelectorAll(".category-select").forEach(select => {
        select.addEventListener("change", function () {
            const transactionId = this.id.split("-")[1];
            const newCategory = this.value;

            fetch("update_category.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `id=${transactionId}&category=${encodeURIComponent(newCategory)}`
            })
            .then(res => res.text())
            .then(response => {
                console.log(response);
                location.reload();
            })
            .catch(err => console.error("Error:", err));
        });
    });

});
