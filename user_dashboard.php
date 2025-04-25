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
    <input type="hidden" name="search" value="1">
    <input type="submit" value="Search Trips">
</form>

<h2>Available Trips</h2>

<div id="trips">
<tr>Search for your trip!</tr>
</div>

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
        let html = '<table border="1"><tr><th>Date of Departure</th><th>Time of Departure</th><th>Economic Contribution</th><th>Travel Duration (minutes)</th><th>Available Seats</th><th>City of Departure</th><th>City of Destination</th><th>Trip Type</th><th>Apply</th></tr>';
        trips.forEach(trip => {
            html += `<tr>
                <td>${trip.data_partenza}</td>
                <td>${trip.ora_partenza}</td>
                <td>${trip.contributo_economico}</td>
                <td>${trip.tempo_percorrenza}</td>
                <td>${trip.posti_disponibili}</td>
                <td>${trip.citta_partenza}</td>
                <td>${trip.citta_destinazione}</td>
                <td>${trip.tipo_viaggio}</td>
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
        let html = '<table border="1"><tr><th>Date of Departure</th><th>Time of Departure</th><th>Economic Contribution</th><th>Travel Duration (minutes)</th><th>Available Seats</th><th>City of Departure</th><th>City of Destination</th><th>Trip Type</th><th>Apply</th></tr>';
        html += '<tr>No more trips to show...</tr>'
        html += `</table> <button id="prev" >Previous</button> 
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
</script>