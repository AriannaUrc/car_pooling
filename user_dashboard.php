<?php
ob_start();
session_start();
//var_dump($_SESSION);

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'utente') {
    header('Location: index.php');
    exit;
}

?>





<div class="header">
    <h1><?php echo $_SESSION['username'];?>: USER</h1>
    <form action="" method="post">
      <input type="submit" name="logout" value="Logout" class="logout-button">
    </form>
  </div>
  <div class="row">
    <div class="col-left">
      <div class="search-form">
        <h2>Search for Trips</h2>
        <form method="get" action="">
          <label for="data_partenza">Date of Departure:</label>
          <input type="date" id="data_partenza" name="data_partenza"><br>
          <label for="ora_partenza">Time of Departure:</label>
          <input type="time" id="ora_partenza" name="ora_partenza"><br>
          <label for="animali_allowed">Allow Animals:</label>
          <input type="checkbox" id="animali_allowed" name="animali_allowed"><br>
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
          <br>
          <input type="submit" value="Search Trips">
        </form>
      </div>
      <br>
      <div class="trips-container">
        <h2>Available Trips</h2>
        <div id="trips">
          <p>Search for your trip!</p>
        </div>
      </div>
    </div>
    <div class="col-right">
      <div class="notifications-container">
        <h2>Notifications</h2>
        <div id="notifications-list" class="notifications-list"></div>
      </div>
      <div class="search-autista-form">
        <h2>Search for a Driver</h2>
        <form id="search-form">
          <input type="number" id="search-id" placeholder="Enter autista ID">
          <button type="submit">Search</button>
        </form>
      </div>
      <div class="search-results-container">
        <div id="search-results"></div>
      </div>
    </div>
  </div>
</div>

<style>
body {
    font-family: Arial, sans-serif;
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

.logout-button:hover {
    background-color: #cc0000;
}

.search-form {
    margin-top: 40px;
}

.search-form label {
    display: block;
    margin-bottom: 10px;
}

.search-form input[type="date"],.search-form input[type="time"],.search-form select {
    width: 100%;
    height: 40px;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ccc;
}

.search-form input[type="checkbox"] {
    margin-bottom: 20px;
}

.search-form input[type="submit"] {
    width: 100px;
    height: 40px;
    background-color: #333;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.search-form input[type="submit"]:hover {
    background-color: #444;
}

.trips-container {
    margin-top: 40px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

table th {
    background-color: #f0f0f0;
}

.pagination-button {
    margin-top: 10px;
}

.notifications-container {
    margin-top: 40px;
}

.notifications-list {
    height: 250px;
    overflow-y: auto;
    padding: 0 10px;
    border: 1px solid #ddd;
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #ccc;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item.message {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}

.notification-item.date {
    color: #999;
    font-size: 14px;
}

.read {
    background-color: #f0f0f0;
}

.search-autista-form {
    margin-top: 40px;
}

.search-autista-form input[type="number"] {
    width: 100%;
    height: 40px;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ccc;
}

.search-autista-form button[type="submit"] {
    width: 100px;
    height: 40px;
    background-color: #333;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.search-autista-form button[type="submit"]:hover {
    background-color: #444;
}

.search-results-container {
    margin-top: 40px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 20px;
}

.col-left {
    width: 60%;
    margin-right: 20px;
}

.col-right {
    width: 35%;
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

.review-container {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}

.review-list {
    width: 60%;
}

.review-average {
    width: 30%;
    text-align: center;
}

.review-form {
  width: 100%;
    margin-top: 20px;
    padding: 20px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.review-form label {
    display: block;
    margin-bottom: 10px;
}

.review-form input[type="number"] {
    width: 100%;
    height: 40px;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ccc;
}

.review-form textarea {
    width: 100%;
    height: 100px;
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ccc;
}

.review-form input[type="submit"] {
    width: 100px;
    height: 40px;
    background-color: #333;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.review-form input[type="submit"]:hover {
    background-color: #444;
}

#prev-review, #next-review {
    margin-top: 10px;
    background-color: #333;
    color: #fff;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    width: 80px;
}

#prev-review:hover, #next-review:hover {
    background-color: #444;
}

#prev-review:disabled, #next-review:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.notification-item.message {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}

.notification-item.date {
    color: #666;
    font-size: 14px;
}

.rating-container {
    margin-top: 10px;
}

.star-rating {
    font-size: 20px;
    color: #ffd700;
}

.star-rating span {
    cursor: pointer;
}

@media only screen and (max-width: 768px) {
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

.review-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.review-list li {
    padding: 10px;
    border-bottom: 1px solid #ccc;
}

.review-list li:last-child {
    border-bottom: none;
}


#tripsTable {
  width: 100%;
  border-collapse: collapse;
}

#tripsTable th, #tripsTable td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: left;
}

#tripsTable th {
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

@media only screen and (max-width: 768px) {
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

    const limit = 3;
    let offset = 0;

    const ws = new WebSocket('ws://localhost:10000');

    ws.onopen = () => {
        console.log('WebSocket connected');
        ws.send(JSON.stringify({ action: 'getCities' }));
        ws.send(JSON.stringify({ action: 'getNotifications', userId : <?php echo $_SESSION['user_id']?> }));
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
        else if(data.type === 'notifications'){
          displayNotifications(data.notifications)
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



function displayNotifications(notifications) {
    const notificationsList = document.getElementById('notifications-list');
    notificationsList.innerHTML = ''; // Clear existing notifications

    notifications.forEach(notification => {
        const notificationItem = document.createElement('div');
        notificationItem.classList.add('notification-item');
        if (notification.stato ==='read') {
            notificationItem.classList.add('read');
        }

        const messageElement = document.createElement('span');
        messageElement.classList.add('message');
        messageElement.textContent = notification.messaggio;

        const dateElement = document.createElement('span');
        dateElement.classList.add('date');
        dateElement.textContent = new Date(notification.data_notifica).toLocaleString();

        notificationItem.appendChild(messageElement);
        notificationItem.appendChild(document.createElement('br'));
        notificationItem.appendChild(dateElement);

        notificationsList.appendChild(notificationItem);
    });
}
</script>
