document.addEventListener('DOMContentLoaded', function () {
    const usernameInput = document.getElementById('username');
    const availabilityMessage = document.getElementById('username-availability');

    let debounceTimeout;

    if (usernameInput) {
        usernameInput.addEventListener('input', function () {
            const username = this.value;

            // Clear the previous timeout if there was one
            clearTimeout(debounceTimeout);

            if (username.length >= 3) {  // Only start checking if username is 3+ characters
                // Set a new timeout to delay the request
                debounceTimeout = setTimeout(function () {
                    fetch(`../user/check-username?username=${encodeURIComponent(username)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.available) {
                                availabilityMessage.textContent = "Username is available";
                                availabilityMessage.style.color = "green";
                            } else {
                                availabilityMessage.textContent = "Username is already taken";
                                availabilityMessage.style.color = "red";
                            }
                        })
                        .catch(error => {
                            console.error('There was an error with the AJAX request:', error);
                            availabilityMessage.textContent = "Error checking availability";
                            availabilityMessage.style.color = "orange";
                        });
                }, 350); // Delay in milliseconds (e.g., 500ms after the user stops typing)
            } else {
                // Clear message if the username is too short
                availabilityMessage.textContent = '';
            }
        });
    }
});