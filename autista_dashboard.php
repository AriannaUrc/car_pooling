<?php
// Start the session
session_start();
//var_dump($_SESSION);

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'autista') {
    header('Location: index.php');
    exit;
}

//echo "<h1>Welcome, {$_SESSION['username']}! You are logged in as an Autista.</h1>";
?>

<div class="container">
  <div class="header">
  <h1><?php echo $_SESSION['username'];?> (DRIVER)</h1>
    <form action="" method="post">
      <input type="submit" name="logout" value="Logout" class="logout-button">
    </form>
  </div>
  <div class="row">
    <div class="col-left">
      <h2>Add a New Trip</h2>
      <form id="tripForm" method="post">
        <label for="data_partenza">Date of Departure:</label>
        <input type="date" id="data_partenza" name="data_partenza" required><br>
        <label for="ora_partenza">Time of Departure:</label>
        <input type="time" id="ora_partenza" name="ora_partenza" required><br>
        <label for="contributo_economico">Economic Contribution:</label>
        <input type="number" id="contributo_economico" name="contributo_economico" step="0.01" required><br><br>
        <label for="tempo_percorrenza">Travel Duration (minutes):</label>
        <input type="number" id="tempo_percorrenza" name="tempo_percorrenza" required><br><br>
        <label for="fermate_servizio">Pit Stops:</label>
        <select id="fermate_servizio" name="fermate_servizio" multiple>
        </select><br>
        <label for="animali_allowed">Allow Animals:</label>
        <input type="checkbox" id="animali_allowed" name="animali_allowed"><br>
        <label for="posti_disponibili">Available Seats:</label>
        <input type="number" id="posti_disponibili" name="posti_disponibili" required><br><br>
        <label for="id_citta_partenza">City of Departure:</label>
        <select id="id_citta_partenza" name="id_citta_partenza" required>
          <option value="">Select a city</option>
        </select><br>
        <label for="id_citta_destinazione">City of Destination:</label>
        <select id="id_citta_destinazione" name="id_citta_destinazione" required>
          <option value="">Select a city</option>
        </select><br>
        <input type="submit" value="Add Trip">
      </form>

      <div class="search-autista-form">
        <h2>Search for a user</h2>
        <form id="search-form">
          <input type="number" id="search-id" placeholder="Enter autista ID">
          <button type="submit">Search</button>
        </form>
      </div>
      <div class="search-results-container">
        <div id="search-results"></div>
      </div>
    
    </div>
    <div class="col-right">
      <h2>Your Trips</h2>
      <table id="tripsTable" border="1"></table>
      <button id="prev" disabled>Previous</button>
      <button id="next">Next</button>
      <br><br>
      <div id="applications-container"></div>
    </div>
    
  </div>
</div>


<style>
  .container {
  max-width: 90%;
  margin: 40px auto;
  padding: 20px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header {
    background-color: #CCC;
    color: #000;
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    position: relative;
    border-radius: 9px;
}
    
.logout-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #ff0000;
    color: #fff;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    width: 80px;
}

.row {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  margin-bottom: 20px;
}

.col-left {
  width: 45%;
  margin-right: 2%;
}
.col-right {
  width: 51%;
}

#review-form textarea {
    width: 100%; /* or you can specify a fixed width like 300px */
    height: 150px; /* You can adjust the height to fit your needs */
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    resize: vertical; /* Allows the user to resize vertically if needed */
}


#tripForm {
  padding: 10px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#tripForm label {
  display: block;
  margin-bottom: 10px;
}

#tripForm input[type="date"], #tripForm input[type="time"], #tripForm select {
  width: 100%;
  height: 40px;
  margin-bottom: 20px;
  padding: 5px;
  border: 1px solid #ccc;
}

#tripForm input[type="checkbox"] {
  margin-bottom: 20px;
}

#tripForm input[type="submit"] {
  width: 100px;
  height: 40px;
  background-color: #333;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

#tripForm input[type="submit"]:hover {
  background-color: #444;
}

Table {
  width: 100%;
  border-collapse: collapse;
}

Table th, Table td {
  border: 1px solid #ddd;
  padding: 5px;
  text-align: left;
}

Table th {
  background-color: #f0f0f0;
}

#prev, #next {
  margin-top: 10px;
  background-color: #333;
  color: #fff;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
  width: 80px;
}

#prev:hover, #next:hover {
  background-color: #444;
}

#prev:disabled, #next:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.search-form {
  margin-top: 40px;
}

