const WebSocket = require('ws');
const mysql = require('mysql2');
const bcrypt = require('bcryptjs');
const express = require('express');
const bodyParser = require('body-parser');

const app = express();
app.use(bodyParser.json());

// WebSocket server setup
const ws = new WebSocket.Server({ port: 8080 });

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
ws.on('connection', (ws) =>  console.log('Client connected'));

ws.on('message', async (message) => {
  const data = JSON.parse(message);
  
  if (data.action === 'login') {
    // Handle login
    handleLogin(data, ws);
  } else if (data.action === 'register') {
    // Handle registration
    handleRegister(data, ws);
  } else if (data.action === 'getApplications') {
    // Handle get applications
    handleGetApplications(data, ws);
  } else if (data.action === 'applyToTrip') {
    // Handle apply to trip
    handleApplyToTrip(data, ws);
  }
});

// Handle client disconnection
ws.on('close', () => {
  console.log('Client disconnected');
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
  console.log(user_tmp)
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



app.listen(3000, () => {
console.log('Express server running on http://localhost:3000');
});