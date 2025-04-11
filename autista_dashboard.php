<?php
// Start the session
session_start();
var_dump($_SESSION);

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'autista') {
    header('Location: index.php');
    exit;
}

echo "<h1>Welcome, {$_SESSION['username']}! You are logged in as an Autista.</h1>";

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_pooling";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available cities for the trip form
$citiesQuery = "SELECT id_citta, nome_citta FROM citta";
$citiesResult = $conn->query($citiesQuery);
$cities = $citiesResult->fetch_all(MYSQLI_ASSOC);

// Fetch trip types for the trip form
$tripTypesQuery = "SELECT id_tipo_viaggio, tipo_viaggio FROM tipo_viaggio";
$tripTypesResult = $conn->query($tripTypesQuery);
$tripTypes = $tripTypesResult->fetch_all(MYSQLI_ASSOC);

// Handle trip creation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_autista = $_SESSION['user_id'];
    $data_partenza = $_POST['data_partenza'];
    $ora_partenza = $_POST['ora_partenza'];
    $contributo_economico = $_POST['contributo_economico'];
    $tempo_percorrenza = $_POST['tempo_percorrenza'];
    $posti_disponibili = $_POST['posti_disponibili'];
    $id_citta_partenza = $_POST['id_citta_partenza'];
    $id_citta_destinazione = $_POST['id_citta_destinazione'];
    $id_tipo_viaggio = $_POST['id_tipo_viaggio'];

    $insertTripQuery = "INSERT INTO viaggi (id_autista, data_partenza, ora_partenza, contributo_economico, tempo_percorrenza, posti_disponibili, id_citta_partenza, id_citta_destinazione, id_tipo_viaggio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertTripQuery);
    $stmt->bind_param("issiiiiii", $id_autista, $data_partenza, $ora_partenza, $contributo_economico, $tempo_percorrenza, $posti_disponibili, $id_citta_partenza, $id_citta_destinazione, $id_tipo_viaggio);

    if ($stmt->execute()) {
        // Redirect to the same page to prevent resubmission on refresh
        header("Location: autista_dashboard.php");
        exit();
    } else {
        echo "<p>Error adding trip: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Fetch trips created by the current driver
$tripsQuery = "SELECT v.*, c1.nome_citta AS citta_partenza, c2.nome_citta AS citta_destinazione, tv.tipo_viaggio 
               FROM viaggi v 
               JOIN citta c1 ON v.id_citta_partenza = c1.id_citta 
               JOIN citta c2 ON v.id_citta_destinazione = c2.id_citta 
               LEFT JOIN tipo_viaggio tv ON v.id_tipo_viaggio = tv.id_tipo_viaggio 
               WHERE v.id_autista = ?";
$tripsStmt = $conn->prepare($tripsQuery);
$tripsStmt->bind_param("i", $_SESSION['user_id']);
$tripsStmt->execute();
$tripsResult = $tripsStmt->get_result();
$trips = $tripsResult->fetch_all(MYSQLI_ASSOC);

?>

<h2>Add a New Trip</h2>
<form method="post" action="">
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
        <?php foreach ($cities as $city): ?>
            <option value="<?php echo $city['id_citta']; ?>"><?php echo $city['nome_citta']; ?></option>
        <?php endforeach; ?>
    </select><br>
    <label for="id_citta_destinazione">City of Destination:</label>
    <select id="id_citta_destinazione" name="id_citta_destinazione" required>
        <?php foreach ($cities as $city): ?>
            <option value="<?php echo $city['id_citta']; ?>"><?php echo $city['nome_citta']; ?></option>
        <?php endforeach; ?>
    </select><br>
    <label for="id_tipo_viaggio">Trip Type:</label>
    <select id="id_tipo_viaggio" name="id_tipo_viaggio" required>
        <?php foreach ($tripTypes as $tripType): ?>
            <option value="<?php echo $tripType['id_tipo_viaggio']; ?>"><?php echo $tripType['tipo_viaggio']; ?></option>
        <?php endforeach; ?>
    </select><br>
    <input type="submit" value="Add Trip">
</form>

<h2>Your Trips</h2>
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
        <th>Applications</th>
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
                <button onclick="getApplications(<?php echo $trip['id_viaggio']; ?>)">View Applications</button>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<script>
    async function getApplications(tripId) {
        //TODO
    }
</script>

<?php
$conn->close();
?>