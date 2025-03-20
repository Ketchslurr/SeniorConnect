<?php
include '../../config.php';
include '../../includes/functions.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$professionalId = isset($_GET['professionalId']) ? intval($_GET['professionalId']) : 0;
if ($professionalId === 0) {
    die("Invalid doctor selection.");
}

$sql = "SELECT fname, lname, specialization, profile_picture FROM healthcareprofessional WHERE professionalId = :professionalId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die("Doctor not found.");
}

$sqlServices = "SELECT s.service_name, s.price FROM services s WHERE s.professionalId = :professionalId";
$stmtServices = $pdo->prepare($sqlServices);
$stmtServices->execute(['professionalId' => $professionalId]);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


    <style>
    /* Hover Effect */
    .service-btn:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        transition: all 0.2s ease-in-out;
    }

    /* Selected Effect */
    .service-btn.selected {
        border: 2px solid #3B82F6; /* Blue border */
    }

    .service-btn .price.selected-price {
        color: blue;
    }
    </style>

</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>
        <main class="flex-1 p-6">
            <a href="telehealth.php" class="text-blue-600 hover:underline">&larr; Back</a>
            <h2 class="text-3xl font-bold text-center mb-6">Book an Appointment</h2>

            <div class="bg-white p-6 rounded-lg shadow-md max-w-3xl mx-auto">
                <div class="flex items-center space-x-4">
                    <img src="<?= !empty($doctor['profile_picture']) ? 'data:image/jpeg;base64,' . base64_encode($doctor['profile_picture']) : '../../assets/Images/default-profile.png' ?>" class="w-24 h-24 rounded-full border" />
                    <div>
                        <p class="text-xl font-semibold"><?= htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($doctor['specialization']) ?></p>
                    </div>
                </div>
                
                <h3 class="text-xl font-bold mt-6">Select a Service</h3>
                <div class="flex flex-wrap gap-4 mt-4">
                    <?php foreach ($services as $service): ?>
                        <button class="service-btn p-3 bg-gray-50 rounded-lg shadow transition w-40" data-service="<?= $service['service_name'] ?>">
                            <?= htmlspecialchars($service['service_name']) ?> 
                            <span class="price text-blue-600 block">$<?= htmlspecialchars($service['price']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <h3 class="text-xl font-bold mt-6 ">Select a Date</h3>
                <div class="text-xl font-bold mt-6 flex justify-center items-center"><div id="appointmentDate"></div></div>
                <!-- <div class="flex justify-center items-center min-h-screen bg-gray-100">
                    <div id="appointmentDate"></div>
                </div> -->
                <!-- <input type="text" id="appointmentDate" class="mt-2 w-full p-3 border rounded" placeholder="Choose a date" readonly> -->

                <h3 class="text-xl font-bold mt-6">Select a Time</h3>
                <div id="timeSlots" class="grid grid-cols-4 gap-2 mt-2">
                    <!-- Time slots will be dynamically inserted here -->
                </div>
                
                <button class="mt-6 w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" id="confirmAppointment">Confirm Appointment</button>
            </div>
        </main>
    </div>

    <script>

      



    document.addEventListener("DOMContentLoaded", function () {
        let selectedDate = "";
        let selectedService = null;
    document.getElementById("appointmentDate").addEventListener("change", function () {
        const selectedDate = this.value;
        loadAvailableTimeSlots(selectedDate);
    });

        // Initialize Flatpickr
        flatpickr("#appointmentDate", {
            inline: true, 
            dateFormat: "Y-m-d",
            minDate: "today",
            onChange: function (selectedDates, dateStr) {
                selectedDate = dateStr;
                loadAvailableTimeSlots(dateStr);
            }
        });

        // Service Selection Logic
        const serviceButtons = document.querySelectorAll(".service-btn");
        serviceButtons.forEach(button => {
            button.addEventListener("click", function () {
                // Remove selection from all buttons
                serviceButtons.forEach(btn => {
                    btn.classList.remove("selected");
                    btn.querySelector(".price").classList.remove("selected-price");
                    btn.querySelector(".price").classList.add("text-blue-600");
                });

                // Apply selection to clicked button
                this.classList.add("selected");
                this.querySelector(".price").classList.remove("text-blue-600");
                this.querySelector(".price").classList.add("selected-price");
            });
        });

        // Load Available Time Slots
        function loadAvailableTimeSlots(date) {
    const timeSlots = document.getElementById("timeSlots");
    timeSlots.innerHTML = ""; // Clear previous slots

    const hours = [9, 10, 11, 12, 13, 14, 15, 16];
    const formattedTimes = hours.map(h => `${h % 12 || 12}:00 ${h < 12 ? "AM" : "PM"}`); // Convert to AM/PM

    fetch(`../../includes/fetchBookedSlots.php?professionalId=<?= $professionalId ?>&date=${date}`)
        .then(response => response.json())
        .then(bookedSlots => {
            console.log("Booked Slots:", bookedSlots); // Debugging

            formattedTimes.forEach(timeText => {
                const slot = document.createElement("button");
                slot.textContent = timeText;
                slot.classList.add("p-2", "border", "rounded", "text-center", "cursor-pointer", "hover:bg-blue-100", "m-1");

                if (bookedSlots.includes(timeText)) {
                    slot.classList.add("bg-gray-400", "cursor-not-allowed", "text-white");
                    slot.disabled = true;
                } else {
                    slot.classList.add("bg-white", "text-black", "hover:bg-blue-200");
                    slot.addEventListener("click", function () {
                        document.querySelectorAll("#timeSlots button").forEach(btn => btn.classList.remove("bg-blue-500", "text-white"));
                        this.classList.add("bg-blue-500", "text-white");
                    });
                }

                timeSlots.appendChild(slot);
            });
        })
        .catch(error => console.error("Error fetching slots:", error));
}


        // function loadAvailableTimeSlots(date) {
        //     const timeSlots = document.getElementById("timeSlots");
        //     timeSlots.innerHTML = "";
        //     const hours = [9, 10, 11, 12, 13, 14, 15, 16];

        //     hours.forEach(hour => {
        //         const slot = document.createElement("button");
        //         slot.textContent = `${hour}:00`;
        //         slot.classList.add("p-2", "border", "rounded", "text-center", "cursor-pointer", "hover:bg-blue-100");
        //         slot.addEventListener("click", function () {
        //             document.querySelectorAll("#timeSlots button").forEach(btn => btn.classList.remove("bg-blue-500", "text-white"));
        //             this.classList.add("bg-blue-500", "text-white");
        //         });
        //         timeSlots.appendChild(slot);
        //     });
        // }
    });



      //confirm appointment
document.getElementById("confirmAppointment").addEventListener("click", function () {
    const selectedService = document.querySelector(".service-btn.selected")?.getAttribute("data-service");
    const selectedDate = document.getElementById("appointmentDate").value;
    const selectedTime = document.querySelector("#timeSlots button.bg-blue-500")?.textContent;
    const professionalId = <?= json_encode($professionalId) ?>; // Get professionalId from PHP

    if (!selectedService || !selectedDate || !selectedTime) {
        alert("Please select a service.");
        return;
    }
    if (!selectedDate) {
        alert("Please select date.");
        return;
    }
    if ( !selectedTime) {
        alert("Please select time.");
        return;
    }

    fetch("../../includes/bookAppointmentHandler.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            professionalId: professionalId,
            service_name: selectedService,
            appointment_date: selectedDate,
            appointment_time: selectedTime
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.status === "success") {
            window.location.href = "appointments.php"; // Redirect to appointment list
        }
    })
    .catch(error => console.error("Error:", error));
});

</script>


    <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            let selectedDate = "";
            
            flatpickr("#appointmentDate", {
                inline: true, // This makes the calendar always visible
                dateFormat: "Y-m-d",
                minDate: "today",
                onChange: function(selectedDates, dateStr) {
                    selectedDate = dateStr;
                    loadAvailableTimeSlots(dateStr);
                }
            });
            
            function loadAvailableTimeSlots(date) {
                const timeSlots = document.getElementById("timeSlots");
                timeSlots.innerHTML = "";
                const hours = [9, 10, 11, 12, 13, 14, 15, 16];
                
                hours.forEach(hour => {
                    const slot = document.createElement("button");
                    slot.textContent = `${hour}:00`;
                    slot.classList.add("p-2", "border", "rounded", "text-center", "cursor-pointer", "hover:bg-blue-100");
                    slot.addEventListener("click", function() {
                        document.querySelectorAll("#timeSlots button").forEach(btn => btn.classList.remove("bg-blue-500", "text-white"));
                        this.classList.add("bg-blue-500", "text-white");
                    });
                    timeSlots.appendChild(slot);
                });
            }
        });
    </script> -->
</body>
</html>
