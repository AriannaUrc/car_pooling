<?php
session_start();
var_dump($_SESSION);

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'utente') {
    header('Location: index.php');
    exit;
}

echo "<h1>Welcome, {$_SESSION['username']}! You are logged in as an Utente.</h1>";
?>

<form action="" method="post">
    <input type="submit" name="logout" value="Logout">
</form>

<h2>Search for Trips</h2>
<form method="get" action="">
    <label for="data_partenza">Date of Departure:</label>
    <input type="date" id="data_partenza" name="data_partenza"><br>
    <label for="ora_partenza">Time of Departure:</label>
    <input type="time" id="ora_partenza" name="ora_partenza"><br>
    <label for="id_citta_partenza">City of Departure:</label>
    <select id="id_citta_partenza" name="id_citta_partenza" required>
        <option value="">Select a city</option>
    </select><br>
    <label for="id_citta_destinazione">City of Destination:</label>
    <select id="id_citta_destinazione" name="id_citta_destinazione" required>
        <option value="">Select a city</option>
    </select><br>
    <label for="posti_richiesti">People on board:</label>
    <input type="number" id="posti_richiesti" name="posti_richiesti" required><br>
    <input type="hidden" name="search" value="1">
    <input type="submit" value="Search Trips">
</form>

<h2>Available Trips</h2>

<div id="trips">
<tr>Search for your trip!</tr>
</div>
<br>
<hr>
<br>

<!-- Search form -->
<form id="search-form">
<h2>Search for a Driver</h2>
  <input type="number" id="search-id" placeholder="Enter autista ID">
  <button type="submitSearch">Search</button>
</form>

<!-- Search results container -->
<div id="search-results"></div>

<script>

    const limit = 3;
    let offset = 0;

    const ws = new WebSocket('ws://localhost:10000');

    ws.onopen = () => {
        console.log('WebSocket connected');
        ws.send(JSON.stringify({ action: 'getCities' }));
    };

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        console.log(data);
        if (data.type === 'cities') {
            populateCities(data.cities);
        } else if (data.type === 'trips') {
            displayTrips(data.trips);
        }
        else if (data.type === 'autistaRVDetails') {
            displaySearchResults(data);
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



    function displayTrips(trips) {
    const tripsDiv = document.getElementById('trips');
    if (trips.length > 0) {
        let html = '<table border="1"><tr><th>Driver ID</hr><th>Date of Departure</th><th>Time of Departure</th><th>Economic Contribution</th><th>Travel Duration (minutes)</th><th>Available Seats</th><th>City of Departure</th><th>City of Destination</th><th>Apply</th></tr>';
        trips.forEach(trip => {
            html += `<tr>
                <td>${trip.id_autista}</td>
                <td>${trip.data_partenza}</td>
                <td>${trip.ora_partenza}</td>
                <td>${trip.contributo_economico}</td>
                <td>${trip.tempo_percorrenza}</td>
                <td>${trip.posti_disponibili - trip.posti_occupati}</td>
                <td>${trip.citta_partenza}</td>
                <td>${trip.citta_destinazione}</td>
                <td><button onclick="applyToTrip(${trip.id_viaggio})">Apply</button></td>
            </tr>`;
        });
        html += `</table> <button id="prev" `
        if(offset == 0){html += ' disabled '}
        html += `>Previous</button> 
        <button id="next">Next</button>`;

        tripsDiv.innerHTML = html;

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
    } else if(offset == 0) {
        tripsDiv.innerHTML = '<p>No trips available.</p>';
    }
    else{
        let html = '<table border="1"><tr><th>Driver ID</hr><th>Date of Departure</th><th>Time of Departure</th><th>Economic Contribution</th><th>Travel Duration (minutes)</th><th>Available Seats</th><th>City of Destination</th><th>Apply</th></tr>';
        html += `</table> No more trips to show... <br><button id="prev" >Previous</button> 
        <button id="next" disabled>Next</button>`;

        tripsDiv.innerHTML = html;

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
    }
}

    async function applyToTrip(tripId) {
        userId = "<?php echo $_SESSION['user_id']; ?>";
        console.info(JSON.stringify({ action: 'applyToTrip', tripId, userId }));
        ws.send(JSON.stringify({ action: 'applyToTrip', tripId, userId }));
    }

    let data = {};

    document.querySelector('form[method="get"]').onsubmit = async function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        data = {};
        formData.forEach((value, key) => data[key] = value);
        ws.send(JSON.stringify({ action: 'searchTrips', data, limitN: limit, offsetN: offset }));
    };


    //each time you click the prev or next you send the get trips query again with different offset
    const fetchData = async () => {
        console.log("offset: " + offset);
        console.log("limit" + limit);
        ws.send(JSON.stringify({ action: 'searchTrips', data, limitN: limit, offsetN: offset }));
    };



    //handles the search functions
    async function getAutistaInfo(autistaId) {
        ws.send(JSON.stringify({ action: 'getAutistaInfo', autistaId }));
    }


    // Get the search form and results container
const searchForm = document.getElementById('search-form');
const searchResults = document.getElementById('search-results');

// Add an event listener to the search form
searchForm.addEventListener('submit', (event) => {
  // Prevent the default form submission behavior
  event.preventDefault();

  // Get the search ID and type
  const searchId = document.getElementById('search-id').value;
  console.log("searching for autista")
  // Check if the search ID is valid
  if (searchId && searchId > 0) {
    // Send a message to the WebSocket server to search for the autista
    getAutistaInfo(searchId);
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
    const userType = 'Autista';
    const userInfo = data.user || data.autista;
    const reviews = data.reviews;
    const averageRating = data.averageRating;

    // Create a container for the autista info
    const userInfoContainer = document.createElement('div');
    userInfoContainer.innerHTML = `
      <h2>${userType} Info</h2>
      <p>ID: ${userInfo.id_autista}</p> 
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
      const userIdP = "<?php echo $_SESSION['user_id'];?>";
      const autistaIdP = userInfo.id_autista;
      ws.send(JSON.stringify({ action: 'postReview', userIdP, autistaIdP, rating, review, forwho: "autisti" }));
    });
  } else {
    searchResults.innerHTML = 'No results found';
  }


}
</script>