<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<nav>
    <?php
    // Only show the welcome message if user is logged in
    if (isset($_SESSION['user_id'])) {
        echo '<a href="edit_profile.php"><i class="fas fa-user-circle"></i> Hi, ' . htmlspecialchars($_SESSION['name']) . '</a> |';
    }
    ?>
    <a href="index.php"><i class="fas fa-home"></i> Home</a> |
    <a href="upload.php"><i class="fas fa-upload"></i> Upload</a> |
    <a href="#" class="filter-toggle" onclick="toggleFilters()">
        <i class="fas fa-sliders-h"></i> Filters
    </a> |
    <?php
    if (isset($_SESSION['user_id'])) {
        echo '<a href="my_listings.php"><i class="fas fa-building"></i> My Listings</a> |';
        echo '<a href="shortlist.php"><i class="fas fa-heart"></i> My Shortlisted</a> |';
        echo '<a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a> |';
        echo '<a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>';
    } else {
        echo '<a href="login.php"><i class="fas fa-heart"></i> My Shortlisted</a> |';
        echo '<a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a> |';
        echo '<a href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a>';
    }
    ?>
</nav>

<!-- FILTER PANEL -->
<div id="filterPanel" class="filter-panel">
    <div class="filter-content">
        
        <div class="filter-section">
            <h3><i class="fas fa-sort"></i> Sort By</h3>
            <label class="filter-radio">
                <input type="radio" name="sort" value="lowest" id="sortLowest"> <i class="fas fa-arrow-up"></i> Lowest Price First
            </label>
            <label class="filter-radio">
                <input type="radio" name="sort" value="highest" id="sortHighest"> <i class="fas fa-arrow-down"></i> Highest Price First
            </label>
            <label class="filter-radio">
                <input type="radio" name="sort" value="latest" id="sortLatest"> <i class="fas fa-clock"></i> Latest Listings First
            </label>
        </div>

        <div class="filter-section">
            <h3><i class="fas fa-building"></i> Property Type</h3>
            <div class="filter-checkbox-group">
                <label class="filter-checkbox"><input type="checkbox" name="property_type" value="Apartment"> <i class="fas fa-building"></i> Apartment</label>
                <label class="filter-checkbox"><input type="checkbox" name="property_type" value="Independent House"> <i class="fas fa-home"></i> Independent House</label>
                <label class="filter-checkbox"><input type="checkbox" name="property_type" value="Villa"> <i class="fas fa-tree"></i> Villa</label>
                <label class="filter-checkbox"><input type="checkbox" name="property_type" value="Plot"> <i class="fas fa-map"></i> Plot</label>
            </div>
        </div>

        <div class="filter-section">
            <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
            <input type="text" id="locationFilter" class="filter-input" placeholder="Enter location...">
        </div>

        <div class="filter-section">
            <h3><i class="fas fa-rupee-sign"></i> Price Range</h3>
            <div class="price-range">
                <input type="number" id="minPrice" class="filter-input" placeholder="Min" value="0">
                <span>to</span>
                <input type="number" id="maxPrice" class="filter-input" placeholder="Max" value="5000000">
            </div>
        </div>

        <div class="filter-section">
            <h3><i class="fas fa-bed"></i> BHK</h3>
            <div class="filter-checkbox-group">
                <label class="filter-checkbox"><input type="checkbox" name="bhk" value="1RK"> 1RK</label>
                <label class="filter-checkbox"><input type="checkbox" name="bhk" value="1BHK"> 1BHK</label>
                <label class="filter-checkbox"><input type="checkbox" name="bhk" value="2BHK"> 2BHK</label>
                <label class="filter-checkbox"><input type="checkbox" name="bhk" value="3BHK"> 3BHK</label>
                <label class="filter-checkbox"><input type="checkbox" name="bhk" value="4BHK"> 4BHK</label>
                <label class="filter-checkbox"><input type="checkbox" name="bhk" value="4+BHK"> 4+BHK</label>
            </div>
        </div>

        <div class="filter-section">
            <h3><i class="fas fa-users"></i> Tenant Type</h3>
            <div class="filter-checkbox-group">
                <label class="filter-checkbox"><input type="checkbox" name="tenant" value="Anyone"> <i class="fas fa-globe"></i> Anyone</label>
                <label class="filter-checkbox"><input type="checkbox" name="tenant" value="Girls"> <i class="fas fa-female"></i> Girls</label>
                <label class="filter-checkbox"><input type="checkbox" name="tenant" value="Boys"> <i class="fas fa-male"></i> Boys</label>
                <label class="filter-checkbox"><input type="checkbox" name="tenant" value="Bachelors"> <i class="fas fa-user-friends"></i> Bachelors</label>
                <label class="filter-checkbox"><input type="checkbox" name="tenant" value="Family"> <i class="fas fa-family"></i> Family</label>
                <label class="filter-checkbox"><input type="checkbox" name="tenant" value="Student"> <i class="fas fa-graduation-cap"></i> Student</label>
            </div>
        </div>

        <div class="filter-buttons">
            <button class="apply-filters-btn" onclick="applyFilters()">
                <i class="fas fa-check"></i> Apply Filters
            </button>
            <button class="remove-filters-btn" onclick="removeFilters()">
                <i class="fas fa-trash"></i> Remove Filters
            </button>
        </div>
    </div>
