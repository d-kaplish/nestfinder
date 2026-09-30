<?php
session_start();
include("db.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
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

    $mainImageName = "property_" . time() . "_" . rand(1000,9999) . ".jpg";
    move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $mainImageName);

    mysqli_query($conn, "
        INSERT INTO properties 
        (user_id, image, location, price, sqft, type, property_type, rooms, parking, preferred_tenant, possession_date, age_of_building, furnish, status)
        VALUES 
        ('$user_id', '$mainImageName', '$location', '$price', '$sqft', '$type', '$property_type', '$rooms', '$parking', '$preferred_tenant', '$possession_date', '$age_of_building', '$furnish','pending')
    ");

    $property_id = mysqli_insert_id($conn);

    if (!empty($_FILES['media']['name'][0])) {
        $allowed = ['jpg','jpeg','png','gif','bmp','webp','mp4','mov','avi','mkv','webm','flv','wmv'];

        foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
            if ($tmp_name == "") continue;

            $ext = strtolower(pathinfo($_FILES['media']['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) continue;

            $newName = "property".$property_id."_".time()."_".$key.".".$ext;
            move_uploaded_file($tmp_name, "uploads/".$newName);

            mysqli_query($conn,
                "INSERT INTO pictures (property_id, file_name)
                 VALUES ('$property_id', '$newName')"
            );
        }
    }

    $message = '<div class="success-message"><i class="fas fa-check-circle"></i> Property upload request sent. Kindly wait for Admin approval!</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Property</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="upload-container">
    
    <div class="upload-header">
        <i class="fas fa-home"></i>
        <h2>Add New Property</h2>
        <p>List your property and find the perfect tenant or buyer</p>
    </div>

    <?php echo $message; ?>

    <form method="POST" enctype="multipart/form-data" class="upload-form">
        <div class="form-grid">
            
            <div class="form-column">
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Location *</label>
                        <input type="text" name="location" placeholder="e.g., Andheri East, Mumbai" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-rupee-sign"></i> Price *</label>
                            <input type="number" name="price" placeholder="Enter price" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-arrows-alt"></i> Area (sq ft) *</label>
                            <input type="number" name="sqft" placeholder="Enter area in square feet" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Type *</label>
                            <select name="type" required>
                                <option value="">Select Type</option>
                                <option>Rent</option>
                                <option>Sale</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Property Type *</label>
                            <select name="property_type" required>
                                <option value="">Select Property Type</option>
                                <option>Apartment</option>
                                <option>Independent House</option>
                                <option>Villa</option>
                                <option>Plot</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-bed"></i> Rooms *</label>
                            <select name="rooms" required>
                                <option value="">Select Rooms</option>
                                <option>1RK</option>
                                <option>1BHK</option>
                                <option>2BHK</option>
                                <option>3BHK</option>
                                <option>4BHK</option>
                                <option>4+BHK</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-parking"></i> Parking</label>
                            <select name="parking">
                                <option>No</option>
                                <option>2 Wheeler</option>
                                <option>4 Wheeler</option>
                                <option>2 and 4 Wheeler</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-couch"></i> Furnishing Status</label>
                            <select name="furnish">
                                <option value="unfurnished">Unfurnished</option>
                                <option value="semi">Semi-Furnished</option>
                                <option value="fully">Fully Furnished</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-users"></i> Requirements</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Preferred Tenant</label>
                            <select name="preferred_tenant">
                                <option>Anyone</option>
                                <option>Girls</option>
                                <option>Boys</option>
                                <option>Family</option>
                                <option>Bachelors</option>
                                <option>Student</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
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
                            <input type="hidden" name="possession_date" id="final_possession">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Age of Building</label>
                        <input type="text" name="age_of_building" placeholder="e.g., 2 years, New Construction">
                    </div>
                </div>
            </div>
            
            <div class="form-column">
                <div class="form-section">
                    <h3><i class="fas fa-camera"></i> Main Image</h3>
                    <p class="section-note">Upload a clear image of your property (Required)</p>
                    
                    <div class="main-upload-box" id="mainUploadBox">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Click to upload main image</span>
                        <input type="file" id="mainImageInput" name="image" accept="image/*" required>
                    </div>
                    <div id="main-preview-container"></div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Additional Media</h3>
                    <p class="section-note">Add more photos or videos (Optional, multiple files allowed)</p>
                    
                    <div id="preview-container">
                        <div class="upload-box" id="addImageBox">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Media</span>
                            <input type="file" id="mediaInput" name="media[]" multiple accept="image/*,video/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="reset" class="reset-btn" onclick="resetForm()">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
            <button type="submit" name="submit" class="submit-btn">
                <i class="fas fa-check-circle"></i> Publish Property
            </button>
        </div>
        
    </form>
</div>

<div id="imageModal">
    <span class="close-modal"><i class="fas fa-times"></i></span>
    <img id="modalImg">
</div>

<script>
function handlePossession() {
    let val = document.getElementById("possession_option").value;
    document.getElementById("days_input").style.display = val === "Days" ? "block" : "none";
    document.getElementById("date_input").style.display = val === "Date" ? "block" : "none";
}

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

document.getElementById("mainImageInput").addEventListener("change", function(e){
    let file = e.target.files[0];
    if(!file) return;
    let reader = new FileReader();
    reader.onload = function(ev){
        let container = document.getElementById("main-preview-container");
        let uploadBox = document.getElementById("mainUploadBox");
        container.innerHTML = "";
        let box = document.createElement("div");
        box.classList.add("main-preview-box");
        box.innerHTML = `
            <img src="${ev.target.result}">
            <button type="button" class="main-remove-btn"><i class="fas fa-times"></i></button>
            <button type="button" class="main-expand-btn"><i class="fas fa-expand"></i></button>
        `;
        uploadBox.style.display = "none";
        box.querySelector(".main-remove-btn").onclick = () => {
            container.innerHTML = "";
            uploadBox.style.display = "flex";
            document.getElementById("mainImageInput").value = "";
        };
        box.querySelector(".main-expand-btn").onclick = () => {
            document.getElementById("imageModal").style.display = "flex";
            document.getElementById("modalImg").src = ev.target.result;
        };
        container.appendChild(box);
    };
    reader.readAsDataURL(file);
});

let allFiles = [];
document.getElementById("mediaInput").addEventListener("change", function(e){
    let files = Array.from(e.target.files);
    let container = document.getElementById("preview-container");
    let addBox = document.getElementById("addImageBox");

    files.forEach(file => {
        allFiles.push(file);
        let reader = new FileReader();
        reader.onload = function(ev){
            let box = document.createElement("div");
            box.classList.add("preview-box");
            let fileType = file.type.startsWith('video/') ? 'video' : 'image';
            if(fileType === 'video') {
                box.innerHTML = `
                    <video src="${ev.target.result}"></video>
                    <button type="button" class="preview-remove-btn"><i class="fas fa-times"></i></button>
                    <button type="button" class="preview-expand-btn"><i class="fas fa-play"></i></button>
                `;
            } else {
                box.innerHTML = `
                    <img src="${ev.target.result}">
                    <button type="button" class="preview-remove-btn"><i class="fas fa-times"></i></button>
                    <button type="button" class="preview-expand-btn"><i class="fas fa-expand"></i></button>
                `;
            }
            container.insertBefore(box, addBox);

            box.querySelector(".preview-remove-btn").onclick = () => {
                let index = allFiles.indexOf(file);
                allFiles.splice(index, 1);
                box.remove();
            };
            box.querySelector(".preview-expand-btn").onclick = () => {
                if(fileType === 'video') {
                    window.open(ev.target.result, '_blank');
                } else {
                    document.getElementById("imageModal").style.display = "flex";
                    document.getElementById("modalImg").src = ev.target.result;
                }
            };
        };
        reader.readAsDataURL(file);
    });
    e.target.value = "";
});

document.querySelector("form").addEventListener("submit", function(){
    let dt = new DataTransfer();
    allFiles.forEach(file => dt.items.add(file));
    document.getElementById("mediaInput").files = dt.files;
});

document.querySelector(".close-modal").onclick = () => {
    document.getElementById("imageModal").style.display = "none";
};

function resetForm() {
    if(confirm('Are you sure you want to reset all fields?')) {
        location.reload();
    }
}
</script>

</body>
</html>