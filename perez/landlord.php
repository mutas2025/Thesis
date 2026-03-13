<?php
session_start();
require 'config.php';
requireLogin();
checkRole(['landlord']);

 $landlord_id = $_SESSION['user_id'];
 $msg = "";
 $msgType = "";
 $receiptData = null; 

// --- LOGGING HELPER FUNCTION ---
function logActivity($pdo, $user_id, $action, $entity, $entity_id = null, $description = "") {
    try {
        $ip = $_SERVER['REMOTE_ADDR'];
        // Sanitize description for safety
        $desc = htmlspecialchars($description); 
        $stmt = $pdo->prepare("INSERT INTO ActivityLogs (user_id, action_type, entity_type, entity_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $entity, $entity_id, $desc, $ip]);
    } catch (Exception $e) {
        // Fail silently to not break the main application flow
        error_log("Logging failed: " . $e->getMessage());
    }
}

// --- HANDLE URL PARAMETERS (FOR REDIRECT/MESSAGES/RECEIPT) ---
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    $msgType = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'info';
}

// Fetch receipt data if redirected from payment submission
if (isset($_GET['show_receipt'])) {
    $payment_id = (int)$_GET['show_receipt'];
    $stmtReceipt = $pdo->prepare("SELECT p.*, u.first_name, u.last_name, bh.house_name, rm.room_number FROM Payments p 
        JOIN Rentals re ON p.rental_id = re.rental_id 
        JOIN Users u ON re.boarder_id = u.user_id 
        JOIN Rooms rm ON re.room_id = rm.room_id 
        JOIN BoardingHouses bh ON rm.house_id = bh.house_id 
        WHERE p.payment_id = ?");
    $stmtReceipt->execute([$payment_id]);
    $receiptData = $stmtReceipt->fetch(PDO::FETCH_ASSOC);
}

// --- IMAGE UPLOAD HELPER (Rooms) ---
function handleRoomImageUpload($fileKey) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $targetDir = "uploads/rooms/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES[$fileKey]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
    
    if(in_array($fileType, $allowedTypes)){
        if(move_uploaded_file($_FILES[$fileKey]["tmp_name"], $targetFilePath)){
            return $targetFilePath;
        }
    }
    return null;
}