.search-form label {
  display: block;
  margin-bottom: 10px;
}

.search-form input[type="number"] {
  width: 100%;
  height: 40px;
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ccc;
}

.search-form button[type="submit"] {
  width: 100px;
  height: 40px;
  background-color: #333;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.search-form button[type="submit"]:hover {
  background-color: #444;
}

#search-results {
  padding: 20px;
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#search-results h2 {
  margin-top: 0;
}
.row {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}


@media only screen and (max-width: 1700px) {
 .container {
    margin: 20px auto;
  }
 .row {
    flex-direction: column;
  }
 .col-left,.col-right {
    width: 100%;
    margin-right: 0;
    margin-bottom: 20px;
  }
}
</style>
<script>

    const limit = 4;
    let offset = 0;


    document.getElementById('prev').addEventListener('click', () => {
            offset = Math.max(0, offset - limit);
            console.log(offset)
            fetchData();
            if(offset == 0){
                document.getElementById('prev').disabled = true;
            }
            document.getElementById('next').disabled = false;
        });

    document.getElementById('next').addEventListener('click', () => {
        offset += limit;
        console.log(offset)
        fetchData();
        document.getElementById('prev').disabled = false;
    });

    //each time you click the prev or next you send the get trips query again with different offset
    const fetchData = async () => {
        ws.send(JSON.stringify({ action: 'getTrips', userId: "<?php echo $_SESSION['user_id']; ?>", limitN: limit, offsetN: offset }));
    };


    const ws = new WebSocket('ws://localhost:10000');

    ws.onopen = () => {
        console.log('WebSocket connected');
        ws.send(JSON.stringify({ action: 'getCities' }));
        ws.send(JSON.stringify({ action: 'getStops' }));
        ws.send(JSON.stringify({ action: 'getTripTypes' }));
        ws.send(JSON.stringify({ action: 'getTrips', userId: "<?php echo $_SESSION['user_id']; ?>", limitN: limit, offsetN: offset }));
    };

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        console.log(data);
        if (data.type === 'cities') {
            populateCities(data.cities);
        } else if (data.type === 'tripTypes') {
            populateTripTypes(data.tripTypes);
        } else if (data.type === 'trips') {
            displayTrips(data.trips);
        } else if (data.type === 'applications') {
            displayApplications(data.results);
        } else if (data.type === 'tripCreated') {
            if (data.status ==='success') {
            alert('Trip added successfully');
            window.location.reload();
            } else {
            alert('Error adding trip:'+ data.message);
            }
        } else if (data.type === 'userDetails') {
            displayUserDetails(data.user);
        } else if (data.type === 'userRVDetails') {
            displaySearchResults(data);
        } else if (data.type === 'stops') {
          populateStops(data.stops);
        }
        else if(data.type='noSeats'){
          //alert("There arent enough seats");
          window.location.reload();
        }
        else if(data.type='applicationAccepted'){
          window.location.reload();
        }
    };

    function populateCities(cities) {
        const departureSelect = document.getElementById('id_citta_partenza');
        const destinationSelect = document.getElementById('id_citta_destinazione');
        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city.id_citta;
            option.textContent = city.nome_citta;
            departureSelect.appendChild(option);
            destinationSelect.appendChild(option.cloneNode(true));
        });
    }

  function populateStops(stops) {
      const pitStops = document.getElementById('fermate_servizio');
      stops.forEach(stop => {
          const option = document.createElement('option');
          option.value = stop.id;
          option.textContent = stop.nome;
          pitStops.appendChild(option);
      });
  }


    function displayTrips(trips) {
        const tripsTable = document.getElementById('tripsTable');
        const tbody = tripsTable.querySelector('tbody');
        if (tbody) {
            tripsTable.removeChild(tbody);
        }
        const newTbody = document.createElement('tbody');
        const firstrow = document.createElement('tr');
        firstrow.innerHTML = `
  <th>Date of Departure</th>
  <th>Time of Departure</th>
  <th>Economic Contribution</th>
  <th>Travel Duration (minutes)</th>
  <th>Available Seats</th>
  <th>Departure</th>
  <th>Destination</th>
  <th>App. Aperte</th>
  <th>Registrations</th>
`;

        newTbody.appendChild(firstrow);
        if(trips.length <= 0 && offset > 0){
            const row = document.createElement('tr');
            row.innerHTML = `
            No more trips to show...
            `;
            newTbody.appendChild(row);

            document.getElementById('next').disabled = true;
            tripsTable.appendChild(newTbody);
        }
        else
        {
            trips.forEach(trip => {

              const date = new Date(trip.data_partenza);
              const year = date.getFullYear();
              const month = (date.getMonth() + 1).toString().padStart(2, '0');
              const day = date.getDate().toString().padStart(2, '0');
              const datePart = `${year}-${month}-${day}`;

            const row = document.createElement('tr');
            row.innerHTML = `
            <td>${datePart}</td>
            <td>${trip.ora_partenza}</td>
            <td>${trip.contributo_economico}</td>
            <td>${trip.tempo_percorrenza}</td>
            <td>${trip.posti_disponibili - trip.posti_occupati}</td>
            <td>${trip.citta_partenza}</td>
            <td>${trip.citta_destinazione}</td>
            <td>${trip.applicazione_aperte}</td>
            <td><button onclick="getApplications(${trip.id_viaggio})">View Applications</button> <button onclick="closeApplications(${trip.id_viaggio})">Close Applications</button></td>
            `;
            newTbody.appendChild(row);
        });

        tripsTable.appendChild(newTbody);
        }
    };

    var applicationsTable = [];
    
    function displayApplications(applications) {
      var applicationsTable = document.getElementById('applicationsTable');
      if (applicationsTable) {
        applicationsTable.remove()
      }
      if(applications.length > 0){
        const table = document.createElement('table');
        applicationsTable = document.getElementById('applicationsTable');
        table.id = 'applicationsTable';
        table.innerHTML = `
        <th>Name</th>
        <th>Email</th>
        <th>Number of Passengers</th>
        <th>Options</th>
        `;
        const applicationsContainer = document.getElementById('applications-container');
        applicationsContainer.appendChild(table);
        
        applicationsTable = document.getElementById('applicationsTable');
        const newTbody = document.createElement('tbody');
        applications.forEach(application => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${application.nome} ${application.cognome}</td>
          <td>${application.email}</td>
          <td>${application.n_passeggeri}</td>
          <td><button onclick="acceptApplication(${application.id}, ${application.id_viaggio})">Accept</button>
          <button onclick="acceptApplication(${application.id}, ${application.id_viaggio}, false)">Deny</button></td>
        `;
        newTbody.appendChild(row);
      });
        applicationsTable.appendChild(newTbody);
      }
    }

    async function getUserDetails(userId) {
        ws.send(JSON.stringify({ action: 'getUserDetails', userId }));
    }

    function displayUserDetails(user) {
        const userDetailsModal = document.getElementById('userDetailsModal');
        if (!userDetailsModal) {
            const modal = document.createElement('div');
            modal.id = 'userDetailsModal';
            modal.innerHTML = `
            <h2>User Details</h2>
            <p>ID: ${user.id_utente}</p>
            <p>Name: ${user.nome} ${user.cognome}</p>
            <p>Email: ${user.email}</p>
            <p>Phone: ${user.telefono}</p>
            <button onclick="closeUserDetailsModal()">Close</button>
            `;
            document.body.appendChild(modal);
        } else {
            userDetailsModal.innerHTML = `
            <h2>User Details</h2>
            <p>ID: ${user.id_utente}</p>
            <p>Name: ${user.nome} ${user.cognome}</p>
            <p>Email: ${user.email}</p>
            <p>Phone: ${user.telefono}</p>
            <button onclick="closeUserDetailsModal()">Close</button>
            `;
        }
    }

    function closeUserDetailsModal() {
        const userDetailsModal = document.getElementById('userDetailsModal');
        userDetailsModal.remove()
    }

    async function getApplications(tripId) {
        console.info(JSON.stringify({ action: 'getApplications', tripId }));
        ws.send(JSON.stringify({ action: 'getApplications', tripId }));
    }

    document.getElementById('tripForm').onsubmit = function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        data.id_autista = "<?php echo $_SESSION['user_id']; ?>";
        // Get the selected stops
        const stopsSelect = document.getElementById('fermate_servizio');
        const selectedStops = Array.from(stopsSelect.selectedOptions, option => option.value);
        data.fermate_servizio = selectedStops;
        console.log(JSON.stringify({ action: 'createTrip', data }));

        ws.send(JSON.stringify({ action: 'createTrip', data }));
    };








//handles the search functions
async function getUserInfo(searchId) {
        ws.send(JSON.stringify({ action: 'getUserInfo', searchId }));
    }


const searchForm = document.getElementById('search-form');
const searchResults = document.getElementById('search-results');

// Add an event listener to the search form
searchForm.addEventListener('submit', (event) => {
  // Prevent the default form submission behavior
  event.preventDefault();

  // Get the search ID and type
  const searchId = document.getElementById('search-id').value;
  console.log("searching for user: " + searchId)
  // Check if the search ID is valid
  if (searchId && searchId > 0) {
    // Send a message to the WebSocket server to search for the autista
    getUserInfo(searchId);
  } else {
    // Display an error message
    searchResults.innerHTML = 'Invalid search ID';
  }
});


// Function to display search results
function displaySearchResults(data) {
  // Clear the search results container
  searchResults.innerHTML = '';

  // Display the search results
  if (data.status ==='success') {
    const userType = 'User';
    const userInfo = data.user;
    const reviews = data.reviews;
    const averageRating = data.averageRating;

    // Create a container for the autista info
    const userInfoContainer = document.createElement('div');
    userInfoContainer.innerHTML = `
      <h2>${userType} Info</h2>
      <p>ID: ${userInfo.id_utente}</p> 
      <p>Name: ${userInfo.nome} ${userInfo.cognome}</p>
      <p>Email: ${userInfo.email}</p>
    `;

    // Create a container for the reviews
    const reviewsContainer = document.createElement('div');

    // Paginate the reviews
    const reviewLimit = 3;
    let reviewOffset = 0;
    let reviewPages = Math.ceil(reviews.length / reviewLimit);

    let reviewHtml = `
      <h2>Reviews</h2>
      <ul>
    `;

    for (let i = reviewOffset; i < reviewOffset + reviewLimit && i < reviews.length; i++) {
      reviewHtml += `
        <li>
          <p>Rating: ${reviews[i].voto}/5</p>
          <p>Review: ${reviews[i].giudizio}</p>
          <p>Date: ${reviews[i].data_feedback}</p>
        </li>
      `;
    }

    reviewHtml += `
      </ul>
    `;

    reviewsContainer.innerHTML = reviewHtml;

    // Create a container for the average rating
    const averageRatingContainer = document.createElement('div');
    averageRatingContainer.innerHTML = `
      <h2>Average Rating</h2>
      <p>${averageRating}/5</p>
    `;

    // Create a container for the review form
    const reviewFormContainer = document.createElement('div');
    reviewFormContainer.innerHTML = `
      <h2>Write a Review</h2>
      <form id="review-form">
        <label for="rating">Rating:</label>
        <input type="number" id="rating" name="rating" min="1" max="5" required>
        <br>
        <label for="review">Review:</label>
        <textarea id="review" name="review" required></textarea>
        <br>
        <input type="submit" value="Post Review">
      </form>
    `;

    // Add the containers to the search results container
    searchResults.appendChild(userInfoContainer);
    searchResults.appendChild(reviewFormContainer);
    searchResults.appendChild(averageRatingContainer);
    searchResults.appendChild(reviewsContainer);

    // Add pagination buttons
    const paginationContainer = document.createElement('div');
    paginationContainer.innerHTML = `
      <button id="prev-review" ${reviewOffset === 0? 'disabled' : ''}>Previous</button>
      <button id="next-review" ${reviewOffset + reviewLimit >= reviews.length? 'disabled' : ''}>Next</button>
    `;

    searchResults.appendChild(paginationContainer);

    // Add event listeners to pagination buttons
    document.getElementById('prev-review').addEventListener('click', () => {
      reviewOffset -= reviewLimit;
      displaySearchResults(data);
    });

    document.getElementById('next-review').addEventListener('click', () => {
      reviewOffset += reviewLimit;
      displaySearchResults(data);
    });

    // Add event listener to review form
    const reviewForm = document.getElementById('review-form');
    reviewForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const rating = document.getElementById('rating').value;
      const review = document.getElementById('review').value;
      const userIdP = userInfo.id_utente;
      const autistaIdP = "<?php echo $_SESSION['user_id'];?>";
      ws.send(JSON.stringify({ action: 'postReview', userIdP, autistaIdP, rating, review, forwho: "user" }));
    });
  } else {
    searchResults.innerHTML = 'No results found';
  }


}

async function acceptApplication(applicationId, tripId, accepted = true) {
  ws.send(JSON.stringify({ action: 'acceptApplication', applicationIdA: applicationId, tripIdA: tripId, accepted }));
  //window.location.reload();
  //getApplications(tripId);
}

async function closeApplications(tripId) {
  ws.send(JSON.stringify({ action: 'closeApplications', tripId }));
  window.location.reload();
}
</script>
