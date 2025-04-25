const WebSocket = require('ws');
const mysql = require('mysql2');
const bcrypt = require('bcryptjs');

const port = 10000;

// WebSocket server setup
const wss = new WebSocket.Server({ port: port });

// MySQL database connection setup
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'car_pooling'
});

db.connect((err) => {
  if (err) throw err;
  console.log('Connected to the MySQL database');
});

// Handle WebSocket connections
wss.on('connection', (ws, req) => {
  console.log("Connected " + req.remoteAddress);

  ws.on('message', async (message) => {
    console.log("Message received");
    const data = JSON.parse(message);
    switch (data.action) {
      case 'login':
        // Handle login
        handleLogin(data, ws);
        break;

      case 'register':
        // Handle registration
        handleRegister(data, ws);
        break;

      case 'getApplications':
        // Handle get applications
        const { tripId } = data;
        console.log(req.remoteAddress + ": applicants for " + tripId);
        handleGetApplications(data, ws);
        break;

      case 'applyToTrip':
        // Handle apply to trip
        handleApplyToTrip(data, ws);
        break;

      case 'getCities':
        const cities = await queryDatabase('SELECT id_citta, nome_citta FROM citta');
        ws.send(JSON.stringify({ type: 'cities', cities }));
        break;

      case 'getTripTypes':
        const tripTypes = await queryDatabase('SELECT id_tipo_viaggio, tipo_viaggio FROM tipo_viaggio');
        ws.send(JSON.stringify({ type: 'tripTypes', tripTypes }));
        break;

      case 'searchTrips':
        const trips = await searchTrips(data.data, data.limitN, data.offsetN);
        ws.send(JSON.stringify({ type: 'trips', trips }));
        break;

      case 'getTrips':
        const {userId, offsetN, limitN} = data;
        const userTrips = await getUserTrips(userId, offsetN, limitN);
        ws.send(JSON.stringify({ type: 'trips', trips: userTrips }));
        break;

      case 'createTrip':
        const { data: tripData } = data;
        const tripResult = await createTrip(tripData);
        ws.send(JSON.stringify({ type: 'tripCreated', ...tripResult }));
        break;
    }
  });

  // Handle client disconnection
  ws.on('close', () => {
    console.log('Client disconnected');
  });
});

const handleLogin = (data, ws) => {
  const { username, password, role } = data;

  // Input validation
  if (!username || !password || !role) {
    ws.send(JSON.stringify({ status: 'failure', message: 'Missing required fields' }));
    return;
  }

  let query = '';

  // Set query based on role
  if (role === 'autista') {
    query = 'SELECT * FROM autisti WHERE nome_utente = ?';
  } else if (role === 'utente') {
    query = 'SELECT * FROM utenti WHERE nome_utente = ?';
  } else {
    ws.send(JSON.stringify({ status: 'failure', message: 'Invalid role' }));
    return;
  }

  // Execute query to check user existence
  db.execute(query, [username], async (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    // User not found
    if (results.length === 0) {
      ws.send(JSON.stringify({ status: 'failure', message: 'User not found' }));
      return;
    }

    const user_tmp = results[0];
    console.log(user_tmp);
    const userId = role === 'autista' ? user_tmp.id_autista : user_tmp.id_utente;

    // Check password hash
    try {
      const passwordMatch = await bcrypt.compare(password, user_tmp.password);
      
      if (passwordMatch) {
        // Successful login
        console.log(JSON.stringify({
          status: 'success',
          message: 'Login successful',
          user: {
            id: userId,
            username: user_tmp.nome_utente,
            role: role,
          }
        }));

        ws.send(JSON.stringify({
          status: 'success',
          message: 'Login successful',
          user: {
            id: userId,
            username: user_tmp.nome_utente,
            role: role,
          }
        }));
      } else {
        // Incorrect password
        ws.send(JSON.stringify({ status: 'failure', message: 'Incorrect password' }));
      }
    } catch (err) {
      console.error('Error comparing password:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error processing password' }));
    }
  });
};

// Handle registration action
const handleRegister = (data, ws) => {
  const { username, password, role, nome, cognome, email, telefono, numero_patente } = data;

  // Hash the password for security
  bcrypt.hash(password, 10, (err, hashedPassword) => {
    if (err) {
      ws.send(JSON.stringify({ status: 'failure', message: 'Error hashing password' }));
      return;
    }

    let query = '';
    let values = [];

    if (role === 'autista') {
      query = 'INSERT INTO autisti (nome_utente, password, nome, cognome, email, recapito_telefonico, numero_patente) VALUES (?, ?, ?, ?, ?, ?, ?)';
      values = [username, hashedPassword, nome, cognome, email, telefono, numero_patente];
    } else if (role === 'utente') {
      query = 'INSERT INTO utenti (nome_utente, password, nome, cognome, email, telefono) VALUES (?, ?, ?, ?, ?, ?)';
      values = [username, hashedPassword, nome, cognome, email, telefono];
    }

    db.execute(query, values, (err, result) => {
      if (err) {
        ws.send(JSON.stringify({ status: 'failure', message: 'Error registering user' }));
        return;
      }

      ws.send(JSON.stringify({ status: 'success', message: 'Registration successful' }));
    });
  });
};

