<?php
session_start();
include("db.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'];

$query = mysqli_query($conn, "SELECT * FROM properties WHERE id='$id' AND user_id='$user_id'");
$data = mysqli_fetch_assoc($query);

if (!$data) die("Unauthorized");

$pictures = mysqli_query($conn, "SELECT * FROM pictures WHERE property_id='$id'");

$message = "";

if (isset($_POST['submit'])) {

    $location = $_POST['location'];
    $price = $_POST['price'];
    $sqft = $_POST['sqft'];
    $type = $_POST['type'];
    $property_type = $_POST['property_type'];
    $rooms = $_POST['rooms'];
    $parking = $_POST['parking'];
    $preferred_tenant = $_POST['preferred_tenant'];
    $possession_date = $_POST['possession_date'];
    $age_of_building = $_POST['age_of_building'];
    $furnish = $_POST['furnish'];

    if (!empty($_FILES['image']['name'])) {
        $oldImage = $data['image'];
        if ($oldImage && file_exists("uploads/".$oldImage)) {
            @unlink("uploads/".$oldImage);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $mainImageName = "property_".$id."_".time().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$mainImageName);
        mysqli_query($conn, "UPDATE properties SET image='$mainImageName' WHERE id='$id'");
    }

    mysqli_query($conn, "
        UPDATE properties SET
        location='$location',
        price='$price',
        sqft='$sqft',
        type='$type',
        property_type='$property_type',
        rooms='$rooms',
        parking='$parking',
        preferred_tenant='$preferred_tenant',
        possession_date='$possession_date',
        age_of_building='$age_of_building',
        furnish='$furnish'
        WHERE id='$id'
    ");

    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $pic_id) {
            $res = mysqli_query($conn, "SELECT file_name FROM pictures WHERE pic_id='$pic_id'");
            $row = mysqli_fetch_assoc($res);
            if ($row && file_exists("uploads/".$row['file_name'])) {
                @unlink("uploads/".$row['file_name']);
            }
            mysqli_query($conn, "DELETE FROM pictures WHERE pic_id='$pic_id'");
        }
    }

    if (!empty($_FILES['media']['name'][0])) {
        foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
            if ($tmp_name == "") continue;
            $ext = strtolower(pathinfo($_FILES['media']['name'][$key], PATHINFO_EXTENSION));
            $newName = "property".$id."_".time()."_".$key.".".$ext;
            move_uploaded_file($tmp_name, "uploads/".$newName);
            mysqli_query($conn,
                "INSERT INTO pictures (property_id, file_name)
                 VALUES ('$id', '$newName')"
            );
        }
    }

    $message = '<div class="success-message"><i class="fas fa-check-circle"></i> Updated successfully!</div>';

    $query = mysqli_query($conn, "SELECT * FROM properties WHERE id='$id'");
    $data = mysqli_fetch_assoc($query);
    $pictures = mysqli_query($conn, "SELECT * FROM pictures WHERE property_id='$id'");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Property</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="edit-property-container">
    <h2><i class="fas fa-edit"></i> Edit Property</h2>
    <?php echo $message; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="edit-form-grid">
            
            <div class="edit-form-column">
                <div class="edit-form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    
                    <div class="edit-form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Location</label>
                        <input type="text" name="location" value="<?php echo $data['location']; ?>" required>
                    </div>
                    
                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label><i class="fas fa-rupee-sign"></i> Price</label>
                            <input type="number" name="price" value="<?php echo $data['price']; ?>" required>
                        </div>
                        
                        <div class="edit-form-group">
                            <label><i class="fas fa-arrows-alt"></i> Area (sq ft)</label>
                            <input type="number" name="sqft" value="<?php echo isset($data['sqft']) ? $data['sqft'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label><i class="fas fa-tag"></i> Type</label>
                            <select name="type" required>
                                <option <?php if($data['type']=="Rent") echo "selected"; ?>>Rent</option>
                                <option <?php if($data['type']=="Sale") echo "selected"; ?>>Sale</option>
                            </select>
                        </div>
                        
                        <div class="edit-form-group">
                            <label><i class="fas fa-building"></i> Property Type</label>
                            <select name="property_type" required>
                                <option <?php if($data['property_type']=="Apartment") echo "selected"; ?>>Apartment</option>
                                <option <?php if($data['property_type']=="Independent House") echo "selected"; ?>>Independent House</option>
                                <option <?php if($data['property_type']=="Villa") echo "selected"; ?>>Villa</option>
                                <option <?php if($data['property_type']=="Plot") echo "selected"; ?>>Plot</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label><i class="fas fa-bed"></i> Rooms</label>
                            <select name="rooms" required>
                                <option <?php if($data['rooms']=="1RK") echo "selected"; ?>>1RK</option>
                                <option <?php if($data['rooms']=="1BHK") echo "selected"; ?>>1BHK</option>
                                <option <?php if($data['rooms']=="2BHK") echo "selected"; ?>>2BHK</option>
                                <option <?php if($data['rooms']=="3BHK") echo "selected"; ?>>3BHK</option>
                                <option <?php if($data['rooms']=="4BHK") echo "selected"; ?>>4BHK</option>
                                <option <?php if($data['rooms']=="4+BHK") echo "selected"; ?>>4+BHK</option>
                            </select>
                        </div>
                        
                        <div class="edit-form-group">
                            <label><i class="fas fa-parking"></i> Parking</label>
                            <select name="parking">
                                <option <?php if($data['parking']=="No") echo "selected"; ?>>No</option>
                                <option <?php if($data['parking']=="2 Wheeler") echo "selected"; ?>>2 Wheeler</option>
                                <option <?php if($data['parking']=="4 Wheeler") echo "selected"; ?>>4 Wheeler</option>
                                <option <?php if($data['parking']=="2 and 4 Wheeler") echo "selected"; ?>>2 and 4 Wheeler</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="edit-form-group">
                        <label><i class="fas fa-couch"></i> Furnishing Status</label>
                        <select name="furnish">
                            <option value="unfurnished" <?php if($data['furnish']=="unfurnished") echo "selected"; ?>>Unfurnished</option>
                            <option value="semi" <?php if($data['furnish']=="semi") echo "selected"; ?>>Semi-Furnished</option>
                            <option value="fully" <?php if($data['furnish']=="fully") echo "selected"; ?>>Fully Furnished</option>
                        </select>
                    </div>
                </div>
                
                <div class="edit-form-section">
                    <h3><i class="fas fa-users"></i> Requirements</h3>
                    
                    <div class="edit-form-group">
                        <label><i class="fas fa-users"></i> Preferred Tenant</label>
                        <select name="preferred_tenant">
                            <option <?php if($data['preferred_tenant']=="Anyone") echo "selected"; ?>>Anyone</option>
                            <option <?php if($data['preferred_tenant']=="Girls") echo "selected"; ?>>Girls</option>
                            <option <?php if($data['preferred_tenant']=="Boys") echo "selected"; ?>>Boys</option>
                            <option <?php if($data['preferred_tenant']=="Family") echo "selected"; ?>>Family</option>
                            <option <?php if($data['preferred_tenant']=="Bachelors") echo "selected"; ?>>Bachelors</option>
                            <option <?php if($data['preferred_tenant']=="Student") echo "selected"; ?>>Student</option>
                        </select>
                    </div>
                    
                    <div class="edit-form-group">
                        <label><i class="fas fa-calendar-alt"></i> Possession Date</label>
                        <select id="possession_option" onchange="handlePossession()">
                            <option value="">Select</option>
                            <option value="Immediate">Immediate</option>
                            <option value="Days">Within __ days</option>
                            <option value="Date">Choose Date</option>
                        </select>
                        <div id="days_input" style="display:none; margin-top: 8px;">
                            <input type="number" id="days" placeholder="Enter days">
                        </div>
                        <div id="date_input" style="display:none; margin-top: 8px;">
                            <input type="date" id="date">
                        </div>
                        <input type="hidden" name="possession_date" id="final_possession" value="<?php echo $data['possession_date']; ?>">
                    </div>
                    
                    <div class="edit-form-group">
                        <label><i class="fas fa-clock"></i> Age of Building</label>
                        <input type="text" name="age_of_building" value="<?php echo $data['age_of_building']; ?>" placeholder="e.g., 2 years, New Construction">
                    </div>
                </div>
            </div>
            
            <div class="edit-form-column">
                <div class="edit-form-section">
                    <h3><i class="fas fa-camera"></i> Main Image</h3>
                    
                    <div class="main-image-section">
                        <div class="main-image-preview">
                            <img src="uploads/<?php echo $data['image']; ?>" alt="Main Image" onclick="openImagePreview('uploads/<?php echo $data['image']; ?>', 'Main Image')">
                            <button type="button" class="change-image-btn" onclick="document.getElementById('mainImageInput').click()">
                                <i class="fas fa-sync-alt"></i> Change Image
                            </button>
                            <input type="file" id="mainImageInput" name="image" accept="image/*" style="display:none;">
                        </div>
                    </div>
                </div>
                
                <div class="edit-form-section">
                    <h3><i class="fas fa-images"></i> Additional Images</h3>
                    
                    <div class="extra-images-section">
                        <div class="extra-images-grid" id="extraImagesGrid">
                            <?php while($pic = mysqli_fetch_assoc($pictures)) { ?>
                                <div class="extra-image-card" data-pic-id="<?php echo $pic['pic_id']; ?>">
                                    <img src="uploads/<?php echo $pic['file_name']; ?>" alt="Property Image" onclick="openImagePreview('uploads/<?php echo $pic['file_name']; ?>', 'Property Image')">
                                    <button type="button" class="remove-image-btn" onclick="markForDelete(<?php echo $pic['pic_id']; ?>, this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div class="remove-checkbox">
                                        <input type="checkbox" name="delete_images[]" value="<?php echo $pic['pic_id']; ?>" id="del_<?php echo $pic['pic_id']; ?>">
                                        <label for="del_<?php echo $pic['pic_id']; ?>">Mark to delete</label>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="add-more-images">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add More</span>
                                <input type="file" id="mediaInput" name="media[]" multiple accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-edit-actions">
            <a href="my_listings.php" class="cancel-btn"><i class="fas fa-times"></i> Cancel</a>
            <button type="submit" name="submit" class="update-btn"><i class="fas fa-save"></i> Update Property</button>
        </div>
    </form>
</div>

<div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview()">
    <span class="close-preview" onclick="closeImagePreview()">&times;</span>
    <img class="preview-image" id="previewImage" src="">
    <div class="preview-caption" id="previewCaption"></div>
</div>

<script>
function handlePossession() {
    let val = document.getElementById("possession_option").value;
    document.getElementById("days_input").style.display = val === "Days" ? "block" : "none";
    document.getElementById("date_input").style.display = val === "Date" ? "block" : "none";
}

window.onload = function() {
    let existing = "<?php echo $data['possession_date']; ?>";
    if (existing === "Immediate") {
        document.getElementById("possession_option").value = "Immediate";
    }
    else if (existing.includes("Within")) {
        document.getElementById("possession_option").value = "Days";
        document.getElementById("days_input").style.display = "block";
        let days = existing.match(/\d+/);
        if(days) document.getElementById("days").value = days[0];
    }
    else if (existing.includes("From")) {
        document.getElementById("possession_option").value = "Date";
        document.getElementById("date_input").style.display = "block";
        let date = existing.replace("From ", "");
        document.getElementById("date").value = date;
    }
};

document.querySelector("form").addEventListener("submit", function(){
    let option = document.getElementById("possession_option").value;
    let val = "";
    if(option === "Immediate") val = "Immediate";
    if(option === "Days") val = "Within " + document.getElementById("days").value + " days";
    if(option === "Date") val = "From " + document.getElementById("date").value;
    document.getElementById("final_possession").value = val;
});

let today = new Date().toISOString().split("T")[0];
if(document.getElementById("date")) {
    document.getElementById("date").setAttribute("min", today);
}

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

function markForDelete(picId, element) {
    var checkbox = document.getElementById('del_' + picId);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        var card = element.closest('.extra-image-card');
        if (checkbox.checked) {
            card.style.opacity = '0.5';
            card.style.border = '2px solid #f44336';
        } else {
            card.style.opacity = '1';
            card.style.border = 'none';
        }
    }
}

document.getElementById('mediaInput').addEventListener('change', function(e) {
    var container = document.getElementById('extraImagesGrid');
    var addBox = document.querySelector('.add-more-images');
    var files = Array.from(e.target.files);
    
    files.forEach(function(file, index) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            var tempId = 'temp_' + Date.now() + '_' + index;
            var newCard = document.createElement('div');
            newCard.className = 'extra-image-card';
            newCard.setAttribute('data-temp', tempId);
            newCard.innerHTML = `
                <img src="${ev.target.result}" alt="New Image" onclick="openImagePreview('${ev.target.result}', 'New Image')">
                <button type="button" class="remove-image-btn" onclick="this.closest('.extra-image-card').remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.insertBefore(newCard, addBox);
        };
        reader.readAsDataURL(file);
    });
    e.target.value = '';
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImagePreview();
    }
});
</script>

</body>
</html>