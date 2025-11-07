<?php
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_inquiry'])) {
    $cropId = intval($_POST['crop_id']);
    $farmerId = intval($_POST['farmer_id']);
    $message = sanitizeInput($_POST['message']);
    
    $stmt = $pdo->prepare("INSERT INTO inquiries (crop_id, vendor_id, farmer_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$cropId, $userId, $farmerId, $message]);
    
    $success = "Inquiry sent successfully!";
}

$filterCrop = isset($_GET['filter_crop']) ? $_GET['filter_crop'] : '';
$filterLocation = isset($_GET['filter_location']) ? $_GET['filter_location'] : '';

$query = "SELECT c.*, u.name as farmer_name, u.phone as farmer_phone, u.email as farmer_email, f.location_address 
          FROM crops c 
          JOIN users u ON c.user_id = u.id 
          LEFT JOIN farms f ON c.farm_id = f.id 
          WHERE 1=1";
$params = [];

if ($filterCrop) {
    $query .= " AND c.crop_type = ?";
    $params[] = $filterCrop;
}

if ($filterLocation) {
    $query .= " AND f.location_address LIKE ?";
    $params[] = "%$filterLocation%";
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$crops = $stmt->fetchAll();

$myInquiries = $pdo->prepare("SELECT i.*, c.crop_type, u.name as farmer_name FROM inquiries i JOIN crops c ON i.crop_id = c.id JOIN users u ON i.farmer_id = u.id WHERE i.vendor_id = ? ORDER BY i.created_at DESC");
$myInquiries->execute([$userId]);
$inquiries = $myInquiries->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - AgriIntel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌱 AgriIntel</div>
            <ul class="nav-links">
                <li><a href="index.php?page=vendor">Dashboard</a></li>
                <li><a href="index.php?page=chat">💬 Messages</a></li>
                <li><span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
                <li><a href="index.php?page=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Available Crops</h3>
                <p class="stat-value"><?= count($crops) ?></p>
            </div>
            <div class="stat-card">
                <h3>My Inquiries</h3>
                <p class="stat-value"><?= count($inquiries) ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Farmers</h3>
                <p class="stat-value"><?= count(array_unique(array_column($crops, 'farmer_name'))) ?></p>
            </div>
        </div>

        <div class="card">
            <h2>Filter Crops</h2>
            <form method="GET" class="filter-form">
                <input type="hidden" name="page" value="vendor">
                <div class="form-row">
                    <div class="form-group">
                        <label>Crop Type</label>
                        <select name="filter_crop">
                            <option value="">All Crops</option>
                            <option value="wheat" <?= $filterCrop === 'wheat' ? 'selected' : '' ?>>Wheat</option>
                            <option value="rice" <?= $filterCrop === 'rice' ? 'selected' : '' ?>>Rice</option>
                            <option value="corn" <?= $filterCrop === 'corn' ? 'selected' : '' ?>>Corn</option>
                            <option value="cotton" <?= $filterCrop === 'cotton' ? 'selected' : '' ?>>Cotton</option>
                            <option value="soybean" <?= $filterCrop === 'soybean' ? 'selected' : '' ?>>Soybean</option>
                            <option value="potato" <?= $filterCrop === 'potato' ? 'selected' : '' ?>>Potato</option>
                            <option value="tomato" <?= $filterCrop === 'tomato' ? 'selected' : '' ?>>Tomato</option>
                            <option value="onion" <?= $filterCrop === 'onion' ? 'selected' : '' ?>>Onion</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="filter_location" value="<?= htmlspecialchars($filterLocation) ?>" placeholder="Enter location">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="index.php?page=vendor" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Available Crops</h2>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <div class="table-container">
                <?php if (count($crops) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Crop Type</th>
                                <th>Area</th>
                                <th>Est. Yield</th>
                                <th>Market Price</th>
                                <th>Harvest Date</th>
                                <th>Farmer</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($crops as $crop): ?>
                            <tr>
                                <td><?= ucfirst($crop['crop_type']) ?></td>
                                <td><?= $crop['area'] ?> ha</td>
                                <td><?= number_format($crop['estimated_yield']) ?> kg</td>
                                <td><?= formatCurrency($crop['market_price']) ?>/kg</td>
                                <td><?= date('d M Y', strtotime($crop['expected_harvest_date'])) ?></td>
                                <td><?= htmlspecialchars($crop['farmer_name']) ?><br>
                                    <small><?= htmlspecialchars($crop['farmer_phone']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($crop['location_address']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="showInquiryForm(<?= $crop['id'] ?>, <?= $crop['user_id'] ?>, '<?= htmlspecialchars($crop['farmer_name']) ?>')">
                                        Contact
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No crops found matching your criteria.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>My Inquiries</h2>
            <div class="table-container">
                <?php if (count($inquiries) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Crop</th>
                                <th>Farmer</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td><?= ucfirst($inquiry['crop_type']) ?></td>
                                <td><?= htmlspecialchars($inquiry['farmer_name']) ?></td>
                                <td><?= htmlspecialchars($inquiry['message']) ?></td>
                                <td><span class="badge badge-<?= $inquiry['status'] ?>"><?= $inquiry['status'] ?></span></td>
                                <td><?= date('d M Y', strtotime($inquiry['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No inquiries sent yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="inquiryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Send Inquiry</h2>
            <form method="POST" id="inquiryForm">
                <input type="hidden" name="send_inquiry" value="1">
                <input type="hidden" name="crop_id" id="inquiry_crop_id">
                <input type="hidden" name="farmer_id" id="inquiry_farmer_id">
                <div class="form-group">
                    <label>Farmer</label>
                    <input type="text" id="farmer_name_display" readonly>
                </div>
                <div class="form-group">
                    <label>Your Message</label>
                    <textarea name="message" rows="4" required placeholder="Enter your inquiry message..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Inquiry</button>
            </form>
        </div>
    </div>

    <script>
        function showInquiryForm(cropId, farmerId, farmerName) {
            document.getElementById('inquiry_crop_id').value = cropId;
            document.getElementById('inquiry_farmer_id').value = farmerId;
            document.getElementById('farmer_name_display').value = farmerName;
            document.getElementById('inquiryModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('inquiryModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('inquiryModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
