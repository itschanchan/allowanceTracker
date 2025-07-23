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
    const row = document.getElementById(`row-${id}`);
    const date = row.querySelector('.date').innerText;
    const desc = row.querySelector('.desc').innerText;
    const amount = row.querySelector('.amount').innerText.replace('₱', '').trim();

    row.querySelector('.date').innerHTML = `<input type="date" name="edit_date" value="${date}">`;
    row.querySelector('.desc').innerHTML = `<input type="text" name="edit_desc" value="${desc}">`;
    row.querySelector('.amount').innerHTML = `<input type="number" name="edit_amount" step="0.01" value="${amount}">`;
    row.querySelector('.edit').style.display = 'none';
    row.querySelector('.save').style.display = 'inline-block';
}

function saveRow(id) {
    const row = document.getElementById(`row-${id}`);
    const date = row.querySelector('input[name="edit_date"]').value;
    const desc = row.querySelector('input[name="edit_desc"]').value;
    const amount = row.querySelector('input[name="edit_amount"]').value;

    fetch('updateTransaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&date=${encodeURIComponent(date)}&description=${encodeURIComponent(desc)}&amount=${amount}`
    })
    .then(response => response.text())
    .then(result => {
        location.reload();
    })
    .catch(error => {
        alert("Failed to update transaction.");
        console.error(error);
    });
}