const handleGetApplications = (data, ws) => {
  const { tripId } = data;
  console.log("applicants for " + tripId);

  const query = 'SELECT * FROM applicazioni WHERE id_viaggio = ?';

  // Execute query to check user existence
  db.execute(query, [tripId], (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    console.log(results);
    // Send back the results
    ws.send(JSON.stringify({ results }));
  });
};

const handleApplyToTrip = (data, ws) => {
  const { tripId, userId } = data;
  console.log("make application for " + tripId + " from " + userId);

  const query = 'INSERT INTO applicazioni (id_utente, id_viaggio, n_passeggeri) VALUES (?, ?, ?)';

  // Execute query to check user existence
  db.execute(query, [userId, tripId, 1], (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    console.log(results);
    // Send back the results
    ws.send(JSON.stringify({ status: 'success', message: 'Application submitted successfully' }));
  });
};

const getUserTrips = (userId, offset, limit) => {
  console.log(limit)
  console.log(offset)
  const baseQuery = 'SELECT v.*, c1.nome_citta AS citta_partenza, c2.nome_citta AS citta_destinazione, tv.tipo_viaggio ' +
                  'FROM viaggi v ' +
                  'JOIN citta c1 ON v.id_citta_partenza = c1.id_citta ' +
                  'JOIN citta c2 ON v.id_citta_destinazione = c2.id_citta ' +
                  'LEFT JOIN tipo_viaggio tv ON v.id_tipo_viaggio = tv.id_tipo_viaggio ' +
                  'WHERE v.id_autista = ? ' + 
                  'LIMIT ? ' +
                  'OFFSET ? ';
  return queryDatabase(baseQuery, [userId, limit, offset]);
};

const createTrip = (tripData) => {
  const { id_autista, data_partenza, ora_partenza, contributo_economico, tempo_percorrenza, posti_disponibili, id_citta_partenza, id_citta_destinazione, id_tipo_viaggio } = tripData;

  const query = 'INSERT INTO viaggi (id_autista, data_partenza, ora_partenza, contributo_economico, tempo_percorrenza, posti_disponibili, id_citta_partenza, id_citta_destinazione, id_tipo_viaggio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
  const values = [id_autista, data_partenza, ora_partenza, contributo_economico, tempo_percorrenza, posti_disponibili, id_citta_partenza, id_citta_destinazione, id_tipo_viaggio];

  return new Promise((resolve, reject) => {
    db.execute(query, values, (err, result) => {
      if (err) {
        console.error('Database connection error:', err);
        reject({ status: 'failure', message: 'Error creating trip' });
        return;
      }

      console.log(result);
      resolve({ status: 'success', message: 'Trip created successfully' });
    });
  });
};

function queryDatabase(query, params = []) {
  return new Promise((resolve, reject) => {
    db.query(query, params, (err, results) => {
      if (err) reject(err);
      resolve(results);
    });
  });
}

async function searchTrips(data, limit, offset) {
  let baseQuery = 'SELECT v.*, c1.nome_citta AS citta_partenza, c2.nome_citta AS citta_destinazione, tv.tipo_viaggio ' +
                  'FROM viaggi v ' +
                  'JOIN citta c1 ON v.id_citta_partenza = c1.id_citta ' +
                  'JOIN citta c2 ON v.id_citta_destinazione = c2.id_citta ' +
                  'LEFT JOIN tipo_viaggio tv ON v.id_tipo_viaggio = tv.id_tipo_viaggio ' +
                  'WHERE v.id_citta_partenza = ? AND v.id_citta_destinazione = ?';
  let params = [data.id_citta_partenza, data.id_citta_destinazione];
  let types = 'ii';

  if (data.data_partenza) {
    baseQuery += ' AND v.data_partenza >= ?';
    params.push(data.data_partenza);
    types += 's';

    if (data.ora_partenza) {
      baseQuery += ' AND v.ora_partenza >= ?';
      params.push(data.ora_partenza);
      types += 's';
    }
  }

  baseQuery += ' ORDER BY v.data_partenza, v.ora_partenza LIMIT '+ limit + ' OFFSET ' + offset;

  return await queryDatabase(baseQuery, params);
}