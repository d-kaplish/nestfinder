<?php
session_start();
include("db.php");
include("admin_sidebar.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get property details
$propertyQuery = mysqli_query($conn, 
    "SELECT p.*, u.email, u.contact 
     FROM properties p 
     JOIN users u ON p.user_id = u.user_id 
     WHERE p.id = $property_id"
);
$property = mysqli_fetch_assoc($propertyQuery);

if (!$property) {
    header("Location: admin_properties.php");
    exit();
}

// Get all images for this property
$imagesQuery = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id = $property_id");
$images = [];
while ($img = mysqli_fetch_assoc($imagesQuery)) {
    $images[] = $img['file_name'];
}

// Update property status
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);
    
    mysqli_query($conn, "UPDATE properties SET status = '$new_status', admin_notes = '$admin_notes' WHERE id = $property_id");
    
    // Log activity
    $admin_id = $_SESSION['user_id'];
    mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, target_type, target_id, details) 
                         VALUES ($admin_id, 'status_changed', 'property', $property_id, 'Status changed to $new_status')");
    
    header("Location: admin_property_view.php?id=$property_id");
    exit();
}

// Update property details
if (isset($_POST['update_details'])) {
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $sqft = mysqli_real_escape_string($conn, $_POST['sqft']); 
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $property_type = mysqli_real_escape_string($conn, $_POST['property_type']);
    $rooms = mysqli_real_escape_string($conn, $_POST['rooms']);
    $parking = mysqli_real_escape_string($conn, $_POST['parking']);
    $preferred_tenant = mysqli_real_escape_string($conn, $_POST['preferred_tenant']);
    $possession_date = mysqli_real_escape_string($conn, $_POST['possession_date']);
    $age_of_building = mysqli_real_escape_string($conn, $_POST['age_of_building']);
    
    mysqli_query($conn, "UPDATE properties SET 
        location = '$location',
        price = '$price',
        sqft = '$sqft', 
        type = '$type',
        property_type = '$property_type',
        rooms = '$rooms',
        parking = '$parking',
        preferred_tenant = '$preferred_tenant',
        possession_date = '$possession_date',
        age_of_building = '$age_of_building'
        furnish = '$furnish'
        WHERE id = $property_id");
    
    header("Location: admin_property_view.php?id=$property_id");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Property Details | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <div class="header-left">
                <a href="admin_properties.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Properties
                </a>
                <h1>Property Details</h1>
            </div>
            <a href="admin_property_delete.php?id=<?php echo $property_id; ?>" class="delete-property-btn" onclick="return confirm('Delete this property permanently?')">
                <i class="fas fa-trash"></i> Delete Property
            </a>
        </div>
        
        <!-- Property Info Cards -->
        <div class="property-info-grid">
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Location</h3>
                </div>
                <p class="info-value"><?php echo $property['location']; ?></p>
            </div>
             <div class="info-card">
        <div class="info-header">
            <i class="fas fa-arrows-alt"></i>
            <h3>Area</h3>
        </div>
        <p class="info-value"><?php echo $property['sqft'] ? $property['sqft'] . ' sq ft' : 'Not Specified'; ?></p>
    </div>
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-rupee-sign"></i>
                    <h3>Price</h3>
                </div>
                <p class="info-value">₹<?php echo indianCurrency($property['price']); ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-tag"></i>
                    <h3>Type</h3>
                </div>
                <p class="info-value"><?php echo $property['type']; ?></p>
            </div>
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-couch"></i>
                    <h3>Furnishing</h3>
                </div>
                <p class="info-value"><?php echo ucfirst($property['furnish']); ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-building"></i>
                    <h3>Property Type</h3>
                </div>
                <p class="info-value"><?php echo $property['property_type']; ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-bed"></i>
                    <h3>BHK</h3>
                </div>
                <p class="info-value"><?php echo $property['rooms']; ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-parking"></i>
                    <h3>Parking</h3>
                </div>
                <p class="info-value"><?php echo $property['parking'] ?: 'Not Specified'; ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-users"></i>
                    <h3>Preferred Tenant</h3>
                </div>
                <p class="info-value"><?php echo $property['preferred_tenant']; ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Possession</h3>
                </div>
                <p class="info-value"><?php echo $property['possession_date']; ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-clock"></i>
                    <h3>Age of Building</h3>
                </div>
                <p class="info-value"><?php echo $property['age_of_building'] ?: 'Not Specified'; ?></p>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Views</h3>
                </div>
                <p class="info-value"><?php echo $property['view_count']; ?> views</p>
            </div>
        </div>
        
        <!-- Owner Information -->
        <div class="owner-section">
            <h3><i class="fas fa-user"></i> Owner Information</h3>
            <div class="owner-details-card">
                <div class="owner-detail">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo $property['email']; ?></span>
                </div>
                <div class="owner-detail">
                    <i class="fas fa-phone"></i>
                    <span><?php echo $property['contact']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Images Gallery -->
        <div class="gallery-section">
            <h3><i class="fas fa-images"></i> Property Images</h3>
            <div class="gallery-grid">
                <div class="gallery-item main-image">
                    <img src="uploads/<?php echo $property['image']; ?>" alt="Main Image" onclick="openImagePreview('uploads/<?php echo $property['image']; ?>', 'Main Image')">
                    <span class="image-label">Main Image</span>
                </div>
                <?php foreach($images as $img): ?>
                    <div class="gallery-item">
                        <img src="uploads/<?php echo $img; ?>" alt="Property Image" onclick="openImagePreview('uploads/<?php echo $img; ?>', 'Property Image')">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

<div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview()">
    <span class="close-preview" onclick="closeImagePreview()">&times;</span>
    <img class="preview-image" id="previewImage" src="">
    <div class="preview-caption" id="previewCaption"></div>
</div>
        
        <!-- Status Update Form -->
        <div class="status-section">
            <h3><i class="fas fa-check-circle"></i> Update Status</h3>
            <form method="POST" action="" class="status-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Current Status:</label>
                        <select name="status" class="status-select">
                            <option value="pending" <?php echo $property['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $property['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $property['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Admin Notes:</label>
                        <textarea name="admin_notes" rows="3" class="notes-input" placeholder="Add notes about this property..."><?php echo $property['admin_notes']; ?></textarea>
                    </div>
                </div>
                
                <button type="submit" name="update_status" class="update-status-btn">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </form>
        </div>
        
        <!-- Edit Details Form -->
        <div class="edit-section">
            <h3><i class="fas fa-edit"></i> Edit Property Details</h3>
            <form method="POST" action="" class="edit-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="<?php echo $property['location']; ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Area (sq ft)</label>
                        <input type="number" name="sqft" value="<?php echo $property['sqft']; ?>" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" name="price" value="<?php echo $property['price']; ?>" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-select">
                            <option <?php echo $property['type'] == 'Rent' ? 'selected' : ''; ?>>Rent</option>
                            <option <?php echo $property['type'] == 'Sale' ? 'selected' : ''; ?>>Sale</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Furnishing Status</label>
                        <select name="furnish" class="form-select">
                            <option value="unfurnished" <?php echo $property['furnish'] == 'unfurnished' ? 'selected' : ''; ?>>Unfurnished</option>
                            <option value="semi" <?php echo $property['furnish'] == 'semi' ? 'selected' : ''; ?>>Semi-Furnished</option>
                            <option value="fully" <?php echo $property['furnish'] == 'fully' ? 'selected' : ''; ?>>Fully Furnished</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Property Type</label>
                        <select name="property_type" class="form-select">
                            <option <?php echo $property['property_type'] == 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option <?php echo $property['property_type'] == 'Independent House' ? 'selected' : ''; ?>>Independent House</option>
                            <option <?php echo $property['property_type'] == 'Villa' ? 'selected' : ''; ?>>Villa</option>
                            <option <?php echo $property['property_type'] == 'Plot' ? 'selected' : ''; ?>>Plot</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Rooms</label>
                        <select name="rooms" class="form-select">
                            <option <?php echo $property['rooms'] == '1RK' ? 'selected' : ''; ?>>1RK</option>
                            <option <?php echo $property['rooms'] == '1BHK' ? 'selected' : ''; ?>>1BHK</option>
                            <option <?php echo $property['rooms'] == '2BHK' ? 'selected' : ''; ?>>2BHK</option>
                            <option <?php echo $property['rooms'] == '3BHK' ? 'selected' : ''; ?>>3BHK</option>
                            <option <?php echo $property['rooms'] == '4BHK' ? 'selected' : ''; ?>>4BHK</option>
                            <option <?php echo $property['rooms'] == '4+BHK' ? 'selected' : ''; ?>>4+BHK</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Parking</label>
                        <select name="parking" class="form-select">
                            <option <?php echo $property['parking'] == 'No' ? 'selected' : ''; ?>>No</option>
                            <option <?php echo $property['parking'] == '2 Wheeler' ? 'selected' : ''; ?>>2 Wheeler</option>
                            <option <?php echo $property['parking'] == '4 Wheeler' ? 'selected' : ''; ?>>4 Wheeler</option>
                            <option <?php echo $property['parking'] == '2 and 4 Wheeler' ? 'selected' : ''; ?>>2 and 4 Wheeler</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Preferred Tenant</label>
                        <select name="preferred_tenant" class="form-select">
                            <option <?php echo $property['preferred_tenant'] == 'Anyone' ? 'selected' : ''; ?>>Anyone</option>
                            <option <?php echo $property['preferred_tenant'] == 'Girls' ? 'selected' : ''; ?>>Girls</option>
                            <option <?php echo $property['preferred_tenant'] == 'Boys' ? 'selected' : ''; ?>>Boys</option>
                            <option <?php echo $property['preferred_tenant'] == 'Family' ? 'selected' : ''; ?>>Family</option>
                            <option <?php echo $property['preferred_tenant'] == 'Bachelors' ? 'selected' : ''; ?>>Bachelors</option>
                            <option <?php echo $property['preferred_tenant'] == 'Student' ? 'selected' : ''; ?>>Student</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Possession Date</label>
                        <input type="text" name="possession_date" value="<?php echo $property['possession_date']; ?>" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Age of Building</label>
                        <input type="text" name="age_of_building" value="<?php echo $property['age_of_building']; ?>" class="form-input">
                    </div>
                </div>
                
                <button type="submit" name="update_details" class="update-details-btn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openImagePreview(imageSrc, caption) {
    var modal = document.getElementById('imagePreviewModal');
    var previewImage = document.getElementById('previewImage');
    var previewCaption = document.getElementById('previewCaption');
    
    modal.classList.add('active');
    previewImage.src = imageSrc;
    previewCaption.innerHTML = caption;
    document.body.style.overflow = 'hidden';
}

function closeImagePreview() {
    var modal = document.getElementById('imagePreviewModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImagePreview();
    }
});
</script>

</body>
</html>