</div>

<hr>

<script>
function toggleFilters() {
    let panel = document.getElementById("filterPanel");
    if (panel.classList.contains("show")) {
        panel.classList.remove("show");
    } else {
        panel.classList.add("show");
    }
}

function applyFilters() {
    let params = new URLSearchParams();
    
    let selectedSort = document.querySelector('input[name="sort"]:checked');
    if (selectedSort) {
        params.append("sort", selectedSort.value);
    }
    
    let propertyTypeValues = [];
    document.querySelectorAll('input[name="property_type"]:checked').forEach(cb => {
        propertyTypeValues.push(cb.value);
    });
    if (propertyTypeValues.length > 0) {
        params.append("property_type", propertyTypeValues.join(","));
    }
    
    let location = document.getElementById("locationFilter").value.trim();
    if (location) {
        params.append("location", location);
    }
    
    let minPrice = document.getElementById("minPrice").value;
    let maxPrice = document.getElementById("maxPrice").value;
    if (minPrice) params.append("min_price", minPrice);
    if (maxPrice) params.append("max_price", maxPrice);
    
    let bhkValues = [];
    document.querySelectorAll('input[name="bhk"]:checked').forEach(cb => {
        bhkValues.push(cb.value);
    });
    if (bhkValues.length > 0) {
        params.append("bhk", bhkValues.join(","));
    }
    
    let tenantValues = [];
    document.querySelectorAll('input[name="tenant"]:checked').forEach(cb => {
        tenantValues.push(cb.value);
    });
    if (tenantValues.length > 0) {
        params.append("tenant", tenantValues.join(","));
    }
    
    window.location.href = "index.php?" + params.toString();
}

function removeFilters() {
    window.location.href = "index.php";
}

function loadFiltersFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    
    const sort = urlParams.get("sort");
    if (sort === "lowest") document.getElementById("sortLowest").checked = true;
    else if (sort === "highest") document.getElementById("sortHighest").checked = true;
    else if (sort === "latest") document.getElementById("sortLatest").checked = true;
    
    const propertyType = urlParams.get("property_type");
    if (propertyType) {
        const propertyTypeArray = propertyType.split(",");
        document.querySelectorAll('input[name="property_type"]').forEach(cb => {
            if (propertyTypeArray.includes(cb.value)) cb.checked = true;
        });
    }
    
    const location = urlParams.get("location");
    if (location) document.getElementById("locationFilter").value = location;
    
    const minPrice = urlParams.get("min_price");
    const maxPrice = urlParams.get("max_price");
    if (minPrice) document.getElementById("minPrice").value = minPrice;
    if (maxPrice) document.getElementById("maxPrice").value = maxPrice;
    
    const bhk = urlParams.get("bhk");
    if (bhk) {
        const bhkArray = bhk.split(",");
        document.querySelectorAll('input[name="bhk"]').forEach(cb => {
            if (bhkArray.includes(cb.value)) cb.checked = true;
        });
    }
    
    const tenant = urlParams.get("tenant");
    if (tenant) {
        const tenantArray = tenant.split(",");
        document.querySelectorAll('input[name="tenant"]').forEach(cb => {
            if (tenantArray.includes(cb.value)) cb.checked = true;
        });
    }
}

window.onload = function() {
    loadFiltersFromURL();
};
</script>