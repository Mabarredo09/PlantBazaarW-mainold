document.addEventListener("DOMContentLoaded", function() {
    const regionSelect = document.getElementById("region");
    const citySelect = document.getElementById("city");
    const updateButton = document.getElementById("submit"); // Use the correct ID for the button

    // User's current data (retrieved from PHP)
    const userRegion = "<?php echo $region; ?>";
    const userCity = "<?php echo $city; ?>";

    // Populate regions
    Object.keys(philippinesData).forEach(region => {
        const option = document.createElement("option");
        option.value = region;
        option.textContent = region;

        // Pre-select the current region
        if (region === userRegion) {
            option.selected = true;
        }

        regionSelect.appendChild(option);
    });

    // Initial population of cities if a region is already selected
    if (userRegion) {
        populateCities(userRegion);
    }

    // Function to populate cities based on selected region
    function populateCities(region) {
        console.log("Selected region:", region);
        const cities = philippinesData[region] || [];
        console.log("Available cities:", cities);

        // Clear existing city options
        citySelect.innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';

        // Populate city dropdown
        cities.forEach(city => {
            const option = document.createElement("option");
            option.value = city;
            option.textContent = city;

            // Pre-select the current city if it matches
            if (city === userCity) {
                option.selected = true;
            }

            citySelect.appendChild(option);
        });

        checkFormCompletion();
    }

    // Event listener for region change
    regionSelect.addEventListener("change", function() {
        const selectedRegion = this.value;
        populateCities(selectedRegion);
    });

    // Add event listener for city selection
    citySelect.addEventListener("change", checkFormCompletion);

    function checkFormCompletion() {
        // Enable the update button if both region and city are selected
        updateButton.disabled = !(regionSelect.value && citySelect.value);
    }
});
