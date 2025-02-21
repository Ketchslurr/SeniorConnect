<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];

// Fetch healthcare professional details
$sql = "SELECT * FROM healthcareprofessional WHERE userId = :userId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $userId]);
$professional = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch services offered
$servicesQuery = "SELECT * FROM services WHERE professionalId = :userId";
$servicesStmt = $pdo->prepare($servicesQuery);
$servicesStmt->execute(['userId' => $userId]);
$services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

// Check current availability status
$query = "SELECT is_available FROM available_doctors WHERE professionalId = :userId";
$stmt = $pdo->prepare($query);
$stmt->execute(['userId' => $userId]);
$doctorAvailability = $stmt->fetch(PDO::FETCH_ASSOC);
$isAvailable = $doctorAvailability ? $doctorAvailability['is_available'] : 0;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex flex-col lg:flex-row">
        <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Manage Your Services</h2>

          <!-- Profile Section -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <?php if (!empty($professional['profile_picture'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($professional['profile_picture']) ?>" class="w-24 h-24 rounded-full border" />
                    <?php else: ?>
                        <img src="../../assets/Images/woman.jpg" class="w-24 h-24 rounded-full border" />
                    <?php endif; ?>

                    <div>
                        <p class="text-xl font-semibold"><?= htmlspecialchars($professional['fname'] ?? 'Unknown') ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($professional['specialization'] ?? 'No Specialization') ?></p>
                        
                        <!-- Availability Toggle with Modal -->
                <div class="flex items-center">
                <p class="text-xl font-semibold mr-3">Set Availability: </>

                    <form method="POST" action="updateAvailability.php">
                        <input type="hidden" name="is_available" value="<?= $isAvailable ? 0 : 1 ?>">
                        <button type="button" onclick="showModal()" 
                            class="w-14 h-7 rounded-full flex items-center justify-between px-1 transition-all duration-300 <?= $isAvailable ? 'bg-blue-500' : 'bg-gray-300' ?>">
                            <span class="text-white text-sm"><?= $isAvailable ? 'ON' : 'OFF' ?></span>
                            <div class="w-5 h-5 bg-white rounded-full shadow-md transform <?= $isAvailable ? 'translate-x-6' : '' ?>"></div>
                        </button>
                    </form>
                </div>
                <button class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            onclick="document.getElementById('editProfile').style.display='flex'">
                            Edit Profile
                        </button>
                    </div>
                </div>

                
            </div>

            <!-- Services Offered -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-2xl font-bold mb-4">Services Offered</h3>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 mb-4" onclick="document.getElementById('addService').style.display='flex'">
                    + Add Service
                </button>
                <ul class="space-y-4">
                    <?php foreach ($services as $service) { ?>
                        <li class="border p-4 rounded-lg flex justify-between flex-col md:flex-row">
                            <div>
                                <p class="text-xl font-semibold"><?= htmlspecialchars($service['service_name']) ?></p>
                                <p class="text-gray-600"><?= htmlspecialchars($service['description']) ?></p>
                                <p class="text-blue-600">₱<?= htmlspecialchars($service['price']) ?></p>
                            </div>
                            <div class="flex space-x-2 mt-2 md:mt-0">
                                <button class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700" onclick="editService(<?= $service['serviceId'] ?>, '<?= htmlspecialchars($service['service_name']) ?>', '<?= htmlspecialchars($service['description']) ?>', <?= $service['price'] ?>)">
                                    ✏️ Edit
                                </button>
                                <button class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700" onclick="openDeleteModal(<?= $service['serviceId'] ?>, '<?= htmlspecialchars($service['service_name']) ?>')">
                                    ❌ Delete
                                </button>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if (empty($services)) { ?>
                        <p class="text-gray-500">No services added yet.</p>
                    <?php } ?>
                </ul>
            </div>
        </main>
    </div>


    <!-- availability Modal -->
<div id="confirmModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <p class="text-lg font-semibold mb-4">Are you sure you want to change your availability?</p>
        <form method="POST" action="../../includes/updateAvailability.php">
            <input type="hidden" name="is_available" value="<?= $isAvailable ? 0 : 1 ?>">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 mr-2">Yes</button>
            <button type="button" onclick="hideModal()" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">No</button>
        </form>
    </div>
</div>
    <!-- Edit Profile Modal -->
    <div id="editProfile" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center overflow-y-auto hidden">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md max-h-screen overflow-y-auto">
            <h3 class="text-2xl font-bold mb-4 text-center">Edit Profile</h3>
            <form action="editProfile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="userId" value="<?= $userId ?>">
                <div class="flex justify-center mb-4">
                    <?php if (!empty($professional['profile_picture'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($professional['profile_picture']) ?>" class="w-24 h-24 rounded-full border" />
                    <?php else: ?>
                        <img src="../../assets/Images/woman.jpg" class="w-24 h-24 rounded-full border" />
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Profile Picture</label>
                    <input type="file" name="profile_picture" class="w-full p-2 border rounded" onchange="previewImage(event)">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Full Name</label>
                    <input type="text" name="fname" value="<?= htmlspecialchars($professional['fname']) ?>" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Last Name</label>
                    <input type="text" name="lname" value="<?= htmlspecialchars($professional['lname']) ?>" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($professional['email']) ?>" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Specialization</label>
                    <input type="text" name="specialization" value="<?= htmlspecialchars($professional['specialization']) ?>" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Age</label>
                    <input type="text" value="<?= htmlspecialchars($professional['age']) ?>" class="w-full p-2 border rounded bg-gray-200" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Gender</label>
                    <input type="text" value="<?= htmlspecialchars($professional['gender']) ?>" class="w-full p-2 border rounded bg-gray-200" readonly>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                    <button type="button" class="ml-4 px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500" onclick="document.getElementById('editProfile').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
     <!-- Edit Service Modal -->
     <div id="editServiceModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h3 class="text-2xl font-bold mb-4">Edit Service</h3>
            <form id="editServiceForm">
                <input type="hidden" id="editServiceId">
                <div class="mb-4">
                    <label class="block text-gray-700">Service Name</label>
                    <input type="text" id="editServiceName" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Description</label>
                    <textarea id="editServiceDescription" class="w-full p-2 border rounded" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Price</label>
                    <input type="number" id="editServicePrice" class="w-full p-2 border rounded" required>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="px-4 py-2 bg-gray-400 text-white rounded-lg mr-2" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
        <h3 class="text-xl font-bold mb-4">Confirm Delete</h3>
        <p id="deleteMessage" class="mb-4"></p>
        <form id="deleteForm" method="POST" action="../../includes/deleteService.php">
            <input type="hidden" name="serviceId" id="deleteServiceId">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
            </button>
            <button type="button" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 ml-2" onclick="closeDeleteModal()">
                Cancel
            </button>
        </form>
    </div>
</div>


<!-- Add Service Modal -->
<div id="addService" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-2xl font-bold mb-4">Add Service</h3>
        <form action="../../includes/addService.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700">Service Name</label>
                <input type="text" name="service_name" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Description</label>
                <textarea name="description" class="w-full p-2 border rounded" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Price</label>
                <input type="number" name="price" class="w-full p-2 border rounded" required>
            </div>
            <div class="flex justify-end">
                <button type="button" class="px-4 py-2 bg-red-400 text-white rounded-lg mr-2" onclick="closeAddServiceModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Add Service</button>
            </div>
        </form>
    </div>
</div>

<script>
      function openDeleteModal(serviceId, serviceName) {
    document.getElementById('deleteServiceId').value = serviceId;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${serviceName}"?`;
    document.getElementById('deleteModal').classList.remove('hidden'); // Show modal
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden'); // Hide modal
}
    </script>

<script>
    function showModal() {
        document.getElementById('confirmModal').classList.remove('hidden');
    }
    function hideModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }
</script>
    <!-- JavaScript -->
    <script>
       function editService(serviceId, name, description, price) {
    document.getElementById('editServiceId').value = serviceId;
    document.getElementById('editServiceName').value = name;
    document.getElementById('editServiceDescription').value = description;
    document.getElementById('editServicePrice').value = price;
    document.getElementById('editServiceModal').style.display = 'flex';
}

function deleteService(serviceId) {
    alert("Service has been deleted successfully.");
        window.location.href = "../../includes/deleteService.php?serviceId=" + serviceId;
    
}

function closeAddServiceModal() {
    document.getElementById('addService').style.display = 'none';
}

function closeEditModal() {
    document.getElementById('editServiceModal').style.display = 'none';
}
    
    function closeDeleteModal() {
        document.getElementById('deleteServiceModal').style.display = 'none';
    }


        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profilePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
