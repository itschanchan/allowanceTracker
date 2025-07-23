// Show Password Function
function showPass() {
    var x = document.getElementById("myInput");
    var y = document.getElementById("myInput2");

    if (x.type === "password") {
      x.type = "text";
    } 
    else {
      x.type = "password";
    }

    if (y.type === "password") {
      y.type = "text";
    } 
    else {
      y.type = "password";
    }
}

// Paswword Validation Function
function checkPass() {
  document.getElementById("registerForm").addEventListener("submit", function(event) {
    const pass = document.getElementById("myInput").value;
    const rePass = document.getElementById("myInput2").value;

    if (pass !== rePass) {
      event.preventDefault();
      alert("Passwords do not match. Please try again.");
    }
  });
}

// Pop-up Form For Setting Allowance
function openModal() {
    document.getElementById("allowanceModal").style.display = "block";
}

function closeModal() {
    document.getElementById("allowanceModal").style.display = "none";
}

window.onclick = function(event) {
    const modal = document.getElementById("allowanceModal");
    if (event.target == modal) {
        closeModal();
    }
};

// Switch Tab
document.addEventListener("DOMContentLoaded", function () {
    const tabLinks = document.querySelectorAll(".tabsContainer a");
    const tabContents = document.querySelectorAll(".tab-content");

    tabLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            // Remove active classes
            tabLinks.forEach(l => l.classList.remove("active"));
            tabContents.forEach(c => c.classList.remove("active"));

            // Add active to clicked tab and corresponding content
            this.classList.add("active");
            const targetId = this.getAttribute("href");
            document.querySelector(targetId).classList.add("active");
        });
    });
});

// Actions Column
function editRow(id) {
    const row = document.getElementById('row-' + id);
    const date = row.querySelector('.date');
    const desc = row.querySelector('.desc');
    const amount = row.querySelector('.amount');
    const editBtn = row.querySelector('.edit');
    const saveBtn = row.querySelector('.save');

    // Replace text with input fields
    date.innerHTML = `<input type="date" value="${date.innerText}" />`;
    desc.innerHTML = `<input type="text" value="${desc.innerText}" />`;
    amount.innerHTML = `<input type="number" step="0.01" value="${parseFloat(amount.innerText.replace(/₱|,/g, ''))}" />`;

    editBtn.style.display = "none";
    saveBtn.style.display = "inline-block";
}

function saveRow(id) {
    const row = document.getElementById('row-' + id);
    const date = row.querySelector('.date input').value;
    const desc = row.querySelector('.desc input').value;
    const amount = row.querySelector('.amount input').value;
    const editBtn = row.querySelector('.edit');
    const saveBtn = row.querySelector('.save');

    // Send data to update.php using AJAX
    fetch('update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&date=${encodeURIComponent(date)}&description=${encodeURIComponent(desc)}&amount=${amount}`
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            // Reload the page to reflect updated totals
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
}

