<?php
// Start the session
session_start();
var_dump($_SESSION);

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

echo "<h1>Welcome, {$_SESSION['username']}! You are logged in as an Autista.</h1>";
?>

<form action="" method="post">
    <input type="submit" name="logout" value="Logout">
</form>

<h2>Add a New Trip</h2>
<form id="tripForm" method="post">
    <label for="data_partenza">Date of Departure:</label>
    <input type="date" id="data_partenza" name="data_partenza" required><br>
    <label for="ora_partenza">Time of Departure:</label>
    <input type="time" id="ora_partenza" name="ora_partenza" required><br>
    <label for="contributo_economico">Economic Contribution:</label>
    <input type="number" id="contributo_economico" name="contributo_economico" step="0.01" required><br>
    <label for="tempo_percorrenza">Travel Duration (minutes):</label>
    <input type="number" id="tempo_percorrenza" name="tempo_percorrenza" required><br>
    <label for="posti_disponibili">Available Seats:</label>
    <input type="number" id="posti_disponibili" name="posti_disponibili" required><br>
    <label for="id_citta_partenza">City of Departure:</label>
    <select id="id_citta_partenza" name="id_citta_partenza" required>
        <option value="">Select a city</option>
    </select><br>
    <label for="id_citta_destinazione">City of Destination:</label>
    <select id="id_citta_destinazione" name="id_citta_destinazione" required>
        <option value="">Select a city</option>
    </select><br>
    <label for="id_tipo_viaggio">Trip Type:</label>
    <select id="id_tipo_viaggio" name="id_tipo_viaggio" required>
        <option value="">Select a trip type</option>
    </select><br>
    <input type="submit" value="Add Trip">
</form>

<h2>Your Trips</h2>
<table id="tripsTable" border="1"></table>

<button id="prev" disabled>Previous</button>
<button id="next">Next</button>

<script>

    const limit = 3;
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
        } else if (data.type === 'tripCreated') {
            if (data.status === 'success') {
                alert('Trip added successfully');
                window.location.reload();
            } else {
                alert('Error adding trip: ' + data.message);
            }
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

    function populateTripTypes(tripTypes) {
        const tripTypeSelect = document.getElementById('id_tipo_viaggio');
        tripTypes.forEach(tripType => {
            const option = document.createElement('option');
            option.value = tripType.id_tipo_viaggio;
            option.textContent = tripType.tipo_viaggio;
            tripTypeSelect.appendChild(option);
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
        firstrow.innerHTML = '<th>Date of Departure</th><th>Time of Departure</th><th>Economic Contribution</th><th>Travel Duration (minutes)</th><th>Available Seats</th><th>City of Departure</th><th>City of Destination</th><th>Trip Type</th><th>Registrations</th>';
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
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${trip.data_partenza}</td>
                <td>${trip.ora_partenza}</td>
                <td>${trip.contributo_economico}</td>
                <td>${trip.tempo_percorrenza}</td>
                <td>${trip.posti_disponibili}</td>
                <td>${trip.citta_partenza}</td>
                <td>${trip.citta_destinazione}</td>
                <td>${trip.tipo_viaggio}</td>
                <td><button onclick="getApplications(${trip.id_viaggio})">View Applications</button></td>
            `;
            newTbody.appendChild(row);
        });

        tripsTable.appendChild(newTbody);
        }
        
        
    }

    async function getApplications(tripId) {
        console.info(JSON.stringify({ action: 'getApplications', tripId }));
        ws.send(JSON.stringify({ action: 'getApplications', tripId }));
    }

    document.getElementById('tripForm').onsubmit = async function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        data.id_autista = "<?php echo $_SESSION['user_id']; ?>";
        console.info(JSON.stringify({ action: 'createTrip', data }));
        ws.send(JSON.stringify({ action: 'createTrip', data }));
    };
</script>