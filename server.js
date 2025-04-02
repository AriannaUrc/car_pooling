const WebSocket = require('ws');
const mysql = require('mysql2');
const bcrypt = require('bcryptjs');

// WebSocket server setup
const wss = new WebSocket.Server({ port: 8080 });

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
wss.on('connection', (ws) => {
  console.log('Client connected');

  ws.on('message', async (message) => {
    const data = JSON.parse(message);
    
    if (data.action === 'login') {
      // Handle login
      handleLogin(data, ws);
    } else if (data.action === 'register') {
      // Handle registration
      handleRegister(data, ws);
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
  
      const user = results[0];
  
      // Check password hash
      try {
        const passwordMatch = await bcrypt.compare(password, user.password);
        
        if (passwordMatch) {
          // Successful login
          ws.send(JSON.stringify({
            status: 'success',
            message: 'Login successful',
            user: {
              id: user.id,
              username: user.nome_utente,
              role: user.role, // Assuming role is stored in the user table
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

console.log('WebSocket server running on ws://localhost:8080');
