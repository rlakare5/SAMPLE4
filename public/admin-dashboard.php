<?php
$totalFarmers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'farmer'")->fetchColumn();
$totalVendors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'vendor'")->fetchColumn();
$totalCrops = $pdo->query("SELECT COUNT(*) FROM crops")->fetchColumn();
$totalInquiries = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();

$farmers = $pdo->query("SELECT * FROM users WHERE role = 'farmer' ORDER BY created_at DESC LIMIT 10")->fetchAll();
$vendors = $pdo->query("SELECT * FROM users WHERE role = 'vendor' ORDER BY created_at DESC LIMIT 10")->fetchAll();
$recentCrops = $pdo->query("SELECT c.*, u.name as farmer_name FROM crops c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 10")->fetchAll();

$cropsByType = $pdo->query("SELECT crop_type, COUNT(*) as count FROM crops GROUP BY crop_type")->fetchAll();
$profitByMonth = $pdo->query("SELECT DATE_TRUNC('month', created_at) as month, SUM(estimated_profit) as total_profit FROM crops GROUP BY month ORDER BY month DESC LIMIT 6")->fetchAll();

$complaints = $pdo->query("SELECT c.*, u.name as user_name FROM complaints c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond_complaint'])) {
    $complaintId = intval($_POST['complaint_id']);
    $response = sanitizeInput($_POST['admin_response']);
    
    $stmt = $pdo->prepare("UPDATE complaints SET admin_response = ?, status = 'resolved', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$response, $complaintId]);
    
    header('Location: index.php?page=admin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AgriIntel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌱 AgriIntel Admin</div>
            <ul class="nav-links">
                <li><a href="index.php?page=admin">Dashboard</a></li>
                <li><span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
                <li><a href="index.php?page=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Farmers</h3>
                <p class="stat-value"><?= $totalFarmers ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Vendors</h3>
                <p class="stat-value"><?= $totalVendors ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Crops</h3>
                <p class="stat-value"><?= $totalCrops ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Inquiries</h3>
                <p class="stat-value"><?= $totalInquiries ?></p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Crops Distribution</h2>
                <canvas id="cropChart"></canvas>
            </div>
            <div class="card">
                <h2>Profit Trends (6 Months)</h2>
                <canvas id="profitChart"></canvas>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Recent Farmers</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($farmers as $farmer): ?>
                            <tr>
                                <td><?= htmlspecialchars($farmer['name']) ?></td>
                                <td><?= htmlspecialchars($farmer['email']) ?></td>
                                <td><?= htmlspecialchars($farmer['phone']) ?></td>
                                <td><?= date('d M Y', strtotime($farmer['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2>Recent Vendors</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><?= htmlspecialchars($vendor['name']) ?></td>
                                <td><?= htmlspecialchars($vendor['email']) ?></td>
                                <td><?= htmlspecialchars($vendor['phone']) ?></td>
                                <td><?= date('d M Y', strtotime($vendor['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Recent Crops Added</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Crop Type</th>
                            <th>Farmer</th>
                            <th>Area</th>
                            <th>Est. Yield</th>
                            <th>Est. Profit</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCrops as $crop): ?>
                        <tr>
                            <td><?= ucfirst($crop['crop_type']) ?></td>
                            <td><?= htmlspecialchars($crop['farmer_name']) ?></td>
                            <td><?= $crop['area'] ?> ha</td>
                            <td><?= number_format($crop['estimated_yield']) ?> kg</td>
                            <td><?= formatCurrency($crop['estimated_profit']) ?></td>
                            <td><?= date('d M Y', strtotime($crop['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2>Complaint Management</h2>
            <div class="table-container">
                <?php if (count($complaints) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td><?= htmlspecialchars($complaint['user_name']) ?></td>
                                <td><?= htmlspecialchars($complaint['subject']) ?></td>
                                <td><?= htmlspecialchars($complaint['description']) ?></td>
                                <td><span class="badge badge-<?= $complaint['status'] ?>"><?= $complaint['status'] ?></span></td>
                                <td>
                                    <?php if ($complaint['status'] === 'open'): ?>
                                        <button class="btn btn-sm btn-primary" onclick="showResponseForm(<?= $complaint['id'] ?>)">Respond</button>
                                    <?php else: ?>
                                        <?= htmlspecialchars($complaint['admin_response']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No complaints yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="responseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Respond to Complaint</h2>
            <form method="POST">
                <input type="hidden" name="respond_complaint" value="1">
                <input type="hidden" name="complaint_id" id="complaint_id">
                <div class="form-group">
                    <label>Your Response</label>
                    <textarea name="admin_response" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Response</button>
            </form>
        </div>
    </div>

    <script>
        const cropLabels = <?= json_encode(array_column($cropsByType, 'crop_type')) ?>;
        const cropData = <?= json_encode(array_column($cropsByType, 'count')) ?>;
        
        const ctx1 = document.getElementById('cropChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: cropLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                datasets: [{
                    data: cropData,
                    backgroundColor: ['#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0', '#00BCD4', '#FF9800', '#795548']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        const profitMonths = <?= json_encode(array_column($profitByMonth, 'month')) ?>;
        const profitData = <?= json_encode(array_column($profitByMonth, 'total_profit')) ?>;
        
        const ctx2 = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: profitMonths.map(m => new Date(m).toLocaleDateString('en-US', {month: 'short', year: 'numeric'})),
                datasets: [{
                    label: 'Estimated Profit (₹)',
                    data: profitData,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        function showResponseForm(complaintId) {
            document.getElementById('complaint_id').value = complaintId;
            document.getElementById('responseModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('responseModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('responseModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
