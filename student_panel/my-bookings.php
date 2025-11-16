<?php include("../assets/noSessionRedirect.php"); ?>
<?php include("./verifyRoleRedirect.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link rel="shortcut icon" href="./images/logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo"><img src="./images/logo.png" alt=""><h2>E<span class="danger">R</span>P</h2></div>
        <div class="navbar">
            <a href="index.php"><span class="material-icons-sharp">home</span><h3>Home</h3></a>
            <a href="bookings.php"><span class="material-icons-sharp">calendar_today</span><h3>Book Teacher</h3></a>
            <a href="my-bookings.php" class="active"><span class="material-icons-sharp">history</span><h3>My Bookings</h3></a>
            <a href="logout.php"><span class="material-icons-sharp">logout</span><h3>Logout</h3></a>
        </div>
    </header>
    <div class="container">
        <main>
            <h1>My Bookings</h1>
            <div id="bookingsList" style="margin-top:20px;"></div>
        </main>
    </div>
    <script>
        function loadBookings() {
            fetch('../assets/fetchStudentBookings.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) displayBookings(data.bookings);
                });
        }
        function displayBookings(bookings) {
            const statusColors = { 'pending': '#FF9800', 'confirmed': '#4CAF50', 'completed': '#2196F3', 'cancelled': '#f44336' };
            document.getElementById('bookingsList').innerHTML = bookings.length === 0 ? '<p>No bookings yet.</p>' : bookings.map(b => `
                <div style="border:1px solid #ddd; padding:20px; margin:15px 0; border-radius:8px; background:#f9f9f9;">
                    <h3>${b.teacher_name}</h3>
                    <p><strong>Subject:</strong> ${b.subject}</p>
                    <p><strong>Date:</strong> ${b.booking_date} | <strong>Time:</strong> ${b.time_slot}</p>
                    <p><strong>Purpose:</strong> ${b.purpose}</p>
                    ${b.notes ? `<p><strong>Notes:</strong> ${b.notes}</p>` : ''}
                    <p><strong>Status:</strong> <span style="color:${statusColors[b.status]}; font-weight:bold;">${b.status.toUpperCase()}</span></p>
                    ${b.status === 'pending' ? `<button onclick="cancelBooking(${b.s_no})" style="background:#f44336; color:white; padding:8px 16px; border:none; cursor:pointer; border-radius:5px;">Cancel</button>` : ''}
                </div>
            `).join('');
        }
        function cancelBooking(bookingId) {
            if (!confirm('Cancel this booking?')) return;
            fetch('../assets/cancelBooking.php', { method: 'POST', body: new URLSearchParams({booking_id: bookingId}) })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) loadBookings();
            });
        }
        loadBookings();
    </script>
</body>
</html>