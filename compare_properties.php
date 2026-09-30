<?php
session_start();
include("db.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all shortlisted properties
$shortlistedQuery = mysqli_query($conn,
    "SELECT p.* FROM properties p
     JOIN shortlist s ON p.id = s.property_id
     WHERE s.user_id='$user_id'"
);

$shortlistedProperties = [];
while ($row = mysqli_fetch_assoc($shortlistedQuery)) {
    $shortlistedProperties[] = $row;
}

// Get selected properties for comparison
$selectedIds = isset($_POST['selected_properties']) ? $_POST['selected_properties'] : [];
$compareProperties = [];

if (!empty($selectedIds)) {
    $ids = implode(",", array_map('intval', $selectedIds));
    $compareQuery = mysqli_query($conn, "SELECT * FROM properties WHERE id IN ($ids)");
    while ($row = mysqli_fetch_assoc($compareQuery)) {
        $compareProperties[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Compare Properties</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<h2><i class="fas fa-chart-line"></i> Compare Properties</h2>

<?php if (empty($compareProperties)): ?>
    <!-- Step 1: Select properties to compare -->
    <h3>Choose properties to compare</h3>
    
    <form method="POST" action="compare_properties.php" id="compareForm">
        <div class="compare-properties-container">
            <?php if (count($shortlistedProperties) > 0): ?>
                <?php foreach ($shortlistedProperties as $property): ?>
                    <div class="compare-card">
                        <div class="compare-checkbox">
                            <input type="checkbox" name="selected_properties[]" value="<?php echo $property['id']; ?>" id="prop_<?php echo $property['id']; ?>">
                            <label for="prop_<?php echo $property['id']; ?>" class="checkbox-label">
                                <i class="fas fa-check-circle"></i>
                            </label>
                        </div>
                        <img src="uploads/<?php echo $property['image']; ?>" alt="<?php echo $property['location']; ?>">
                        <div class="compare-card-info">
                            <h4><?php echo $property['location']; ?></h4>
                            <p>₹<?php echo $property['price']; ?></p>
                            <p><?php echo $property['rooms']; ?> • <?php echo $property['property_type']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-heart-broken"></i>
                    <h3>No properties in shortlist</h3>
                    <p>Add some properties to your shortlist first</p>
                    <a href="index.php" class="clear-filters-btn">Browse Properties</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (count($shortlistedProperties) > 0): ?>
            <div class="compare-submit">
                <button type="submit" class="compare-submit-btn" id="compareBtn" disabled>
                    <i class="fas fa-chart-line"></i> Compare Selected Properties
                </button>
                <p class="compare-note"><i class="fas fa-info-circle"></i> Select at least 2 properties to compare</p>
            </div>
        <?php endif; ?>
    </form>

<?php else: ?>
    <!-- Step 2: Display comparison table -->
    <div class="compare-results">
        <div class="compare-header">
            <h3>Comparison Results</h3>
            <a href="compare_properties.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Compare Different Properties
            </a>
        </div>
        
        <div class="comparison-table-wrapper">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th class="criteria-col">Criteria</th>
                        <?php foreach ($compareProperties as $property): ?>
                            <th class="property-col">
                                <div class="property-header">
                                    <img src="uploads/<?php echo $property['image']; ?>" alt="<?php echo $property['location']; ?>" class="thumb-img">
                                    <h4><?php echo $property['location']; ?></h4>
                                    <p class="price">₹<?php echo $property['price']; ?></p>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Images Gallery -->
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-images"></i> Images</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell">
                                <div class="image-gallery">
                                    <?php
                                    // Get all images for this property
                                    $imgQuery = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id = '{$property['id']}'");
                                    $images = [];
                                    while ($img = mysqli_fetch_assoc($imgQuery)) {
                                        $images[] = $img['file_name'];
                                    }
                                    // Add main image
                                    array_unshift($images, $property['image']);
                                    ?>
                                    <div class="gallery-scroll">
                                        <?php foreach ($images as $img): ?>
                                            <img src="uploads/<?php echo $img; ?>" class="gallery-img" onclick="openModal(this.src)">
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-map-marker-alt"></i> Location</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['location']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-rupee-sign"></i> Price</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell">₹<?php echo indianCurrency($property['price']); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-arrows-alt"></i> Area</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo isset($property['sqft']) && $property['sqft'] ? $property['sqft'] . ' sq ft' : 'N/A'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-tag"></i> Type</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['type']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-building"></i> Property Type</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['property_type']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-bed"></i> BHK</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['rooms']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-couch"></i> Furnishing</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo ucfirst($property['furnish']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-parking"></i> Parking</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['parking']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <!-- Preferred Tenant -->
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-users"></i> Tenant Type</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['preferred_tenant']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <!-- Possession Date -->
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-calendar-alt"></i> Possession</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['possession_date']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <!-- Age of Building -->
                    <tr>
                        <td class="criteria-cell"><strong><i class="fas fa-clock"></i> Age of Building</strong></td>
                        <?php foreach ($compareProperties as $property): ?>
                            <td class="property-cell"><?php echo $property['age_of_building']; ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Modal for image fullscreen -->
<div id="imageModal" class="image-modal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImg">
</div>

<script>
// Enable/disable compare button based on checkbox selection
document.querySelectorAll('input[name="selected_properties[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        let checkedCount = document.querySelectorAll('input[name="selected_properties[]"]:checked').length;
        let compareBtn = document.getElementById('compareBtn');
        if (compareBtn) {
            compareBtn.disabled = checkedCount < 2;
            if (checkedCount >= 2) {
                compareBtn.style.opacity = '1';
                compareBtn.style.cursor = 'pointer';
            } else {
                compareBtn.style.opacity = '0.5';
                compareBtn.style.cursor = 'not-allowed';
            }
        }
    });
});

// Image modal functions
function openModal(src) {
    let modal = document.getElementById('imageModal');
    let modalImg = document.getElementById('modalImg');
    modal.style.display = 'flex';
    modalImg.src = src;
}

function closeModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    let modal = document.getElementById('imageModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>