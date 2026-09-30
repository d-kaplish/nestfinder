<?php
session_start();
include("db.php");
include("navbar.php");

if (!isset($_GET['id'])) {
    echo "Invalid Property";
    exit();
}

$property_id = $_GET['id'];

mysqli_query($conn, "UPDATE properties SET view_count = view_count + 1 WHERE id = '$property_id'");

$propertyQuery = mysqli_query($conn,
    "SELECT * FROM properties WHERE id='$property_id'"
);
$property = mysqli_fetch_assoc($propertyQuery);

if (!$property) {
    echo "Property not found";
    exit();
}

$mediaQuery = mysqli_query($conn,
    "SELECT * FROM pictures WHERE property_id='$property_id'"
);

$ownerQuery = mysqli_query($conn,
    "SELECT email, contact FROM users WHERE user_id='{$property['user_id']}'"
);
$owner = mysqli_fetch_assoc($ownerQuery);

$shortlistMessage = "";
if (isset($_POST['shortlist'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $checkQuery = mysqli_query($conn, "SELECT * FROM shortlist WHERE user_id='$user_id' AND property_id='$property_id'");
        if (mysqli_num_rows($checkQuery) == 0) {
            mysqli_query($conn,
                "INSERT INTO shortlist (user_id, property_id)
                 VALUES ('$user_id', '$property_id')"
            );
            $shortlistMessage = '<div class="success-toast"><i class="fas fa-heart"></i> Added to shortlist!</div>';
        } else {
            $shortlistMessage = '<div class="warning-toast"><i class="fas fa-info-circle"></i> Already in shortlist!</div>';
        }
    }
}

$reportMessage = "";
if (isset($_POST['report_property'])) {
    if (!isset($_SESSION['user_id'])) {
        $reportMessage = '<div class="warning-toast"><i class="fas fa-exclamation-triangle"></i> Please login to report</div>';
    } else {
        $user_id = $_SESSION['user_id'];
        $report_type = $_POST['report_type'];
        $report_reason = mysqli_real_escape_string($conn, $_POST['report_reason']);
        
        $checkReport = mysqli_query($conn, "SELECT * FROM reports WHERE property_id='$property_id' AND user_id='$user_id'");
        if (mysqli_num_rows($checkReport) > 0) {
            $reportMessage = '<div class="warning-toast"><i class="fas fa-info-circle"></i> You have already reported this property</div>';
        } else {
            $insert = mysqli_query($conn, "INSERT INTO reports (property_id, user_id, report_type, report_reason, status) 
                                           VALUES ('$property_id', '$user_id', '$report_type', '$report_reason', 'pending')");
            if ($insert) {
                $reportMessage = '<div class="success-toast"><i class="fas fa-check-circle"></i> Report submitted successfully! Admin will review it.</div>';
            } else {
                $reportMessage = '<div class="warning-toast"><i class="fas fa-exclamation-triangle"></i> Failed to submit report</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $property['location']; ?> | NestFinder</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="property-container-modern">

    <?php echo $shortlistMessage; ?>
    <?php echo $reportMessage; ?>

    <div class="back-button">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
    </div>

    <div class="property-header">
        <div class="property-badge">
            <span class="badge <?php echo strtolower($property['type']); ?>">
                <i class="fas fa-tag"></i> <?php echo $property['type']; ?>
            </span>
            <span class="badge property-type-badge">
                <i class="fas fa-building"></i> <?php echo $property['property_type']; ?>
            </span>
        </div>
        <h1><?php echo $property['location']; ?></h1>
        <div class="property-price">
            <span class="price-amount"><?php echo indianCurrency($property['price']); ?></span>
            <span class="price-type"><?php echo $property['type'] == 'Rent' ? '/ month' : ''; ?></span>
        </div>
    </div>

    <div class="property-content-grid">
        
        <div class="media-column">
            <div class="slider-modern">
                <div id="slides-modern" style="display: flex !important; width: 100% !important;">
                    <?php 
                    $mediaCount = 1;
                    $mediaArray = [];
                    ?>
                    <div class="slide-modern">
                        <img src="uploads/<?php echo $property['image']; ?>" alt="Main Property Image" onclick="openImagePreview('uploads/<?php echo $property['image']; ?>', 'Main Image')" style="cursor: pointer;">
                    </div>
                    
                    <?php 
                    $mediaQuery = mysqli_query($conn, "SELECT * FROM pictures WHERE property_id='$property_id'");
                    while ($media = mysqli_fetch_assoc($mediaQuery)) {
                        $file = $media['file_name'];
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        $mediaCount++;
                        $mediaArray[] = $file;
                        
                        if (in_array($ext, ['mp4','mov','avi','webm','mkv'])) { ?>
                            <div class="slide-modern">
                                <video controls>
                                    <source src="uploads/<?php echo $file; ?>">
                                </video>
                            </div>
                        <?php } else { ?>
                            <div class="slide-modern">
                                <img src="uploads/<?php echo $file; ?>" alt="Property Image" onclick="openImagePreview('uploads/<?php echo $file; ?>', 'Property Image')" style="cursor: pointer;">
                            </div>
                    <?php }
                    } ?>
                </div>
                
                <button class="slider-btn prev-btn" onclick="prevModern()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-btn next-btn" onclick="nextModern()">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <div class="slide-counter">
                    <span id="currentSlide">1</span> / <span id="totalSlides"><?php echo $mediaCount; ?></span>
                </div>
                
                <div class="thumbnail-nav">
                    <?php 
                    $thumbIndex = 0;
                    $thumbImages = array_merge([$property['image']], $mediaArray);
                    foreach($thumbImages as $thumb) {
                        $thumbExt = pathinfo($thumb, PATHINFO_EXTENSION);
                        if (!in_array($thumbExt, ['mp4','mov','avi','webm','mkv'])) {
                    ?>
                        <div class="thumbnail" data-index="<?php echo $thumbIndex; ?>" onclick="goToSlide(<?php echo $thumbIndex; ?>); openImagePreview('uploads/<?php echo $thumb; ?>', 'Property Image')">
                            <img src="uploads/<?php echo $thumb; ?>" alt="Thumbnail">
                        </div>
                    <?php 
                        }
                        $thumbIndex++;
                    } 
                    ?>
                </div>
            </div>
        </div>
        
        <div class="details-column">
            
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-bed"></i>
                    <div class="feature-info">
                        <span class="feature-label">BHK</span>
                        <span class="feature-value"><?php echo $property['rooms']; ?></span>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fas fa-arrows-alt"></i>
                    <div class="feature-info">
                        <span class="feature-label">Area</span>
                        <span class="feature-value"><?php echo $property['sqft'] ? $property['sqft'] . ' sq ft' : 'Not Specified'; ?></span>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fas fa-couch"></i>
                    <div class="feature-info">
                        <span class="feature-label">Furnishing</span>
                        <span class="feature-value"><?php echo ucfirst($property['furnish']); ?></span>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fas fa-parking"></i>
                    <div class="feature-info">
                        <span class="feature-label">Parking</span>
                        <span class="feature-value"><?php echo $property['parking'] ?: 'Not Specified'; ?></span>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <div class="feature-info">
                        <span class="feature-label">Preferred Tenant</span>
                        <span class="feature-value"><?php echo $property['preferred_tenant']; ?></span>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fas fa-calendar-alt"></i>
                    <div class="feature-info">
                        <span class="feature-label">Possession</span>
                        <span class="feature-value"><?php echo $property['possession_date']; ?></span>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <div class="feature-info">
                        <span class="feature-label">Age of Building</span>
                        <span class="feature-value"><?php echo $property['age_of_building'] ?: 'Not Specified'; ?></span>
                    </div>
                </div>
                <div class="feature-card">
                    <i class="fas fa-building"></i>
                    <div class="feature-info">
                        <span class="feature-label">Property Type</span>
                        <span class="feature-value"><?php echo $property['property_type']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <?php if(isset($_SESSION['user_id'])) { ?>
                    <form method="POST" class="shortlist-form">
                        <button type="submit" name="shortlist" class="btn-shortlist">
                            <i class="fas fa-heart"></i> Add to Shortlist
                        </button>
                    </form>
                <?php } else { ?>
                    <a href="login.php" class="btn-shortlist">
                        <i class="fas fa-heart"></i> Login to Shortlist
                    </a>
                <?php } ?>
                
                <?php if(isset($_SESSION['user_id'])) { ?>
                    <button onclick="showOwner()" class="btn-owner">
                        <i class="fas fa-user"></i> Show Owner Details
                    </button>
                <?php } else { ?>
                    <a href="login.php" class="btn-owner">
                        <i class="fas fa-user"></i> Login to View Owner Details
                    </a>
                <?php } ?>
                
                <?php if(isset($_SESSION['user_id'])) { ?>
                    <button onclick="openReportModal()" class="btn-report">
                        <i class="fas fa-flag"></i> Report
                    </button>
                <?php } else { ?>
                    <a href="login.php" class="btn-report">
                        <i class="fas fa-flag"></i> Report (Login Required)
                    </a>
                <?php } ?>
            </div>
            
            <div id="owner-card" class="owner-card" style="display:none;">
                <div class="owner-header">
                    <i class="fas fa-user-circle"></i>
                    <h3>Property Owner</h3>
                </div>
                <div class="owner-details">
                    <div class="owner-info">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo $owner['email']; ?></span>
                    </div>
                    <div class="owner-info">
                        <i class="fas fa-phone-alt"></i>
                        <span><?php echo $owner['contact']; ?></span>
                    </div>
                </div>
                <button onclick="copyContact('<?php echo $owner['contact']; ?>')" class="btn-copy">
                    <i class="fas fa-copy"></i> Copy Contact
                </button>
            </div>
            
        </div>
        
    </div>

</div>

<div id="reportModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-flag"></i> Report Property</h3>
            <span class="close-modal" onclick="closeReportModal()">&times;</span>
        </div>
        <form method="POST" action="" id="reportForm">
            <div class="form-group">
                <label><i class="fas fa-exclamation-triangle"></i> Report Type</label>
                <select name="report_type" required>
                    <option value="">Select reason</option>
                    <option value="fake_listing">Fake / Fraud Listing</option>
                    <option value="wrong_price">Wrong Price Information</option>
                    <option value="already_sold">Property Already Sold/Rented</option>
                    <option value="inappropriate_content">Inappropriate Content</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-comment"></i> Additional Details</label>
                <textarea name="report_reason" rows="4" placeholder="Please provide more details about your report..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn" onclick="closeReportModal()">Cancel</button>
                <button type="submit" name="report_property" class="submit-report-btn">
                    <i class="fas fa-paper-plane"></i> Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<div id="imagePreviewModal" class="image-preview-modal" onclick="closeImagePreview()">
    <span class="close-preview" onclick="closeImagePreview()">&times;</span>
    <img class="preview-image" id="previewImage" src="">
    <div class="preview-caption" id="previewCaption"></div>
</div>

<script>
let currentIndex = 0;
const slides = document.querySelectorAll("#slides-modern .slide-modern");
const totalSlides = slides.length;
const currentSlideSpan = document.getElementById("currentSlide");
const totalSlidesSpan = document.getElementById("totalSlides");

if (totalSlidesSpan) {
    totalSlidesSpan.textContent = totalSlides;
}

function updateSlider() {
    const slidesContainer = document.getElementById("slides-modern");
    slidesContainer.style.transform = "translateX(" + (-currentIndex * 100) + "%)";
    if (currentSlideSpan) {
        currentSlideSpan.textContent = currentIndex + 1;
    }
    updateThumbnailActive();
}

function nextModern() {
    if (currentIndex < totalSlides - 1) {
        currentIndex++;
    } else {
        currentIndex = 0;
    }
    updateSlider();
}

function prevModern() {
    if (currentIndex > 0) {
        currentIndex--;
    } else {
        currentIndex = totalSlides - 1;
    }
    updateSlider();
}

function goToSlide(index) {
    currentIndex = index;
    updateSlider();
}

function updateThumbnailActive() {
    const thumbnails = document.querySelectorAll(".thumbnail");
    thumbnails.forEach((thumb, idx) => {
        if (idx === currentIndex) {
            thumb.classList.add("active");
        } else {
            thumb.classList.remove("active");
        }
    });
}

function showOwner() {
    const ownerCard = document.getElementById("owner-card");
    if (ownerCard.style.display === "none") {
        ownerCard.style.display = "block";
    } else {
        ownerCard.style.display = "none";
    }
}

function copyContact(contact) {
    navigator.clipboard.writeText(contact).then(() => {
        const btn = event.target.closest('.btn-copy');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    });
}

function openReportModal() {
    document.getElementById("reportModal").style.display = "flex";
}

function closeReportModal() {
    document.getElementById("reportModal").style.display = "none";
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

window.onclick = function(event) {
    const modal = document.getElementById("reportModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
    const imageModal = document.getElementById("imagePreviewModal");
    if (event.target == imageModal) {
        closeImagePreview();
    }
}

setTimeout(() => {
    const toasts = document.querySelectorAll('.success-toast, .warning-toast');
    toasts.forEach(toast => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    });
}, 3000);

document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') {
        prevModern();
    } else if (e.key === 'ArrowRight') {
        nextModern();
    } else if (e.key === 'Escape') {
        closeImagePreview();
        closeReportModal();
    }
});
</script>

</body>
</html>