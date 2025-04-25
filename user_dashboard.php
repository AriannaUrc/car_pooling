<?php
// Start the session
session_start();
var_dump($_SESSION);

if (isset($_POST['logout'])) {
    //var_dump("logged out");
    session_destroy();
    header("Location: index.php");
    exit;
}

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'utente') {
    header('Location: index.php');
    exit;
}

echo "<h1>Welcome, {$_SESSION['username']}! You are logged in as an Utente.</h1>";

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_pooling";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available cities for the search form
$citiesQuery = "SELECT id_citta, nome_citta FROM citta";
$citiesResult = $conn->query($citiesQuery);
$cities = $citiesResult->fetch_all(MYSQLI_ASSOC);

// Handle trip search
$trips = [];
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $id_citta_partenza = $_GET['id_citta_partenza'];
    $id_citta_destinazione = $_GET['id_citta_destinazione'];
    $data_partenza = isset($_GET['data_partenza']) ? $_GET['data_partenza'] : null;
    $ora_partenza = isset($_GET['ora_partenza']) ? $_GET['ora_partenza'] : null;

    // Validate mandatory fields
    if (empty($id_citta_partenza) || empty($id_citta_destinazione)) {
        echo "<p>Please select both starting and arrival cities.</p>";
    } else {
        $baseQuery = "SELECT v.*, c1.nome_citta AS citta_partenza, c2.nome_citta AS citta_destinazione, tv.tipo_viaggio 
                      FROM viaggi v 
                      JOIN citta c1 ON v.id_citta_partenza = c1.id_citta 
                      JOIN citta c2 ON v.id_citta_destinazione = c2.id_citta 
                      LEFT JOIN tipo_viaggio tv ON v.id_tipo_viaggio = tv.id_tipo_viaggio 
                      WHERE v.id_citta_partenza = ? AND v.id_citta_destinazione = ?";

        $params = [$id_citta_partenza, $id_citta_destinazione];
        $types = "ii";

        if ($data_partenza) {
            $baseQuery .= " AND v.data_partenza >= ?";
            $params[] = $data_partenza;
            $types .= "s";

            if ($ora_partenza) {
                $baseQuery .= " AND v.ora_partenza >= ?";
                $params[] = $ora_partenza;
                $types .= "s";
            }
        }

        $baseQuery .= " ORDER BY v.data_partenza, v.ora_partenza";

        $stmt = $conn->prepare($baseQuery);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $tripsResult = $stmt->get_result();
        $trips = $tripsResult->fetch_all(MYSQLI_ASSOC);
    }
}

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
        <?php foreach ($cities as $city): ?>
            <option value="<?php echo $city['id_citta']; ?>"><?php echo $city['nome_citta']; ?></option>
        <?php endforeach; ?>
    </select><br>
    <label for="id_citta_destinazione">City of Destination:</label>
    <select id="id_citta_destinazione" name="id_citta_destinazione" required>
        <option value="">Select a city</option>
        <?php foreach ($cities as $city): ?>
            <option value="<?php echo $city['id_citta']; ?>"><?php echo $city['nome_citta']; ?></option>
        <?php endforeach; ?>
    </select><br>
    <input type="hidden" name="search" value="1">
    <input type="submit" value="Search Trips">
</form>

<h2>Available Trips</h2>
<table border="1">
    <tr>
        <th>Date of Departure</th>
        <th>Time of Departure</th>
        <th>Economic Contribution</th>
        <th>Travel Duration (minutes)</th>
        <th>Available Seats</th>
        <th>City of Departure</th>
        <th>City of Destination</th>
        <th>Trip Type</th>
        <th>Apply</th>
    </tr>
    <?php foreach ($trips as $trip): ?>
        <tr>
            <td><?php echo $trip['data_partenza']; ?></td>
            <td><?php echo $trip['ora_partenza']; ?></td>
            <td><?php echo $trip['contributo_economico']; ?></td>
            <td><?php echo $trip['tempo_percorrenza']; ?></td>
            <td><?php echo $trip['posti_disponibili']; ?></td>
            <td><?php echo $trip['citta_partenza']; ?></td>
            <td><?php echo $trip['citta_destinazione']; ?></td>
            <td><?php echo $trip['tipo_viaggio']; ?></td>
            <td>
                <button onclick="applyToTrip(<?php echo $trip['id_viaggio']; ?>)">Apply</button>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<script>

    const ws = new WebSocket('ws://localhost:10000');

    ws.onopen = () => {
        console.log('WebSocket connected');
    };

    ws.onmessage = (event) => {
        
        const data = JSON.parse(event.data);
        console.log(data)
    };

    async function applyToTrip(tripId) {
        //send to the server a request to get all applications for a specific trip
        userId = "<?php echo $_SESSION['user_id']; ?>";

        console.info(JSON.stringify({ action: 'applyToTrip', tripId, userId}));
        ws.send(JSON.stringify({ action: 'applyToTrip', tripId, userId}));
    }
</script>

<?php
$conn->close();
?>