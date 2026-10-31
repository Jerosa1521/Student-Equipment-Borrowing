
async function loginUser(event) {
    event.preventDefault();

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    try {
        const response = await fetch("login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ username, password })
        });

        const result = await response.json();

        // Temporary diagnostic
        alert("Login response: " + JSON.stringify(result));

        if (!response.ok || !result.success) {
            alert(result.message || "Login failed.");
            return;
        }

        if (result.role === "admin") {
            window.location.href = "admin-dashboard.html";
        } else if (result.role === "student") {
            window.location.href = "student-dashboard.html";
        } else {
            alert("Unknown account role: " + result.role);
        }

    } catch (error) {
        console.error(error);
        alert("Login error. Check login.php and the browser console.");
    }
}
