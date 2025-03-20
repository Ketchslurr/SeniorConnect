<aside class="w-64 bg-white shadow-lg p-6 min-h-screen">
    <?php
    include '../../config.php'; 
    // session_start();

    // Ensure user is logged in
    if (!isset($_SESSION['seniorId'])) {
        header("Location: login.php");
        exit();
    }

    $seniorId = $_SESSION['seniorId'];

    // Fetch profile picture if not already in session
    if (!isset($_SESSION['profile_picture'])) {
        $sql = "SELECT profile_picture FROM seniorCitizen WHERE seniorId = :seniorId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['seniorId' => $seniorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['profile_picture'] = $result['profile_picture'] ?? 'default.png'; // Default if null
    }

    // Path to the profile picture
    $profilePicture = !empty($_SESSION['profile_picture']) ? "../../uploads/profile_pictures/" . $_SESSION['profile_picture'] : "../../assets/Icons/default.png";
    ?>

    <div class="flex flex-col items-center">
        <!-- Profile Picture -->
        <img src="<?php echo htmlspecialchars($profilePicture); ?>" class="w-24 h-24 rounded-full shadow-md border border-gray-300" alt="Profile Picture">
        <h2 class="text-xl font-semibold mt-4 text-gray-700"><?php echo $_SESSION['fname'] ?? 'Guest'; ?></h2>
    </div>

    <nav class="mt-10 space-y-4">
        <a href="../SeniorCitizen/seniorProfile.php" class="flex items-center space-x-2 text-gray-700 hover:text-blue-500">
            <span>👤</span><span>My Profile</span>
        </a>
        <a href="../SeniorCitizen/seniorCitizenDashboard.php" class="flex items-center space-x-2 text-gray-700 hover:text-blue-500">
            <span>🏠</span><span>Dashboard</span>
        </a>
        <a href="../SeniorCitizen/appointments.php" class="flex items-center space-x-2 text-gray-700 hover:text-blue-500">
            <span>📅</span><span>Appointments</span>
        </a>
        <a href="../SeniorCitizen/telehealth.php" class="flex items-center space-x-2 text-gray-700 hover:text-blue-500">
            <span>💻</span><span>Telehealth</span>
        </a>
        <a href="#" class="flex items-center space-x-2 text-gray-700 hover:text-blue-500">
            <span>📡</span><span>Real-Time Monitoring</span>
        </a>
        <a href="../SeniorCitizen/notifications.php" class="flex items-center space-x-2 text-gray-700 hover:text-blue-500">
            <span>🔔</span><span>Notifications</span>
        </a>
        <a href="#" class="flex items-center space-x-2 text-gray-700 hover:text-blue-500">
            <span>⚙️</span><span>Settings</span>
        </a>
    </nav>
</aside>
