document.addEventListener("DOMContentLoaded", function() {
    const regionSelect = document.getElementById("region");
    const citySelect = document.getElementById("city");
    const signupButton = document.getElementById("signupButton");

    // Populate regions
    Object.keys(philippinesData).forEach(region => {
        const option = document.createElement("option");
        option.value = region;
        option.textContent = region;
        regionSelect.appendChild(option);
    });

    // Update city options based on region selection
    regionSelect.addEventListener("change", function() {
        const selectedRegion = this.value;
        const cities = philippinesData[selectedRegion] || [];

        // Reset city dropdown
        citySelect.innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';

        // Populate city dropdown
        cities.forEach(city => {
            const option = document.createElement("option");
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });

        // Enable submit button if both region and city are selected
        checkFormCompletion();
    });

    // Add event listener for city selection
    citySelect.addEventListener("change", checkFormCompletion);

    function checkFormCompletion() {
        // Enable the signup button if both region and city are selected
        signupButton.disabled = !(regionSelect.value && citySelect.value);
    }
});