// --- AJAX: SEARCH USERS ---
if (isset($_GET['action']) && $_GET['action'] == 'search_users') {
    header('Content-Type: application/json');
    $query = "%" . $_GET['q'] . "%";
    try {
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email FROM Users WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?) AND role = 'boarder' LIMIT 10");
        $stmt->execute([$query, $query, $query]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit; 
}

// --- HANDLE FORM SUBMISSIONS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Helper function for redirect to prevent duplicate submissions on refresh
    function redirectWithMessage($msg, $type, $receiptId = null) {
        $params = "msg=" . urlencode($msg) . "&type=" . urlencode($type);
        if ($receiptId) {
            $params .= "&show_receipt=" . $receiptId;
        }
        header("Location: landlord.php?" . $params);
        exit;
    }

    // 1. Add/Edit Property
    if (isset($_POST['add_property'])) {
        try {
            $pdo->beginTransaction();
            $stmtAddr = $pdo->prepare("INSERT INTO Addresses (street_number, street_name, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtAddr->execute([$_POST['street_number'], $_POST['street_name'], $_POST['city'], $_POST['state'], $_POST['postal_code'], $_POST['country']]);
            $address_id = $pdo->lastInsertId();
            
            $stmtHouse = $pdo->prepare("INSERT INTO BoardingHouses (landlord_id, address_id, house_name, description, amenities, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmtHouse->execute([$landlord_id, $address_id, $_POST['house_name'], $_POST['description'], $_POST['amenities']]);
            $newHouseId = $pdo->lastInsertId();
            
            // LOG: Added Property
            logActivity($pdo, $landlord_id, 'create', 'property', $newHouseId, "Added new property: " . $_POST['house_name']);
            
            $pdo->commit();
            redirectWithMessage("Property added successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            redirectWithMessage("Error adding property: " . $e->getMessage(), "danger");
        }
    }
    elseif (isset($_POST['edit_property'])) {
        try {
            $pdo->beginTransaction();
            $stmtAddr = $pdo->prepare("UPDATE Addresses SET street_number=?, street_name=?, city=?, state=?, postal_code=?, country=? WHERE address_id=?");
            $stmtAddr->execute([$_POST['street_number'], $_POST['street_name'], $_POST['city'], $_POST['state'], $_POST['postal_code'], $_POST['country'], $_POST['address_id']]);
            
            $stmtHouse = $pdo->prepare("UPDATE BoardingHouses SET house_name=?, description=?, amenities=? WHERE house_id=?");
            $stmtHouse->execute([$_POST['house_name'], $_POST['description'], $_POST['amenities'], $_POST['house_id']]);
            
            // LOG: Updated Property
            logActivity($pdo, $landlord_id, 'update', 'property', $_POST['house_id'], "Updated property: " . $_POST['house_name']);
            
            $pdo->commit();
            redirectWithMessage("Property updated successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            redirectWithMessage("Error updating property: " . $e->getMessage(), "danger");
        }
    }
    elseif (isset($_POST['delete_property'])) {
        try {
            $house_id = $_POST['house_id'];
            // Get name for log before deleting
            $stmtName = $pdo->prepare("SELECT house_name FROM BoardingHouses WHERE house_id = ?");
            $stmtName->execute([$house_id]);
            $houseName = $stmtName->fetchColumn();

            $check = $pdo->prepare("SELECT COUNT(*) FROM Rentals r JOIN Rooms rm ON r.room_id = rm.room_id WHERE rm.house_id = ? AND r.status = 'active'");
            $check->execute([$house_id]);
            if ($check->fetchColumn() > 0) { throw new Exception("Cannot delete property with active rentals."); }
            
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM Rooms WHERE house_id = ?")->execute([$house_id]);
            $stmt = $pdo->prepare("SELECT address_id FROM BoardingHouses WHERE house_id = ?");
            $stmt->execute([$house_id]);
            $addr = $stmt->fetch();
            $pdo->prepare("DELETE FROM BoardingHouses WHERE house_id = ?")->execute([$house_id]);
            if($addr) { $pdo->prepare("DELETE FROM Addresses WHERE address_id = ?")->execute([$addr['address_id']]); }
            
            // LOG: Deleted Property
            logActivity($pdo, $landlord_id, 'delete', 'property', $house_id, "Deleted property: " . ($houseName ?: 'Unknown'));
            
            $pdo->commit();
            redirectWithMessage("Property deleted successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            redirectWithMessage("Error deleting property: " . $e->getMessage(), "danger");
        }
    }

    // 2. Add/Edit Room
    if (isset($_POST['add_room'])) {
        try {
            $imagePath = handleRoomImageUpload('room_image');
            $stmt = $pdo->prepare("INSERT INTO Rooms (house_id, room_number, floor_number, price_per_month, capacity, room_type, amenities, status, room_image) VALUES (?, ?, ?, ?, ?, ?, ?, 'available', ?)");
            $stmt->execute([$_POST['house_id'], $_POST['room_number'], $_POST['floor_number'], $_POST['price_per_month'], $_POST['capacity'], $_POST['room_type'], $_POST['room_amenities'], $imagePath]);
            $newRoomId = $pdo->lastInsertId();

            // Get House Name for log context
            $stmtH = $pdo->prepare("SELECT house_name FROM BoardingHouses WHERE house_id = ?");
            $stmtH->execute([$_POST['house_id']]);
            $hName = $stmtH->fetchColumn();

            // LOG: Added Room
            logActivity($pdo, $landlord_id, 'create', 'room', $newRoomId, "Added Room {$_POST['room_number']} to property: " . $hName);

            redirectWithMessage("Room added successfully!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error adding room: " . $e->getMessage(), "danger");
        }
    }
    elseif (isset($_POST['edit_room'])) {
        try {
            $imagePath = $_POST['existing_room_image'] ?? null;
            if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
                $newImage = handleRoomImageUpload('room_image');
                if ($newImage) { $imagePath = $newImage; }
            }
            
            $stmt = $pdo->prepare("UPDATE Rooms SET house_id=?, room_number=?, floor_number=?, price_per_month=?, capacity=?, room_type=?, amenities=?, room_image=? WHERE room_id=?");
            $stmt->execute([$_POST['house_id'], $_POST['room_number'], $_POST['floor_number'], $_POST['price_per_month'], $_POST['capacity'], $_POST['room_type'], $_POST['room_amenities'], $imagePath, $_POST['room_id']]);

            // LOG: Updated Room
            logActivity($pdo, $landlord_id, 'update', 'room', $_POST['room_id'], "Updated Room {$_POST['room_number']} details");

            redirectWithMessage("Room updated successfully!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error updating room: " . $e->getMessage(), "danger");
        }
    }
    elseif (isset($_POST['delete_room'])) {
        try {
            $room_id = $_POST['room_id'];
            $check = $pdo->prepare("SELECT COUNT(*) FROM Rentals WHERE room_id = ? AND status = 'active'");
            $check->execute([$room_id]);
            if ($check->fetchColumn() > 0) { throw new Exception("Cannot delete room with an active rental."); }
            
            // Get room info before deletion
            $stmtInfo = $pdo->prepare("SELECT room_number, house_id FROM Rooms WHERE room_id = ?");
            $stmtInfo->execute([$room_id]);
            $roomInfo = $stmtInfo->fetch();

            $stmt = $pdo->prepare("DELETE FROM Rooms WHERE room_id = ?");
            $stmt->execute([$room_id]);

            // LOG: Deleted Room
            logActivity($pdo, $landlord_id, 'delete', 'room', $room_id, "Deleted Room " . ($roomInfo['room_number'] ?? ''));

            redirectWithMessage("Room deleted successfully!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error deleting room: " . $e->getMessage(), "danger");
        }
    }

    // 3. Announcements
    if (isset($_POST['add_announcement'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Announcements (house_id, landlord_id, title, content, is_pinned) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['house_id'], $landlord_id, $_POST['title'], $_POST['content'], isset($_POST['is_pinned']) ? 1 : 0]);
            $annId = $pdo->lastInsertId();

            // LOG: Added Announcement
            logActivity($pdo, $landlord_id, 'create', 'announcement', $annId, "Posted announcement: " . $_POST['title']);

            redirectWithMessage("Announcement posted!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error posting announcement: " . $e->getMessage(), "danger");
        }
    }
    elseif (isset($_POST['edit_announcement'])) {
        try {
            $stmt = $pdo->prepare("UPDATE Announcements SET house_id=?, title=?, content=?, is_pinned=? WHERE announcement_id=? AND landlord_id=?");
            $stmt->execute([$_POST['house_id'], $_POST['title'], $_POST['content'], isset($_POST['is_pinned']) ? 1 : 0, $_POST['announcement_id'], $landlord_id]);

            // LOG: Updated Announcement
            logActivity($pdo, $landlord_id, 'update', 'announcement', $_POST['announcement_id'], "Updated announcement: " . $_POST['title']);

            redirectWithMessage("Announcement updated!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error updating announcement: " . $e->getMessage(), "danger");
        }
    }
    elseif (isset($_POST['delete_announcement'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Announcements WHERE announcement_id = ? AND landlord_id = ?");
            $stmt->execute([$_POST['announcement_id'], $landlord_id]);

            // LOG: Deleted Announcement
            logActivity($pdo, $landlord_id, 'delete', 'announcement', $_POST['announcement_id'], "Deleted announcement ID: " . $_POST['announcement_id']);

            redirectWithMessage("Announcement deleted!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error deleting announcement: " . $e->getMessage(), "danger");
        }
    }

    // 4. Create Rental
    if (isset($_POST['create_rental'])) {
        try {
            $pdo->beginTransaction();
            $stmtUser = $pdo->prepare("SELECT user_id, first_name, last_name FROM Users WHERE email = ? AND role = 'boarder'");
            $stmtUser->execute([$_POST['boarder_email']]);
            $boarder = $stmtUser->fetch();
            if (!$boarder) throw new Exception("Boarder email not found.");
            
            $stmtRoom = $pdo->prepare("SELECT capacity, current_occupancy, price_per_month, room_number, house_id FROM Rooms WHERE room_id = ?");
            $stmtRoom->execute([$_POST['room_id']]);
            $roomData = $stmtRoom->fetch();
            if($roomData['current_occupancy'] >= $roomData['capacity']) { throw new Exception("Room is already at full capacity."); }

            $stmtApp = $pdo->prepare("INSERT INTO Applications (room_id, boarder_id, status, reviewed_by, application_date) VALUES (?, ?, 'approved', ?, NOW())");
            $stmtApp->execute([$_POST['room_id'], $boarder['user_id'], $landlord_id]);
            $app_id = $pdo->lastInsertId();

            $advanceAmount = floatval($_POST['advance_amount']);
            $depositAmount = floatval($_POST['deposit_amount']);
            $totalDeposit = $advanceAmount + $depositAmount;

            $stmtRent = $pdo->prepare("INSERT INTO Rentals (application_id, room_id, boarder_id, start_date, end_date, deposit_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmtRent->execute([$app_id, $_POST['room_id'], $boarder['user_id'], $_POST['start_date'], $_POST['end_date'], $totalDeposit]);
            $rentalId = $pdo->lastInsertId();
            
            $pdo->prepare("UPDATE Rooms SET status = 'occupied', current_occupancy = current_occupancy + 1 WHERE room_id = ?")->execute([$_POST['room_id']]);

            // LOG: Created Rental
            $boarderName = $boarder['first_name'] . ' ' . $boarder['last_name'];
            logActivity($pdo, $landlord_id, 'create', 'rental', $rentalId, "Created rental agreement for $boarderName in Room {$roomData['room_number']}");

            $pdo->commit();
            redirectWithMessage("Rental agreement created successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            redirectWithMessage("Error creating rental: " . $e->getMessage(), "danger");
        }
    }

    // 4b. UPDATE RENTAL STATUS
    if (isset($_POST['update_rental'])) {
        try {
            $rental_id = (int)$_POST['rental_id'];
            $new_status = $_POST['status'];
            $new_end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            $stmtCheck = $pdo->prepare("SELECT status, room_id, end_date FROM Rentals WHERE rental_id = ?");
            $stmtCheck->execute([$rental_id]);
            $rentalInfo = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$rentalInfo) { throw new Exception("Rental not found."); }

            $pdo->beginTransaction();
            
            $stmtUpdate = $pdo->prepare("UPDATE Rentals SET status = ?, end_date = ? WHERE rental_id = ?");
            $stmtUpdate->execute([$new_status, $new_end_date, $rental_id]);

            if ($rentalInfo['status'] == 'active' && $new_status != 'active') {
                $room_id = $rentalInfo['room_id'];
                $pdo->prepare("UPDATE Rooms SET current_occupancy = GREATEST(0, current_occupancy - 1) WHERE room_id = ?")->execute([$room_id]);
                $pdo->prepare("UPDATE Rooms SET status = CASE WHEN current_occupancy = 0 THEN 'available' ELSE status END WHERE room_id = ?")->execute([$room_id]);
                $msg = "Rental updated, status changed to $new_status, and room vacancy updated.";
                
                // LOG: Ended Rental
                logActivity($pdo, $landlord_id, 'update', 'rental', $rental_id, "Changed rental status to $new_status (Ended)");
            } else {
                $msg = "Rental status and end date updated successfully.";
                
                // LOG: Updated Rental Details
                logActivity($pdo, $landlord_id, 'update', 'rental', $rental_id, "Updated rental details/end date");
            }
            $pdo->commit();
            redirectWithMessage($msg, "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            redirectWithMessage("Error updating rental: " . $e->getMessage(), "danger");
        }
    }

    // 5. Approve Application
    if (isset($_POST['approve_app'])) {
        $pdo->prepare("UPDATE Applications SET status = 'approved', reviewed_by = ? WHERE application_id = ?")->execute([$landlord_id, $_POST['app_id']]);

        // LOG: Approved Application
        logActivity($pdo, $landlord_id, 'approve', 'application', $_POST['app_id'], "Approved application ID: " . $_POST['app_id']);

        redirectWithMessage("Application approved!", "success");
    }

    // 6. UPDATE PAYMENT STATUS
    if (isset($_POST['update_payment_status'])) {
        try {
            $payment_id = (int)$_POST['payment_id'];
            $new_status = $_POST['status'];
            
            if ($new_status === 'paid') {
                $stmt = $pdo->prepare("UPDATE Payments SET status = 'paid', payment_date = NOW() WHERE payment_id = ?");
                $stmt->execute([$payment_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE Payments SET status = ? WHERE payment_id = ?");
                $stmt->execute([$new_status, $payment_id]);
            }

            // LOG: Updated Payment Status
            logActivity($pdo, $landlord_id, 'update', 'payment', $payment_id, "Marked payment ID $payment_id as $new_status");
            
            redirectWithMessage("Payment status updated successfully!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error updating payment: " . $e->getMessage(), "danger");
        }
    }

    // 6b. ADD NEW PAYMENT
    if (isset($_POST['add_payment'])) {
        try {
            $rental_id = $_POST['rental_id'];
            $due_date = $_POST['due_date'];
            $dateObj = new DateTime($due_date);
            $month = $dateObj->format('m');
            $year = $dateObj->format('Y');
            
            // Check for duplicates
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Payments WHERE rental_id = ? AND status = 'paid' AND MONTH(due_date) = ? AND YEAR(due_date) = ?");
            $checkStmt->execute([$rental_id, $month, $year]);
            if ($checkStmt->fetchColumn() > 0) { throw new Exception("Payment for " . $dateObj->format('F Y') . " has already been recorded."); }
            
            $amount = floatval($_POST['amount']);
            $penalty = floatval($_POST['penalty']);
            $totalPaid = $amount + $penalty;
            
            $stmt = $pdo->prepare("INSERT INTO Payments (rental_id, amount, penalty_fee, due_date, payment_method, status) VALUES (?, ?, ?, ?, ?, 'paid')");
            $stmt->execute([$rental_id, $totalPaid, $penalty, $due_date, $_POST['payment_method']]);
            $payment_id = $pdo->lastInsertId();

            // LOG: Recorded Payment
            logActivity($pdo, $landlord_id, 'create', 'payment', $payment_id, "Recorded manual payment of $totalPaid for Rental ID $rental_id");
            
            redirectWithMessage("Payment recorded successfully!", "success", $payment_id);
        } catch (Exception $e) {
            redirectWithMessage("Error adding payment: " . $e->getMessage(), "danger");
        }
    }

    // 7. UPDATE MAINTENANCE REQUEST
    if (isset($_POST['update_maintenance'])) {
        try {
            $status = $_POST['status'];
            $resolved_at = ($status == 'resolved') ? 'NOW()' : 'NULL';
            $stmt = $pdo->prepare("UPDATE MaintenanceRequests SET status = ?, resolved_at = $resolved_at, assigned_to = ? WHERE request_id = ?");
            $stmt->execute([$status, $landlord_id, $_POST['request_id']]);

            // LOG: Updated Maintenance
            logActivity($pdo, $landlord_id, 'update', 'maintenance', $_POST['request_id'], "Updated maintenance request to status: $status");

            redirectWithMessage("Maintenance request updated!", "success");
        } catch (Exception $e) {
            redirectWithMessage("Error updating request: " . $e->getMessage(), "danger");
        }
    }
}

// --- FETCH DATA ---
 $stmtHouses = $pdo->prepare("SELECT * FROM BoardingHouses WHERE landlord_id = ?");
 $stmtHouses->execute([$landlord_id]);
 $houses = $stmtHouses->fetchAll();

 $stmtAllRooms = $pdo->query("SELECT r.room_id, r.house_id, r.room_number, r.status, r.price_per_month, r.room_type, r.capacity, r.current_occupancy, r.floor_number, r.amenities as room_amenities, r.room_image, bh.house_name 
    FROM Rooms r 
    JOIN BoardingHouses bh ON r.house_id = bh.house_id 
    WHERE bh.landlord_id = $landlord_id");
 $allRooms = $stmtAllRooms->fetchAll();

 $stmtAnn = $pdo->prepare("SELECT * FROM Announcements WHERE landlord_id = ? ORDER BY is_pinned DESC, created_at DESC");
 $stmtAnn->execute([$landlord_id]);
 $announcements = $stmtAnn->fetchAll();

 $stmtApps = $pdo->prepare("SELECT a.*, u.first_name, u.last_name, u.email, r.room_number 
    FROM Applications a 
    JOIN Users u ON a.boarder_id = u.user_id 
    JOIN Rooms r ON a.room_id = r.room_id 
    JOIN BoardingHouses bh ON r.house_id = bh.house_id 
    WHERE bh.landlord_id = ? AND a.status = 'pending'");
 $stmtApps->execute([$landlord_id]);
 $apps = $stmtApps->fetchAll();

// FIXED RENTALS QUERY
 $stmtRentals = $pdo->query("SELECT re.*, u.first_name, u.last_name, r.room_number, bh.house_name, r.price_per_month as room_price, 
    addr.street_number, addr.street_name, addr.city, addr.state, addr.postal_code 
    FROM Rentals re 
    JOIN Users u ON re.boarder_id = u.user_id 
    JOIN Rooms r ON re.room_id = r.room_id 
    JOIN BoardingHouses bh ON r.house_id = bh.house_id 
    JOIN Addresses addr ON bh.address_id = addr.address_id
    WHERE bh.landlord_id = $landlord_id AND re.status = 'active'");
 $rentals = $stmtRentals->fetchAll();

// Group Payments by Rental ID for efficient backlog checking
 $paymentsByRental = [];
 $paidMonthsByRental = []; // Key: rental_id, Value: ['2023-01', '2023-02']
foreach($rentals as $r) {
    $paymentsByRental[$r['rental_id']] = [];
}
// We need to fetch payments separately to group them correctly
 $stmtAllPayments = $pdo->query("SELECT p.rental_id, p.status, p.due_date FROM Payments p JOIN Rentals re ON p.rental_id = re.rental_id JOIN Rooms rm ON re.room_id = rm.room_id JOIN BoardingHouses bh ON rm.house_id = bh.house_id WHERE bh.landlord_id = $landlord_id");
 $allPaymentRecords = $stmtAllPayments->fetchAll();

foreach($allPaymentRecords as $p) {
    if($p['status'] == 'paid') {
        $monthKey = date('Y-m', strtotime($p['due_date']));
        if (!isset($paidMonthsByRental[$p['rental_id']])) {
            $paidMonthsByRental[$p['rental_id']] = [];
        }
        $paidMonthsByRental[$p['rental_id']][] = $monthKey;
    }
}

// PAYMENTS with Proof Image
 $stmtPayments = $pdo->prepare("SELECT p.payment_id, p.rental_id, p.amount, p.due_date, p.payment_date, p.payment_method, p.status, p.penalty_fee, p.proof_image_url, CONCAT(u.first_name, ' ', u.last_name) as tenant_name, u.user_id as tenant_id, bh.house_name, r.room_number, r.price_per_month as current_rent_price
    FROM Payments p 
    JOIN Rentals re ON p.rental_id = re.rental_id 
    JOIN Users u ON re.boarder_id = u.user_id 
    JOIN Rooms r ON re.room_id = r.room_id 
    JOIN BoardingHouses bh ON r.house_id = bh.house_id 
    WHERE bh.landlord_id = ? 
    ORDER BY p.payment_date DESC");
 $stmtPayments->execute([$landlord_id]);
 $payments = $stmtPayments->fetchAll();

 $stmtMaintenance = $pdo->prepare("SELECT mr.*, bh.house_name, r.room_number, CONCAT(u.first_name, ' ', u.last_name) as reporter_name 
    FROM MaintenanceRequests mr 
    JOIN Rooms r ON mr.room_id = r.room_id 
    JOIN BoardingHouses bh ON r.house_id = bh.house_id 
    LEFT JOIN Users u ON mr.reported_by = u.user_id 
    WHERE bh.landlord_id = ? 
    ORDER BY mr.priority DESC, mr.created_at DESC");
 $stmtMaintenance->execute([$landlord_id]);
 $maintenanceRequests = $stmtMaintenance->fetchAll();

// --- REPORTS DATA ---
 $stmtFinancials = $pdo->query("SELECT re.rental_id, bh.house_name, r.room_number, CONCAT(u.first_name, ' ', u.last_name) as boarder_name, r.price_per_month, re.start_date, re.end_date 
    FROM Rentals re 
    JOIN Rooms r ON re.room_id = r.room_id 
    JOIN BoardingHouses bh ON r.house_id = bh.house_id 
    JOIN Users u ON re.boarder_id = u.user_id 
    WHERE bh.landlord_id = $landlord_id AND re.status = 'active'");
 $financialData = $stmtFinancials->fetchAll();

 $totalMonthlyRevenue = 0;
foreach($financialData as $fin) { $totalMonthlyRevenue += $fin['price_per_month']; }

 $stmtTenantReport = $pdo->prepare("SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) as tenant_name, SUM(p.amount) as total_paid, COUNT(p.payment_id) as payment_count
    FROM Payments p
    JOIN Rentals re ON p.rental_id = re.rental_id
    JOIN Users u ON re.boarder_id = u.user_id
    JOIN Rooms r ON re.room_id = r.room_id
    JOIN BoardingHouses bh ON r.house_id = bh.house_id
    WHERE bh.landlord_id = ? AND p.status = 'paid'
    GROUP BY u.user_id
    ORDER BY total_paid DESC");
 $stmtTenantReport->execute([$landlord_id]);
 $tenantReportData = $stmtTenantReport->fetchAll();

 $occupancyData = [];
foreach($houses as $h) {
    $totalRooms = $pdo->prepare("SELECT COUNT(*) FROM Rooms WHERE house_id = ?");
    $totalRooms->execute([$h['house_id']]);
    $tR = $totalRooms->fetchColumn();
    $occupiedRooms = $pdo->prepare("SELECT COUNT(*) FROM Rooms WHERE house_id = ? AND status = 'occupied'");
    $occupiedRooms->execute([$h['house_id']]);
    $oR = $occupiedRooms->fetchColumn();
    $occupancyData[] = [
        'house_name' => $h['house_name'], 'total' => $tR, 'occupied' => $oR,
        'percent' => $tR > 0 ? round(($oR / $tR) * 100, 1) : 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Landlord | AdminLTE</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    .content-section { display: none; }
    .content-section.active { display: block; }
    .nav-sidebar .nav-link.active { background-color: #007bff; color: #fff; }
    .priority-high { color: #dc3545; font-weight: bold; }
    .priority-med { color: #ffc107; font-weight: bold; }
    .priority-low { color: #28a745; font-weight: bold; }
    .room-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
    .search-results { position: absolute; background: white; border: 1px solid #ccc; width: 100%; z-index: 1000; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .search-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
    .search-item:hover { background-color: #f8f9fa; }
    .search-email { font-size: 0.85em; color: #666; }

    .document-paper { background: white; padding: 40px; border: 1px solid #ddd; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-height: 70vh; overflow-y: auto; }
    .agreement-body { font-family: 'Times New Roman', Times, serif; line-height: 1.6; font-size: 14px; }
    .agreement-title { text-align: center; text-transform: uppercase; font-weight: bold; font-size: 24px; margin-bottom: 30px; text-decoration: underline;}
    .signature-line { margin-top: 50px; border-top: 1px solid #000; width: 80%; }
    .signature-label { margin-top: 5px; font-weight: bold; }
    .receipt-box { border: 2px dashed #ccc; padding: 20px; margin-top: 10px; background: #fff; }
    .receipt-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
    .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px dotted #ccc; padding-bottom: 4px;}
    .receipt-total { border-top: 2px solid #000; padding-top: 10px; font-weight: bold; font-size: 1.2em; display: flex; justify-content: space-between; margin-top: 20px; }
    
    .content-wrapper { position: relative; z-index: 800; background-color: #f4f6f9; }
    .proof-img-thumbnail { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #ddd; }
    .proof-img-thumbnail:hover { opacity: 0.8; }
    
    @media print {
        .wrapper, .main-sidebar, .main-header, .no-print, .content-header, .main-footer, .toast-container { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding-top: 0 !important; min-height: auto !important; background: white; display: block !important; position: static; }
        .modal { display: none !important; }
        body.printing-agreement #rentalAgreementModal { display: block !important; position: static; opacity: 1 !important; overflow: visible !important; }
        body.printing-agreement .modal-backdrop { display: none !important; }
        body.printing-agreement .modal-dialog { max-width: 100%; margin: 0; padding: 0; width: 100%; }
        body.printing-agreement .modal-content { border: none; box-shadow: none; background: white; color: black; }
        body.printing-agreement .modal-header, body.printing-agreement .modal-footer { display: none !important; }
        body.printing-agreement .modal-body { padding: 0 !important; }
        body.printing-agreement .document-paper { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; max-width: 100% !important; width: 100% !important; }
        body.printing-agreement, body.printing-agreement * { color: black !important; background: white !important; }

        body.printing-receipt #receiptModal { display: block !important; position: static; opacity: 1 !important; overflow: visible !important; }
        body.printing-receipt .modal-backdrop { display: none !important; }
        body.printing-receipt .modal-dialog { max-width: 100%; margin: 0; padding: 0; width: 100%; }
        body.printing-receipt .modal-content { border: none; box-shadow: none; background: white; color: black; }
        body.printing-receipt .modal-header, body.printing-receipt .modal-footer { display: none !important; }
        body.printing-receipt .modal-body { padding: 0 !important; }
        body.printing-receipt .document-paper { border: 2px solid #000 !important; padding: 20px !important; margin: 0 !important; max-width: 100% !important; width: 100% !important; }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light no-print">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
    
    <!-- USER DISPLAY FIX: MATCHING ADMIN.PHP STYLE -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <span class="nav-link text-muted">
            <i class="fas fa-user-circle mr-1"></i> 
            Logged in as: <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?>
        </span>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4 no-print">
    <a href="#" class="brand-link"><span class="brand-text font-weight-light">Landlord Portal</span></a>
    <div class="sidebar">
      
      <!-- USER DISPLAY FIX: MATCHING ADMIN.PHP USER PANEL -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['first_name'] ?? 'Admin'); ?>&background=3b82f6&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')); ?></a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"><a href="#" class="nav-link active" data-tab-id="dashboard" onclick="switchTab('dashboard', this)"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="#" class="nav-link" data-tab-id="properties" onclick="switchTab('properties', this)"><i class="nav-icon fas fa-building"></i><p>Properties</p></a></li>
          <li class="nav-item"><a href="#" class="nav-link" data-tab-id="rooms" onclick="switchTab('rooms', this)"><i class="nav-icon fas fa-door-open"></i><p>Rooms</p></a></li>
          <li class="nav-item"><a href="#" class="nav-link" data-tab-id="rentals" onclick="switchTab('rentals', this)"><i class="nav-icon fa fa-key"></i><p>Rentals</p></a></li>
          <li class="nav-header">MANAGEMENT</li>
          <li class="nav-item"><a href="#" class="nav-link" data-tab-id="payments" onclick="switchTab('payments', this)"><i class="nav-icon fas fa-credit-card"></i><p>Payments</p></a></li>
          <li class="nav-item"><a href="#" class="nav-link" data-tab-id="maintenance" onclick="switchTab('maintenance', this)"><i class="nav-icon fas fa-tools"></i><p>Maintenance</p></a></li>
          <li class="nav-item"><a href="#" class="nav-link" data-tab-id="announcements" onclick="switchTab('announcements', this)"><i class="nav-icon fas fa-bullhorn"></i><p>Announcements</p></a></li>
          <li class="nav-header">REPORTS</li>
          <li class="nav-item"><a href="#" class="nav-link" data-tab-id="reports" onclick="switchTab('reports', this)"><i class="nav-icon fas fa-chart-line"></i><p>Financial & Stats</p></a></li>
          <li class="nav-header">ACCOUNT</li>
          <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header no-print">
      <div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0" id="page-title">Dashboard</h1></div></div></div>
    </div>

    <section class="content"><div class="container-fluid">
        
        <!-- DASHBOARD SECTION -->
        <div id="dashboard" class="content-section active">
          <div class="row">
            <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo count($houses); ?></h3><p>Properties</p></div><div class="icon"><i class="fas fa-building"></i></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo count($allRooms); ?></h3><p>Total Rooms</p></div><div class="icon"><i class="fas fa-bed"></i></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo count($apps); ?></h3><p>Pending Apps</p></div><div class="icon"><i class="fas fa-file-alt"></i></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?php echo count($rentals); ?></h3><p>Active Rentals</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
          </div>
          <div class="row">
            <div class="col-12"><div class="card"><div class="card-header"><h3 class="card-title">Recent Pending Applications</h3></div><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap"><thead><tr><th>Applicant</th><th>Room</th><th>Date</th><th>Action</th></tr></thead><tbody><?php foreach($apps as $app): ?><tr><td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td><td><?php echo htmlspecialchars($app['room_number']); ?></td><td><?php echo $app['application_date']; ?></td><td><form method="POST" style="display:inline;"><input type="hidden" name="app_id" value="<?php echo $app['application_id']; ?>"><input type="hidden" name="approve_app" value="1"><button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button></form></td></tr><?php endforeach; ?></tbody></table></div></div></div>
          </div>
        </div>

        <!-- PROPERTIES SECTION -->
        <div id="properties" class="content-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title" id="propFormTitle">Add New Property</h3></div>
                        <form method="POST">
                            <div class="card-body">
                                <input type="hidden" name="house_id" id="prop_house_id">
                                <input type="hidden" name="address_id" id="prop_address_id">
                                <h6 class="text-primary border-bottom pb-1">Address</h6>
                                <div class="row"><div class="col-4"><input type="text" name="street_number" id="prop_street_number" class="form-control form-control-sm mb-2" placeholder="#" required></div><div class="col-8"><input type="text" name="street_name" id="prop_street_name" class="form-control form-control-sm mb-2" placeholder="Street Name" required></div></div>
                                <div class="row"><div class="col-6"><input type="text" name="city" id="prop_city" class="form-control form-control-sm mb-2" placeholder="City" required></div><div class="col-6"><input type="text" name="state" id="prop_state" class="form-control form-control-sm mb-2" placeholder="State" required></div></div>
                                <div class="row"><div class="col-6"><input type="text" name="postal_code" id="prop_postal_code" class="form-control form-control-sm mb-2" placeholder="Zip" required></div><div class="col-6"><input type="text" name="country" id="prop_country" class="form-control form-control-sm mb-2" value="Philippines" required></div></div>
                                <h6 class="text-primary border-bottom pb-1 mt-3">Details</h6>
                                <div class="form-group"><input type="text" name="house_name" id="prop_house_name" class="form-control" placeholder="Property Name" required></div>
                                <div class="form-group"><textarea name="description" id="prop_description" class="form-control" rows="2" placeholder="Description"></textarea></div>
                                <div class="form-group"><input type="text" name="amenities" id="prop_amenities" class="form-control" placeholder="Amenities (WiFi, Gym...)"></div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="add_property" id="propSubmitBtn" class="btn btn-primary">Add Property</button>
                                <button type="button" class="btn btn-default float-right" onclick="resetPropertyForm()"><i class="fas fa-undo"></i> Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">My Properties</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-striped">
                                <thead><tr><th>Name</th><th>Location</th><th>Amenities</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody>
                                  <?php foreach($houses as $house): 
                                      $addr = $pdo->query("SELECT * FROM Addresses WHERE address_id = " . $house['address_id'])->fetch();
                                      $propData = array_merge($house, $addr);
                                  ?>
                                  <tr>
                                    <td><?php echo htmlspecialchars($house['house_name']); ?></td>
                                    <td><?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state']); ?></td>
                                    <td><?php echo htmlspecialchars($house['amenities']); ?></td>
                                    <td><?php echo $house['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'; ?></td>
                                    <td>
                                      <button class="btn btn-sm btn-info" onclick='loadPropertyData(<?php echo htmlspecialchars(json_encode($propData), ENT_QUOTES, 'UTF-8'); ?>)'><i class="fas fa-edit"></i></button>
                                      <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure? This will delete all rooms in this property.');">
                                        <input type="hidden" name="house_id" value="<?php echo $house['house_id']; ?>">
                                        <input type="hidden" name="delete_property" value="1">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                      </form>
                                    </td>
                                  </tr>
                                  <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROOMS SECTION -->
        <div id="rooms" class="content-section">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header"><h3 class="card-title" id="roomFormTitle">Add New Room</h3></div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="card-body">
                                <input type="hidden" name="room_id" id="room_id">
                                <input type="hidden" name="existing_room_image" id="room_existing_image">
                                <div class="form-group"><label>Select Property</label><select name="house_id" id="room_house_id" class="form-control" required><?php foreach($houses as $house): ?><option value="<?php echo $house['house_id']; ?>"><?php echo htmlspecialchars($house['house_name']); ?></option><?php endforeach; ?></select></div>
                                <div class="row"><div class="col-6"><div class="form-group"><input type="text" name="room_number" id="room_number" class="form-control" placeholder="Room #" required></div></div><div class="col-6"><div class="form-group"><input type="number" name="floor_number" id="room_floor" class="form-control" placeholder="Floor" required></div></div></div>
                                <div class="form-group"><label>Type</label><select name="room_type" id="room_type" class="form-control"><option value="single">Single</option><option value="shared">Shared</option><option value="studio">Studio</option></select></div>
                                <div class="form-group"><input type="number" name="price_per_month" id="room_price" class="form-control" placeholder="Price" required></div>
                                <div class="form-group"><input type="number" name="capacity" id="room_capacity" class="form-control" placeholder="Capacity" required></div>
                                <div class="form-group"><input type="text" name="room_amenities" id="room_amenities" class="form-control" placeholder="Amenities"></div>
                                <div class="form-group">
                                    <label>Room Image</label>
                                    <div id="room_image_preview_container" class="mb-2" style="display:none;"><img src="" id="room_image_preview" class="room-thumb"></div>
                                    <div class="input-group"><div class="custom-file"><input type="file" name="room_image" class="custom-file-input" id="roomImageInput" accept="image/*"><label class="custom-file-label" for="roomImageInput">Choose file...</label></div></div>
                                    <small class="text-muted" id="room_image_hint">Upload room image.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="add_room" id="roomSubmitBtn" class="btn btn-success">Add Room</button>
                                <button type="button" class="btn btn-default float-right" onclick="resetRoomForm()"><i class="fas fa-undo"></i> Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">All Rooms</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead><tr><th>Image</th><th>House</th><th>Room</th><th>Type</th><th>Price</th><th>Occupancy</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody>
                                  <?php foreach($allRooms as $room): ?>
                                  <tr>
                                    <td><?php if(!empty($room['room_image'])): ?><img src="<?php echo htmlspecialchars($room['room_image']); ?>" class="room-thumb" alt="Room Image"><?php else: ?><span class="text-muted small">No Image</span><?php endif; ?></td>
                                    <td><?php echo htmlspecialchars($room['house_name']); ?></td>
                                    <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                    <td><?php echo ucfirst($room['room_type'] ?? 'N/A'); ?></td>
                                    <td>₱<?php echo number_format($room['price_per_month'], 2); ?></td>
                                    <td>
                                        <?php $occPercent = ($room['capacity'] > 0) ? ($room['current_occupancy'] / $room['capacity']) * 100 : 0; $color = $occPercent >= 100 ? 'danger' : ($occPercent > 0 ? 'warning' : 'success'); ?>
                                        <div class="progress progress-xs"><div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $occPercent; ?>%"></div></div>
                                        <small class="text-muted"><?php echo $room['current_occupancy']; ?>/<?php echo $room['capacity']; ?></small>
                                    </td>
                                    <td><?php $badge = 'bg-success'; if($room['status'] == 'maintenance') $badge = 'bg-warning'; if($room['status'] == 'occupied') $badge = 'bg-danger'; ?><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($room['status']); ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick='loadRoomData(<?php echo htmlspecialchars(json_encode($room), ENT_QUOTES, 'UTF-8'); ?>)'><i class="fas fa-edit"></i></button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                                            <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                            <input type="hidden" name="delete_room" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                  </tr>
                                  <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RENTALS SECTION -->
        <div id="rentals" class="content-section">
          <div class="row">
            <div class="col-md-4">
              <div class="card card-secondary">
                <div class="card-header"><h3 class="card-title">Create Rental Agreement</h3></div>
                <form method="POST">
                  <div class="card-body">
                    <div class="form-group position-relative">
                        <label>Search Boarder (Name or Email)</label>
                        <input type="text" id="boarder_search" class="form-control" placeholder="Type to search..." autocomplete="off" required>
                        <input type="hidden" name="boarder_email" id="boarder_email" required>
                        <div id="search_results" class="search-results" style="display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label>Select Room</label>
                        <select name="room_id" id="rental_room_select" class="form-control" required onchange="updateRentalAmounts()">
                            <option value="">-- Select Room --</option>
                            <?php foreach($allRooms as $rm): 
                                $isFull = ($rm['current_occupancy'] >= $rm['capacity']);
                                $availSlots = $rm['capacity'] - $rm['current_occupancy'];
                                $disabled = $isFull ? 'disabled' : '';
                                $statusText = $isFull ? "(Occupied/Full)" : "($availSlots slots available)";
                            ?>
                                <option value="<?php echo $rm['room_id']; ?>" data-price="<?php echo $rm['price_per_month']; ?>" data-start-date="<?php echo date('Y-m-d'); ?>" <?php echo $disabled; ?>>
                                    <?php echo htmlspecialchars($rm['house_name'] . ' - Room ' . $rm['room_number'] . ' (₱' . number_format($rm['price_per_month'], 2) . ') ' . $statusText); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row"><div class="col-6"><div class="form-group"><label>Start Date</label><input type="date" name="start_date" id="rental_start_date" class="form-control" required></div></div><div class="col-6"><div class="form-group"><label>End Date</label><input type="date" name="end_date" class="form-control" required></div></div></div>
                    <div class="alert alert-info p-2 mt-2">
                        <small class="font-weight-bold">Initial Payment Breakdown</small>
                        <div class="row mt-1"><div class="col-6"><label>1 Month Advance</label><input type="number" name="advance_amount" id="advance_amount" class="form-control form-control-sm" readonly></div><div class="col-6"><label>1 Month Deposit</label><input type="number" name="deposit_amount" id="deposit_amount" class="form-control form-control-sm" readonly></div></div>
                        <div class="text-right mt-1"><strong>Total Due: ₱<span id="total_initial">0.00</span></strong></div>
                    </div>
                  </div>
                  <div class="card-footer"><button type="submit" name="create_rental" class="btn btn-secondary btn-block">Create Rental</button></div>
                </form>
              </div>
            </div>
            <div class="col-md-8">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Active Rentals</h3></div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-hover">
                    <thead><tr><th>Boarder</th><th>House / Room</th><th>Contract Dates</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                      <?php 
                      $now = time();
                      foreach($rentals as $r): 
                          // Contract Status Logic: Expired if current date is 1 day after end date
                          $endDateTimestamp = strtotime($r['end_date']);
                          $isExpired = ($now > ($endDateTimestamp + 86400)); 
                          
                          // Status Display Logic
                          if ($isExpired) {
                              $statusBadge = '<span class="badge badge-danger">Contract Expired</span>';
                          } else {
                              $statusBadge = '<span class="badge badge-success">Active</span>';
                          }

                          $rJson = json_encode($r);
                      ?>
                      <tr>
                        <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['house_name']); ?> (<?php echo $r['room_number']; ?>)</td>
                        <td><small>Start: <?php echo $r['start_date']; ?><br>End: <?php echo $r['end_date']; ?></small></td>
                        <td><?php echo $statusBadge; ?></td>
                        <td>
                             <button class="btn btn-sm btn-primary" onclick='openRentalModal(<?php echo htmlspecialchars($rJson, ENT_QUOTES, 'UTF-8'); ?>)'><i class="fas fa-cog"></i> Manage</button>
                             <button class="btn btn-sm btn-default" onclick='printRentalAgreement(<?php echo htmlspecialchars($rJson, ENT_QUOTES, 'UTF-8'); ?>)' title="Print Agreement"><i class="fas fa-file-contract"></i> Agreement</button>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PAYMENTS SECTION: FORM LEFT, TABLES RIGHT -->
        <div id="payments" class="content-section">
          <div class="row">
            <!-- LEFT: FORM -->
            <div class="col-md-4">
              <div class="card card-info">
                <div class="card-header"><h3 class="card-title">Record Payment</h3></div>
                <form method="POST">
                  <div class="card-body">
                    <div class="form-group">
                        <label>Select Rental</label>
                        <select name="rental_id" id="pay_rental_select" class="form-control" required onchange="updatePaymentDueDate()">
                            <option value="">-- Select Tenant --</option>
                            <?php foreach($rentals as $r): 
                                // --- NEW LOGIC: 1 MONTH ADVANCE + 1 MONTH DEPOSIT ---
                                // If no payments, the first billable month is Start Date + 2 months.
                                // If payments exist, next due is Last Paid + 1 month.
                                
                                $nextDueDateStr = "";
                                $paid = isset($paidMonthsByRental[$r['rental_id']]) ? $paidMonthsByRental[$r['rental_id']] : [];
                                
                                if (empty($paid)) {
                                    // No payments yet. 
                                    // Month 1 = Advance, Month 2 = Deposit. 
                                    // First Bill = Month 3.
                                    $startDate = new DateTime($r['start_date']);
                                    $startDate->modify('+2 months'); 
                                    $nextDueDateStr = $startDate->format('Y-m-d');
                                } else {
                                    // Payments exist.
                                    rsort($paid); 
                                    $lastPaidKey = $paid[0]; 
                                    $lastPaidDate = new DateTime($lastPaidKey . '-01');
                                    $lastPaidDate->modify('+1 month');
                                    $nextDueDateStr = $lastPaidDate->format('Y-m-d');
                                }
                            ?>
                                <option value="<?php echo $r['rental_id']; ?>" 
                                        data-price="<?php echo $r['room_price']; ?>" 
                                        data-start-date="<?php echo $r['start_date']; ?>"
                                        data-next-due-date="<?php echo $nextDueDateStr; ?>">
                                    <?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name'] . ' - ' . $r['house_name'] . ' (Rm ' . $r['room_number'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if(empty($rentals)): ?><option value="">No active rentals available</option><?php endif; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-8"><div class="form-group"><label>Amount (₱)</label><input type="number" name="amount" id="pay_amount" class="form-control" step="0.01" placeholder="0.00" required></div></div>
                        <div class="col-4"><div class="form-group"><label>Penalty (₱)</label><input type="number" name="penalty" id="pay_penalty" class="form-control" step="0.01" value="0.00" placeholder="0.00"></div></div>
                    </div>
                    <div class="form-group">
                        <label>For Month (Due Date)</label>
                        <input type="date" name="due_date" id="pay_due_date" class="form-control" required>
                        <small class="text-muted">Auto-set based on 1+1 rule.</small>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash / Maya</option>
                            <option value="check">Check</option>
                        </select>
                    </div>
                  </div>
                  <div class="card-footer"><button type="submit" name="add_payment" class="btn btn-info btn-block">Record Payment</button></div>
                </form>
              </div>
            </div>

            <!-- RIGHT: TABLES (PAYABLES + HISTORY) -->
            <div class="col-md-8">
                <!-- 1. CURRENT PAYABLES TABLE (UPDATED LOGIC FOR BACKLOG) -->
                <div class="card card-outline card-success mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Current Payables (Unpaid Months)</h3>
                        <div class="card-tools">
                            <span class="badge badge-info">All Unpaid</span>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Boarder</th>
                                    <th>Room</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $currentDate = new DateTime();
                                foreach($rentals as $rental): 
                                    // Get paid months for this rental
                                    $paid = isset($paidMonthsByRental[$rental['rental_id']]) ? $paidMonthsByRental[$rental['rental_id']] : [];
                                    
                                    // --- LOGIC UPDATE: Skip first 2 months (Advance & Deposit) ---
                                    // Generate expected months from Start Date to Current Date
                                    $start = new DateTime($rental['start_date']);
                                    $end = (clone $currentDate)->modify('+1 day'); 
                                    
                                    $interval = DateInterval::createFromDateString('1 month');
                                    $period = new DatePeriod($start, $interval, $end);

                                    $hasUnpaid = false;

                                    foreach ($period as $dt) {
                                        $monthKey = $dt->format('Y-m');
                                        
                                        // Check if this month is paid
                                        if (!in_array($monthKey, $paid)) {
                                            // IMPORTANT: Do not show as unpaid if it's the 1st or 2nd month of contract
                                            // Logic: If month index is 0 or 1 relative to start date, ignore.
                                            $diff = $start->diff($dt);
                                            $monthsFromStart = ($diff->y * 12) + $diff->m;
                                            
                                            // Only show payable if month index >= 2 (0=Jan, 1=Feb, 2=Mar...)
                                            // Actually, we just need to ensure the contract has covered the pre-paid months.
                                            // If we are in March (index 2), we should show it as unpaid if not paid.
                                            
                                            if ($monthsFromStart >= 2) {
                                                $hasUnpaid = true;
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($rental['house_name'] . ' - ' . $rental['room_number']); ?></td>
                                                    <td><?php echo $dt->format('F Y'); ?></td>
                                                    <td>₱<?php echo number_format($rental['room_price'], 2); ?></td>
                                                    <td><span class="badge badge-danger">Unpaid</span></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                                <?php endforeach; ?>
                                <?php if(empty($rentals)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No active rentals.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. PAYMENT HISTORY TABLE -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payment History</h3>
                        <div class="float-right">
                            <select id="paymentFilter" class="form-control form-control-sm" style="width: 200px;" onchange="filterPayments()">
                                <option value="all">Filter by Tenant (All)</option>
                                <?php 
                                $uniqueTenants = []; 
                                foreach($payments as $p) { 
                                    if(!isset($uniqueTenants[$p['tenant_name']])) { 
                                        echo '<option value="'.htmlspecialchars($p['tenant_name']).'">'.htmlspecialchars($p['tenant_name']).'</option>'; 
                                        $uniqueTenants[$p['tenant_name']] = true; 
                                    } 
                                } 
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover" id="paymentTable">
                            <thead><tr><th>Tenant</th><th>Property / Room</th><th>Amount</th><th>Paid Date</th><th>Penalty</th><th>Method</th><th>Proof</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach($payments as $p): ?>
                                <tr data-tenant-name="<?php echo htmlspecialchars($p['tenant_name']); ?>">
                                    <td><?php echo htmlspecialchars($p['tenant_name']); ?></td>
                                    <td><?php echo htmlspecialchars($p['house_name']); ?> <br><small class="text-muted">Room <?php echo htmlspecialchars($p['room_number']); ?></small></td>
                                    <td>₱<?php echo number_format($p['amount'], 2); ?></td>
                                    <td><?php echo $p['payment_date'] ? date('M d, Y', strtotime($p['payment_date'])) : '-'; ?></td>
                                    <td class="text-danger"><?php echo $p['penalty_fee'] > 0 ? '₱'.number_format($p['penalty_fee'],2) : '₱0.00'; ?></td>
                                    <td><?php echo ucfirst($p['payment_method']); ?></td>
                                    <td>
                                        <?php if(!empty($p['proof_image_url'])): ?>
                                            <a href="uploads/proof/<?php echo htmlspecialchars($p['proof_image_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View Proof">
                                                <i class="fas fa-image"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No Proof</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php $status = strtolower($p['status']); $pStatus = 'secondary'; if($status == 'paid') $pStatus = 'success'; if($status == 'pending') $pStatus = 'warning'; if($status == 'failed' || $status == 'overdue') $pStatus = 'danger'; ?><span class="badge badge-<?php echo $pStatus; ?>"><?php echo ucfirst($status); ?></span></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary" onclick='openPaymentModal(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)' title="Manage"><i class="fas fa-eye"></i></button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick='viewReceipt(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)' title="Print Receipt"><i class="fas fa-print"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($payments)): ?><tr><td colspan="9" class="text-center">No payments found.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
          </div>
        </div>

        <!-- MAINTENANCE SECTION -->
        <div id="maintenance" class="content-section">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Maintenance Requests</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead><tr><th>Property</th><th>Category</th><th>Priority</th><th>Description</th><th>Reported By</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach($maintenanceRequests as $mr): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mr['house_name']); ?><br><small class="text-muted">Room <?php echo htmlspecialchars($mr['room_number']); ?></small></td>
                                <td><?php echo ucfirst($mr['category']); ?></td>
                                <td><?php $pClass = 'priority-low'; if(strtolower($mr['priority']) == 'medium') $pClass = 'priority-med'; if(strtolower($mr['priority']) == 'high') $pClass = 'priority-high'; ?><span class="<?php echo $pClass; ?>"><?php echo ucfirst($mr['priority']); ?></span></td>
                                <td><?php echo htmlspecialchars(substr($mr['description'], 0, 50)) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($mr['reporter_name'] ?? 'N/A'); ?></td>
                                <td><span class="badge badge-<?php echo $mr['status'] == 'resolved' ? 'success' : 'warning'; ?>"><?php echo ucfirst($mr['status']); ?></span></td>
                                <td><button class="btn btn-sm btn-info" onclick='openMaintenanceModal(<?php echo htmlspecialchars(json_encode($mr), ENT_QUOTES, 'UTF-8'); ?>)'><i class="fas fa-tools"></i> Manage</button></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($maintenanceRequests)): ?><tr><td colspan="7" class="text-center">No maintenance requests.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ANNOUNCEMENTS SECTION -->
        <div id="announcements" class="content-section">
            <div class="row">
                <div class="col-md-5">
                    <div class="card card-warning">
                        <div class="card-header"><h3 class="card-title" id="annFormTitle">Post Announcement</h3></div>
                        <form method="POST">
                            <div class="card-body">
                                <input type="hidden" name="announcement_id" id="ann_id">
                                <div class="form-group"><label>Target Property</label><select name="house_id" id="ann_house_id" class="form-control" required><?php foreach($houses as $house): ?><option value="<?php echo $house['house_id']; ?>"><?php echo htmlspecialchars($house['house_name']); ?></option><?php endforeach; ?></select></div>
                                <div class="form-group"><input type="text" name="title" id="ann_title" class="form-control" placeholder="Title" required></div>
                                <div class="form-group"><textarea name="content" id="ann_content" class="form-control" rows="6" placeholder="Message content..." required></textarea></div>
                                <div class="form-check"><input type="checkbox" name="is_pinned" id="ann_pin" class="form-check-input"><label class="form-check-label" for="ann_pin">Pin Announcement</label></div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="add_announcement" id="annSubmitBtn" class="btn btn-warning">Post Announcement</button>
                                <button type="button" class="btn btn-default float-right" onclick="resetAnnouncementForm()"><i class="fas fa-undo"></i> Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Recent Announcements</h3></div>
                        <div class="card-body">
                            <?php if(empty($announcements)): ?><p class="text-muted">No announcements posted yet.</p><?php else: ?>
                                <?php foreach($announcements as $ann): ?>
                                    <div class="callout callout-info">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5><?php echo htmlspecialchars($ann['title']); ?> <?php echo $ann['is_pinned'] ? '<i class="fas fa-thumbtack text-danger"></i>' : ''; ?></h5>
                                            <div class="btn-group">
                                                <button class="btn btn-xs btn-default" onclick='loadAnnouncementData(<?php echo htmlspecialchars(json_encode($ann), ENT_QUOTES, 'UTF-8'); ?>)'><i class="fas fa-edit"></i></button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this announcement?');">
                                                    <input type="hidden" name="announcement_id" value="<?php echo $ann['announcement_id']; ?>">
                                                    <input type="hidden" name="delete_announcement" value="1">
                                                    <button type="submit" class="btn btn-xs btn-default text-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                        <p><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                                        <small class="text-muted">Posted: <?php echo $ann['created_at']; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REPORTS SECTION -->
        <div id="reports" class="content-section">
            <div class="row">
                <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3>₱<?php echo number_format($totalMonthlyRevenue, 0); ?></h3><p>Est. Monthly Revenue</p></div><div class="icon"><i class="fas fa-money-bill-wave"></i></div></div></div>
                <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo count($financialData); ?></h3><p>Paying Tenants</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
                <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo count($tenantReportData); ?></h3><p>Active Payers</p></div><div class="icon"><i class="fas fa-chart-pie"></i></div></div></div>
                <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?php echo count($houses); ?></h3><p>Total Properties</p></div><div class="icon"><i class="fas fa-building"></i></div></div></div>
            </div>
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Active Leases</h3></div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-striped">
                                <thead><tr><th>House / Room</th><th>Tenant</th><th>Start Date</th><th class="text-right">Rate</th></tr></thead>
                                <tbody>
                                    <?php foreach($financialData as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['house_name']); ?> - <?php echo htmlspecialchars($row['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['boarder_name']); ?></td>
                                        <td><?php echo $row['start_date']; ?></td>
                                        <td class="text-right">₱<?php echo number_format($row['price_per_month'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Occupancy by Property</h3></div>
                        <div class="card-body">
                            <?php foreach($occupancyData as $occ): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1"><span><?php echo htmlspecialchars($occ['house_name']); ?></span><span class="badge <?php echo $occ['percent'] == 100 ? 'badge-danger' : 'badge-success'; ?>"><?php echo $occ['percent']; ?>%</span></div>
                                <div class="progress progress-sm"><div class="progress-bar bg-<?php echo $occ['percent'] > 90 ? 'danger' : 'primary'; ?>" style="width: <?php echo $occ['percent']; ?>%"></div></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header"><h3 class="card-title">Total Amount Received Per Tenant</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-valign-middle">
                                    <thead><tr><th>Tenant Name</th><th>Total Paid</th><th class="text-center">Transactions</th></tr></thead>
                                    <tbody>
                                        <?php foreach($tenantReportData as $tr): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tr['tenant_name']); ?></td>
                                            <td class="text-success font-weight-bold">₱<?php echo number_format($tr['total_paid'], 2); ?></td>
                                            <td class="text-center"><span class="badge badge-info"><?php echo $tr['payment_count']; ?> payments</span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      </div></section>
  </div>

<footer class="main-footer no-print"><strong>Copyright &copy; <?php echo date("Y"); ?> Dorm Finder.</strong></footer>
</div>

<!-- MODALS -->
<!-- Payment Status Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Payment Details & Status</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
      <form method="POST">
        <div class="modal-body">
            <input type="hidden" name="payment_id" id="view_payment_id">
            <ul class="list-group mb-3">
                <li class="list-group-item"><strong>Amount:</strong> <span id="view_amount"></span></li>
                <li class="list-group-item"><strong>Method:</strong> <span id="view_method"></span></li>
                <li class="list-group-item"><strong>Date Paid:</strong> <span id="view_date"></span></li>
                <li class="list-group-item"><strong>Penalty Fee:</strong> <span id="view_penalty"></span></li>
            </ul>
            <div class="form-group mb-3">
                <label><strong>Proof of Payment</strong></label>
                <div id="view_proof_container" class="text-center p-2 bg-light border rounded" style="display:none;">
                    <a id="view_proof_link" href="#" target="_blank">
                        <img id="view_proof_image" src="" alt="Proof" style="max-height: 200px; border: 1px solid #ccc;">
                    </a>
                </div>
                <div id="view_proof_none" class="text-muted small">No proof uploaded.</div>
            </div>
            <div class="form-group">
                <label><strong>Update Status</strong></label>
                <select name="status" id="update_status_select" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
      </div>
      <div class="modal-footer">
          <button type="submit" name="update_payment_status" class="btn btn-primary">Update Status</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Rental Status Modal -->
<div class="modal fade" id="rentalStatusModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Manage Rental Status</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
      <form method="POST">
        <div class="modal-body">
            <input type="hidden" name="update_rental" value="1">
            <input type="hidden" name="rental_id" id="manage_rental_id">
            <div class="alert alert-info">
                <strong>Tenant:</strong> <span id="manage_rental_tenant"></span><br>
                <strong>Room:</strong> <span id="manage_rental_room"></span>
            </div>
            <div class="form-group">
                <label>Update Status</label>
                <select name="status" id="manage_rental_status" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="terminated">Terminated</option>
                    <option value="evicted">Evicted</option>
                </select>
            </div>
            <div class="form-group">
                <label>Update Contract End Date</label>
                <input type="date" name="end_date" id="manage_rental_end_date" class="form-control">
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Maintenance Request Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Manage Maintenance</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
      <form method="POST">
        <div class="modal-body">
            <input type="hidden" name="request_id" id="maint_req_id">
            <div class="form-group"><label>Issue Description</label><textarea class="form-control" rows="3" id="maint_desc" readonly></textarea></div>
            <div class="row">
                <div class="col-6"><div class="form-group"><label>Category</label><input type="text" class="form-control" id="maint_category" readonly></div></div>
                <div class="col-6"><div class="form-group"><label>Priority</label><input type="text" class="form-control" id="maint_priority" readonly></div></div>
            </div>
            <div class="form-group">
                <label>Update Status</label>
                <select name="status" class="form-control" required>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="update_maintenance" class="btn btn-primary">Save Update</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Rental Agreement Document Modal -->
<div class="modal fade" id="rentalAgreementModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header no-print"><h5 class="modal-title">Rental Agreement</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <div class="document-paper agreement-body">
            <div class="agreement-title">BOARDING HOUSE LEASE CONTRACT</div>
            <p><strong>THIS LEASE CONTRACT</strong> made and entered into this <span id="ag_date_day"></span> day of <span id="ag_date_month"></span>, <span id="ag_date_year"></span>, by and between:</p>
            <p><strong>THE LESSOR (Landlord):</strong><br><?php echo htmlspecialchars(($_SESSION['last_name'] ?? 'User') . ', ' . ($_SESSION['first_name'] ?? '')); ?></p>
            <p><strong>THE LESSEE (Tenant):</strong><br><span id="ag_tenant_name"></span></p>
            <div class="agreement-section">
                <h4>1. Property</h4>
                <p>The Lessor agrees to rent to the Lessee, and the Lessee agrees to rent from the Lessor, the premises located at:</p>
                <p style="text-align:center; font-weight:bold; border:1px solid #ccc; padding:10px;">
                    <span id="ag_house_name"></span><br>
                    <span id="ag_address"></span><br>
                    Room Number: <span id="ag_room_number"></span>
                </p>
            </div>
            <div class="agreement-section"><h4>2. Term</h4><p>The term of this lease shall commence on <strong><span id="ag_start_date"></span></strong> and end on <strong><span id="ag_end_date"></span></strong>.</p></div>
            
            <div class="agreement-section">
                <h4>3. Payment Terms</h4>
                <p><strong>Initial Payment:</strong> Upon signing, the Lessee has paid an amount equivalent to <strong>Two (2) Months Rent</strong>.</p>
                <ul>
                    <li><strong>1 Month Advance:</strong> Covers the <strong>First Month</strong> of occupancy.</li>
                    <li><strong>1 Month Deposit:</strong> Covers the <strong>Second Month</strong> of occupancy.</li>
                </ul>
                <p><strong>Subsequent Payments:</strong> Monthly rental payments shall be due starting the <strong>Third Month</strong> of the contract.</p>
                <p><strong>Final Month:</strong> The "1 Month Advance" paid upon signing shall be applied to the <strong>Final Month</strong> of this lease term. Therefore, no payment shall be required for the final month listed in the Term section above.</p>
                <p><strong>Monthly Rent Amount:</strong> <strong>₱<span id="ag_rent_amount"></span></strong>.</p>
            </div>
            
            <div style="margin-top: 60px; display: flex; justify-content: space-between;">
                <div style="width: 45%;"><div class="signature-line"></div><div class="signature-label">LESSOR (Landlord)</div></div>
                <div style="width: 45%;"><div class="signature-line"></div><div class="signature-label">LESSEE (Tenant)</div></div>
            </div>
        </div>
      </div>
      <div class="modal-footer no-print"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary" onclick="triggerAgreementPrint()"><i class="fas fa-print"></i> Print Agreement</button></div>
    </div>
  </div>
</div>

<!-- Receipt Document Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header no-print"><h5 class="modal-title">Official Receipt</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <div class="receipt-box document-paper">
            <div class="receipt-header">
                <h3>OFFICIAL RECEIPT</h3>
                
                <small>Date: <span id="rec_date"></span></small>
            </div>
            <div class="receipt-body">
                <!-- Boarder Name Added -->
                <div class="receipt-row"><span style="font-weight:bold; color:#555;">BOARDER NAME:</span><strong id="rec_boarder_name"></strong></div>
                <div class="receipt-row"><span>PROPERTY / ROOM:</span><strong id="rec_property"></strong></div>
                <div class="receipt-row"><span>PAYMENT METHOD:</span><span id="rec_method" style="text-transform: capitalize;"></span></div>
                <div class="receipt-row"><span>FOR PERIOD:</span><span id="rec_period"></span></div>
                <div class="receipt-row"><span>PENALTY:</span><span id="rec_penalty_display"></span></div>
                
                <div class="receipt-total"><span>TOTAL AMOUNT:</span><span id="rec_amount"></span></div>
                
                <!-- Next Due Date Added -->
                <div style="margin-top: 15px; text-align: right; border-top: 1px dotted #ccc; padding-top: 10px;">
                    <small>Next Due Date:</small><br>
                    <strong style="font-size: 1.1em;" id="rec_next_due"></strong>
                </div>

                <!-- Signature Removed as requested -->
            </div>
        </div>
      </div>
      <div class="modal-footer no-print"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary" onclick="triggerReceiptPrint()"><i class="fas fa-print"></i> Print Receipt</button></div>
    </div>
  </div>
</div>

<div class="toast-container no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
  <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
    <div class="toast-header"><i class="fas fa-bell mr-2" id="toastIcon"></i><strong class="mr-auto" id="toastTitle">Notification</strong><small>Just now</small><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
    <div class="toast-body" id="toastMessage">Message goes here.</div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/admin-lite.min.js"></script>

<script>
  let currentRentalData = null;

  function switchTab(tabId, element) {
    document.querySelectorAll('.content-section').forEach(function(section) { section.classList.remove('active'); });
    document.getElementById(tabId).classList.add('active');
    document.querySelectorAll('.nav-sidebar .nav-link').forEach(function(link) { link.classList.remove('active'); });
    if(element) element.classList.add('active');
    const titleMap = { 'dashboard': 'Dashboard', 'properties': 'Manage Properties', 'rooms': 'Manage Rooms', 'rentals': 'Manage Rentals', 'payments': 'Payments', 'maintenance': 'Maintenance Requests', 'announcements': 'Announcements', 'reports': 'Financial & Stats' };
    document.getElementById('page-title').innerText = titleMap[tabId];
    localStorage.setItem('activeTab', tabId);
  }

  // --- FORM LOGIC HELPERS ---
  function loadPropertyData(data) {
      $('#prop_house_id').val(data.house_id);
      $('#prop_address_id').val(data.address_id);
      $('#prop_street_number').val(data.street_number);
      $('#prop_street_name').val(data.street_name);
      $('#prop_city').val(data.city);
      $('#prop_state').val(data.state);
      $('#prop_postal_code').val(data.postal_code);
      $('#prop_country').val(data.country);
      $('#prop_house_name').val(data.house_name);
      $('#prop_description').val(data.description);
      $('#prop_amenities').val(data.amenities);
      $('#propFormTitle').text('Edit Property');
      $('#propSubmitBtn').attr('name', 'edit_property').text('Save Changes').removeClass('btn-primary').addClass('btn-warning');
  }
  function resetPropertyForm() {
      $('#prop_house_id').closest('form')[0].reset();
      $('#prop_house_id, #prop_address_id').val('');
      $('#propFormTitle').text('Add New Property');
      $('#propSubmitBtn').attr('name', 'add_property').text('Add Property').removeClass('btn-warning').addClass('btn-primary');
  }

  function loadRoomData(data) {
      $('#room_id').val(data.room_id);
      $('#room_house_id').val(data.house_id);
      $('#room_number').val(data.room_number);
      $('#room_floor').val(data.floor_number);
      $('#room_type').val(data.room_type);
      $('#room_price').val(data.price_per_month);
      $('#room_capacity').val(data.capacity);
      $('#room_amenities').val(data.room_amenities);
      if(data.room_image) {
          $('#room_existing_image').val(data.room_image);
          $('#room_image_preview').attr('src', data.room_image);
          $('#room_image_preview_container').show();
          $('#room_image_hint').text('Leave empty to keep current image.');
      }
      $('#roomFormTitle').text('Edit Room');
      $('#roomSubmitBtn').attr('name', 'edit_room').text('Update Room').removeClass('btn-success').addClass('btn-warning');
  }
  function resetRoomForm() {
      $('#room_id').closest('form')[0].reset();
      $('#room_id, #room_existing_image').val('');
      $('#room_image_preview_container').hide();
      $('#roomImageInput').val('');
      $('#room_image_hint').text('Upload room image.');
      $('#roomFormTitle').text('Add New Room');
      $('#roomSubmitBtn').attr('name', 'add_room').text('Add Room').removeClass('btn-warning').addClass('btn-success');
  }

  function loadAnnouncementData(data) {
      $('#ann_id').val(data.announcement_id);
      $('#ann_house_id').val(data.house_id);
      $('#ann_title').val(data.title);
      $('#ann_content').val(data.content);
      $('#ann_pin').prop('checked', data.is_pinned == 1);
      $('#annFormTitle').text('Edit Announcement');
      $('#annSubmitBtn').attr('name', 'edit_announcement').text('Save Changes').removeClass('btn-warning').addClass('btn-info');
  }
  function resetAnnouncementForm() {
      $('#ann_id').closest('form')[0].reset();
      $('#ann_id').val('');
      $('#annFormTitle').text('Post Announcement');
      $('#annSubmitBtn').attr('name', 'add_announcement').text('Post Announcement').removeClass('btn-info').addClass('btn-warning');
  }

  // --- PAYMENT LOGIC UPDATED ---
  // Now uses data-next-due-date if available, else falls back to start date
  function updatePaymentDueDate() {
      const select = document.getElementById('pay_rental_select');
      const amountInput = document.getElementById('pay_amount');
      const dueDateInput = document.getElementById('pay_due_date');
      
      if (select.selectedIndex > 0) {
          const price = select.options[select.selectedIndex].getAttribute('data-price');
          const nextDueDate = select.options[select.selectedIndex].getAttribute('data-next-due-date');
          
          if(price) {
              amountInput.value = price;
              
              if(nextDueDate) {
                  dueDateInput.value = nextDueDate;
              } else {
                  dueDateInput.valueAsDate = new Date();
              }
          }
      } else {
          amountInput.value = '';
          dueDateInput.value = '';
      }
  }

  function filterPayments() {
      const filterValue = document.getElementById('paymentFilter').value.toLowerCase();
      const table = document.getElementById('paymentTable');
      const rows = table.getElementsByTagName('tr');
      for (let i = 1; i < rows.length; i++) {
          const tenantName = rows[i].getAttribute('data-tenant-name').toLowerCase();
          if (filterValue === 'all' || tenantName === filterValue) {
              rows[i].style.display = "";
          } else {
              rows[i].style.display = "none";
          }
      }
  }

  // --- MODALS ---
  function openPaymentModal(data) {
      document.getElementById('view_payment_id').value = data.payment_id;
      document.getElementById('view_amount').innerText = '₱' + parseFloat(data.amount).toFixed(2);
      document.getElementById('view_method').innerText = data.payment_method;
      document.getElementById('view_date').innerText = data.payment_date || 'Not yet paid';
      document.getElementById('view_penalty').innerText = '₱' + parseFloat(data.penalty_fee || 0).toFixed(2);
      document.getElementById('update_status_select').value = data.status.toLowerCase();
      
      const proofUrl = data.proof_image_url;
      const proofContainer = document.getElementById('view_proof_container');
      const proofNone = document.getElementById('view_proof_none');
      const proofImg = document.getElementById('view_proof_image');
      const proofLink = document.getElementById('view_proof_link');

      if (proofUrl) {
          const fullPath = 'uploads/proof/' + proofUrl;
          proofImg.src = fullPath;
          proofLink.href = fullPath;
          proofContainer.style.display = 'block';
          proofNone.style.display = 'none';
      } else {
          proofContainer.style.display = 'none';
          proofNone.style.display = 'block';
      }
      $('#paymentModal').modal('show');
  }

  function viewReceipt(data) {
      const modal = $('#receiptModal');
      $('#rec_date').text(data.payment_date ? new Date(data.payment_date).toLocaleDateString() : new Date().toLocaleDateString());
      
      // Added Boarder Name
      $('#rec_boarder_name').text(data.tenant_name || 'N/A');
      
      $('#rec_property').text((data.house_name || '') + ' - Room ' + (data.room_number || ''));
      $('#rec_method').text(data.payment_method);
      const dueDate = data.due_date ? new Date(data.due_date) : new Date();
      $('#rec_period').text(dueDate.toLocaleString('default', { month: 'long', year: 'numeric' }));
      const penalty = parseFloat(data.penalty_fee || 0);
      $('#rec_penalty_display').text('₱' + penalty.toFixed(2));
      $('#rec_amount').text('₱' + parseFloat(data.amount).toFixed(2));
      
      // Calculate and Show Next Due Date
      const nextDue = new Date(dueDate);
      nextDue.setMonth(nextDue.getMonth() + 1);
      $('#rec_next_due').text(nextDue.toLocaleDateString());
      
      modal.modal('show');
  }

  function openRentalModal(data) {
      currentRentalData = data;
      document.getElementById('manage_rental_id').value = data.rental_id;
      document.getElementById('manage_rental_tenant').innerText = data.first_name + ' ' + data.last_name;
      document.getElementById('manage_rental_room').innerText = data.house_name + ' - Room ' + data.room_number;
      document.getElementById('manage_rental_status').value = data.status;
      document.getElementById('manage_rental_end_date').value = data.end_date;
      $('#rentalStatusModal').modal('show');
  }

  function printRentalAgreement(data) {
      currentRentalData = data;
      const d = new Date(data.start_date);
      document.getElementById('ag_date_day').innerText = d.getDate();
      const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
      document.getElementById('ag_date_month').innerText = months[d.getMonth()];
      document.getElementById('ag_date_year').innerText = d.getFullYear();
      document.getElementById('ag_tenant_name').innerText = data.last_name + ', ' + data.first_name;
      document.getElementById('ag_house_name').innerText = data.house_name;
      document.getElementById('ag_address').innerText = `${data.street_number} ${data.street_name}, ${data.city}, ${data.state} ${data.postal_code}`;
      document.getElementById('ag_room_number').innerText = data.room_number;
      document.getElementById('ag_start_date').innerText = data.start_date;
      document.getElementById('ag_end_date').innerText = data.end_date;
      const price = parseFloat(data.room_price).toLocaleString('en-PH', { minimumFractionDigits: 2 });
      document.getElementById('ag_rent_amount').innerText = price;
      $('#rentalAgreementModal').modal('show');
  }

  function openMaintenanceModal(data) {
      document.getElementById('maint_req_id').value = data.request_id;
      document.getElementById('maint_desc').value = data.description;
      document.getElementById('maint_category').value = data.category;
      document.getElementById('maint_priority').value = data.priority;
      const select = document.querySelector('#maintenanceModal select[name="status"]');
      select.value = data.status;
      $('#maintenanceModal').modal('show');
  }

  function triggerAgreementPrint() {
      document.body.classList.add('printing-agreement');
      window.print();
      setTimeout(function() { document.body.classList.remove('printing-agreement'); }, 1000);
  }
  function triggerReceiptPrint() {
      document.body.classList.add('printing-receipt');
      window.print();
      setTimeout(function() { document.body.classList.remove('printing-receipt'); }, 1000);
  }

  let searchTimeout;
  $('#boarder_search').on('keyup', function() {
      clearTimeout(searchTimeout);
      const query = $(this).val();
      const resultsBox = $('#search_results');
      if (query.length < 2) { resultsBox.hide(); return; }
      searchTimeout = setTimeout(function() {
          $.ajax({
              url: 'landlord.php?action=search_users', method: 'GET', data: { q: query }, dataType: 'json',
              success: function(data) {
                  resultsBox.empty();
                  if (data.length > 0) {
                      data.forEach(user => {
                          resultsBox.append(`<div class="search-item" onclick="selectUser('${user.email}', '${user.first_name} ${user.last_name}')"><strong>${user.last_name}, ${user.first_name}</strong><br><span class="search-email">${user.email}</span></div>`);
                      });
                      resultsBox.show();
                  } else { resultsBox.hide(); }
              },
              error: function() { resultsBox.hide(); }
          });
      }, 300);
  });

  function selectUser(email, name) {
      $('#boarder_email').val(email);
      $('#boarder_search').val(name);
      $('#search_results').hide();
  }

  $(document).on('click', function(e) {
      if (!$(e.target).closest('.position-relative').length) { $('#search_results').hide(); }
  });
  
  function updateRentalAmounts() {
      const select = document.getElementById('rental_room_select');
      const price = select.options[select.selectedIndex].getAttribute('data-price');
      if (price) {
          document.getElementById('advance_amount').value = price;
          document.getElementById('deposit_amount').value = price;
          document.getElementById('total_initial').innerText = parseFloat(price) + parseFloat(price);
      } else {
          document.getElementById('advance_amount').value = 0;
          document.getElementById('deposit_amount').value = 0;
          document.getElementById('total_initial').innerText = "0.00";
      }
  }

  function showToast(message, type) {
    const toast = $('#liveToast');
    const title = $('#toastTitle');
    const icon = $('#toastIcon');
    const body = $('#toastMessage');
    $('.toast-header').removeClass('bg-success text-white bg-danger text-white bg-warning text-dark bg-primary text-white');
    body.text(message);
    if (type === 'success') {
        title.text('Success');
        icon.removeClass('fa-exclamation-circle fa-info-circle').addClass('fa-check-circle');
        $('.toast-header').addClass('bg-success text-white');
    } else if (type === 'danger') {
        title.text('Error');
        icon.removeClass('fa-check-circle fa-info-circle').addClass('fa-exclamation-circle');
        $('.toast-header').addClass('bg-danger text-white');
    } else {
        title.text('Info');
        icon.removeClass('fa-check-circle fa-exclamation-circle').addClass('fa-info-circle');
        $('.toast-header').addClass('bg-primary text-white');
    }
    toast.toast('show');
  }

  <?php if($receiptData): ?>
  $(document).ready(function() {
      const data = <?php echo json_encode($receiptData); ?>;
      $('#rec_date').text(new Date().toLocaleDateString());
      const nameParts = (data.tenant_name || "").split(' ');
      if(nameParts.length > 1) { const last = nameParts.pop(); const first = nameParts.join(' '); $('#rec_name').text(last + ', ' + first); } else { $('#rec_name').text(data.tenant_name || 'N/A'); }
      $('#rec_boarder_name').text(data.tenant_name || 'N/A');
      $('#rec_property').text(data.house_name + ' - Room ' + data.room_number);
      $('#rec_method').text(data.payment_method);
      const dueDate = data.due_date ? new Date(data.due_date) : new Date();
      $('#rec_period').text(dueDate.toLocaleString('default', { month: 'long', year: 'numeric' }));
      const penalty = parseFloat(data.penalty_fee || 0);
      $('#rec_penalty_display').text('₱' + penalty.toFixed(2));
      $('#rec_amount').text('₱' + parseFloat(data.amount).toFixed(2));
      
      // Auto calculate next due for receipt popup
      const nextDue = new Date(dueDate);
      nextDue.setMonth(nextDue.getMonth() + 1);
      $('#rec_next_due').text(nextDue.toLocaleDateString());
      
      $('#receiptModal').modal('show');
  });
  <?php endif; ?>

  $(document).ready(function() {
      const activeTab = localStorage.getItem('activeTab');
      if (activeTab) {
          const link = $(`a[data-tab-id="${activeTab}"]`)[0];
          if (link) { switchTab(activeTab, link); }
      }
      $('.custom-file-input').on('change', function() {
          var fileName = $(this).val().split('\\').pop();
          $(this).next('.custom-file-label').addClass("selected").html(fileName);
      });
      
      <?php if($msg): ?>
      showToast("<?php echo $msg; ?>", "<?php echo $msgType; ?>");
      // Clear URL parameters so refreshing doesn't show toast again
      if (window.history.replaceState) {
          const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
          window.history.replaceState({path:newUrl},'',newUrl);
      }
      <?php endif; ?>
  });
</script>

</body>
</html>