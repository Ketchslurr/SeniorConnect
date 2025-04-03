<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Fetch available doctors grouped by specialization
$sql = "SELECT hp.fname, hp.lname, hp.specialization, hp.profile_picture, hp.professionalId, hp.consultationFee 
        FROM available_doctors ad 
        JOIN healthcareprofessional hp ON ad.professionalId = hp.professionalId 
        WHERE ad.is_available = 1 
        ORDER BY hp.specialization";

$stmt = $pdo->query($sql);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group doctors by specialization
$specializedDoctors = [];
foreach ($doctors as $doctor) {
    $specializedDoctors[$doctor['specialization']][] = $doctor;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Services</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.2/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100" x-data="{ selectedSpecialization: null, showModal: false, selectedDoctor: null }">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>
        
        <main class="flex-1 p-6 flex flex-col items-center" @click="selectedSpecialization = null">
            <h2 class="text-3xl font-bold text-center mb-6">Available Specializations</h2>
            
            <div x-show="!selectedSpecialization" class="grid grid-cols-2 md-grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
                <?php foreach (array_keys($specializedDoctors) as $specialization): ?>
                    <div @click.stop="selectedSpecialization = '<?= htmlspecialchars($specialization) ?>'" 
                        class="bg-white p-6 rounded-lg shadow-md hover:shadow-2xl transition transform hover:-translate-y-2 cursor-pointer flex items-center justify-center text-center w-40 h-40">
                        <h3 class="text-xl font-semibold text-blue-700"> <?= htmlspecialchars($specialization) ?> </h3>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php foreach ($specializedDoctors as $specialization => $doctors): ?>
                <div x-show="selectedSpecialization === '<?= htmlspecialchars($specialization) ?>'" class="w-full" @click.stop>
                    <h3 class="text-2xl font-semibold mb-4 text-blue-700 text-center"> <?= htmlspecialchars($specialization) ?> </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($doctors as $doctor): ?>
                            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-2xl transition transform hover:-translate-y-2 flex flex-col items-center text-center" @click.stop>
                                <?php if (!empty($doctor['profile_picture'])): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($doctor['profile_picture']) ?>" class="w-24 h-24 rounded-full border mb-4" />
                                <?php else: ?>
                                    <img src="../../assets/Images/default-doctor.png" class="w-24 h-24 rounded-full border mb-4" />
                                <?php endif; ?>
                                <h3 class="text-xl font-semibold"> <?= htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']) ?> </h3>
                                <p class="text-gray-600">Consultation Fee: ₱<?= isset($doctor['consultationFee']) ? htmlspecialchars(number_format($doctor['consultationFee'], 2)) : '0.00' ?></p>
                                <div class="flex gap-4 mt-4">
                                                                    <!-- bookAppointment.php changed to appointmentBookingPage.php 4/4/2025 -->
                                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" 
                                        onclick="window.location.href='appointmentBookingPage.php?professionalId=<?= $doctor['professionalId'] ?>'">
                                        Consult Now!
                                    </button>
<!--                                    
                                    <button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition" 
                                        @click.stop="showModal = true; selectedDoctor = JSON.parse($el.dataset.doctor || '{}')"
                                        :data-doctor="<?= htmlspecialchars(json_encode($doctor, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
                                        View Profile
                                    </button> -->


                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
    
    <!-- Doctor Profile Modal -->
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" @click="showModal = false">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full" @click.stop>
            <h3 class="text-2xl font-semibold mb-4">Doctor Profile</h3>
            <template x-if="selectedDoctor">
                <div class="text-center">
                    <img :src="selectedDoctor.profile_picture ? 'data:image/jpeg;base64,' + selectedDoctor.profile_picture : '../../assets/Images/default-doctor.png'" class="w-24 h-24 rounded-full border mb-4 mx-auto" />
                    <h3 class="text-xl font-semibold" x-text="selectedDoctor.fname + ' ' + selectedDoctor.lname"></h3>
                    <p class="text-gray-600" x-text="'Specialization: ' + selectedDoctor.specialization"></p>
                    <p class="text-gray-600" x-text="'Consultation Fee: ₱' + Number(selectedDoctor.consultationFee).toFixed(2)"></p>
                    <button class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition" @click="showModal = false">
                        Close
                    </button>
                </div>
            </template>
        </div>
    </div>
</body>
</html>
