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
document.getElementById("registerForm").addEventListener("submit", function(e) {
    const pass1 = document.getElementById("myInput").value;
    const pass2 = document.getElementById("myInput2").value;


    if (pass1 !== pass2) {
        e.preventDefault();
        alert("Passwords do not match.");
    }
})

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
    // regex implementation
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
