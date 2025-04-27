const WebSocket = require('ws');
const mysql = require('mysql2');
const bcrypt = require('bcryptjs');

const port = 10000;

// WebSocket server setup
const wss = new WebSocket.Server({ port: port });
// Store all connected clients
const clients = new Set();

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
  //console.log(wss);
  clients.add(wss);
});

// Handle WebSocket connections
wss.on('connection', (ws, req) => {
  console.log("Connected " + req);
  // Retrieve unread notifications for the user


  ws.on('message', async (message) => {
    const data = JSON.parse(message);
    console.log("Message received: " + data.action);
    switch (data.action) {
      case 'login':
        // Handle login
        handleLogin(data, ws);
        break;

      case 'register':
        // Handle registration
        handleRegister(data, ws);
        break;
      
      case 'getNotifications':
        console.log("get user notification")
        console.log(data)
        getNotifications(data.userId, ws);
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

      case 'getStops':
        const stops = await queryDatabase('SELECT id, nome FROM stops');
        ws.send(JSON.stringify({ type: 'stops', stops }));
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

      case 'getUserDetails':
          console.log("got asked for user details")
          handleGetUserDetails(data, ws);
        break;

      case 'getAutistaInfo':
        const { autistaId } = data;
        console.log("searching for autista")
        getAutistaReviewDetails(autistaId, ws)
        break;

      case 'getUserInfo':
        const { searchId } = data;
        console.log("searching for user: " + searchId)
        getUserReviewDetails(searchId, ws);
      break;

      case 'postReview':
        const { userIdP, autistaIdP, rating, review, forwho } = data;
        var query = "";
        if(forwho == "user")
        query = 'INSERT INTO feedback_utenti_autisti (id_autista, id_utente, voto, giudizio) VALUES (?,?,?,?)';
        else
        query = 'INSERT INTO feedback_autisti_utenti (id_autista, id_utente, voto, giudizio) VALUES (?,?,?,?)';

        console.log(autistaIdP + " " + userIdP+ " " + rating + " " + review)
        db.execute(query, [autistaIdP, userIdP, rating, review], (err, results) => {
          if (err) {
            console.error('Database connection error:', err);
            ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
            return;
          }
          ws.send(JSON.stringify({ status:'success', message: 'Review posted successfully' }));
        });
        break;

      case 'acceptApplication':
      const { applicationIdA, tripIdA } = data;
      console.log(applicationIdA, tripIdA)
      handleAcceptApplication(applicationIdA, tripIdA, ws);
      break;

      case 'closeApplications':
        const { tripId: tripIdClose } = data;
        handleCloseApplications(tripIdClose, ws);
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

  const query = `
    SELECT a.*, u.nome, u.cognome, u.email
    FROM applicazioni a
    JOIN utenti u ON a.id_utente = u.id_utente
    WHERE a.id_viaggio =? AND a.stato = 'in_attesa'
  `;

  // Execute query to check user existence
  db.execute(query, [tripId], (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    console.log(results);
    // Send back the results
    ws.send(JSON.stringify({ type: 'applications', results }));
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

const getUserTrips = (userId, offset, limit) => { //get all the trips of one driver
  console.log(limit)
  console.log(offset)
  const baseQuery = 'SELECT v.*, c1.nome_citta AS citta_partenza, c2.nome_citta AS citta_destinazione ' +
                  'FROM viaggi v ' +
                  'JOIN citta c1 ON v.id_citta_partenza = c1.id_citta ' +
                  'JOIN citta c2 ON v.id_citta_destinazione = c2.id_citta ' +
                  'WHERE v.id_autista = ? ' + 
                  'LIMIT ? ' +
                  'OFFSET ? ';
  return queryDatabase(baseQuery, [userId, limit, offset]);
};

const createTrip = (tripData) => {
  const {
    id_autista,
    data_partenza,
    ora_partenza,
    contributo_economico,
    tempo_percorrenza,
    fermate_servizio,
    animali_allowed,
    posti_disponibili,
    id_citta_partenza,
    id_citta_destinazione,
  } = tripData;

  // Convert animali_allowed to boolean
  const animali = animali_allowed === 'on';

  // Insert trip into viaggi table
  const query = 'INSERT INTO viaggi (id_autista, data_partenza, ora_partenza, contributo_economico, tempo_percorrenza, animali, posti_disponibili, id_citta_partenza, id_citta_destinazione) VALUES (?,?,?,?,?,?,?,?,?)';
  const values = [
    id_autista,
    data_partenza,
    ora_partenza,
    contributo_economico,
    tempo_percorrenza,
    animali,
    posti_disponibili,
    id_citta_partenza,
    id_citta_destinazione,
  ];

  return new Promise((resolve, reject) => {
    db.execute(query, values, (err, result) => {
      if (err) {
        console.error('Database connection error:', err);
        reject({ status: 'failure', message: 'Error creating trip' });
        return;
      }

      const tripId = result.insertId;

      // Insert stops into stops-viaggi table
      var stopsQuery = 'INSERT INTO `stops-viaggi` (id_viaggio, id_stop) VALUES ';
      var stopsValues = [];

      const placeholders = fermate_servizio.map((stop, index) => {
        stopsValues.push(tripId, stop);
        return `(?,?)`;
      }).join(', ');

      stopsQuery += placeholders;

      db.execute(stopsQuery, stopsValues, (err) => {
        if (err) {
          console.error('Database connection error:', err);
          reject({ status: 'failure', message: 'Error adding stops to trip' });
          return;
        }

        resolve({ status:'success', message: 'Trip created successfully' });
      });
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
  let baseQuery = 'SELECT v.*, c1.nome_citta AS citta_partenza, c2.nome_citta AS citta_destinazione, v.id_autista ' +
                  'FROM viaggi v ' +
                  'JOIN citta c1 ON v.id_citta_partenza = c1.id_citta ' +
                  'JOIN citta c2 ON v.id_citta_destinazione = c2.id_citta ' +
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

  if(data.animali_allowed=='on'){
    baseQuery+= 'AND v.animali = true '
  }
  else{
    baseQuery+= 'AND v.animali = false '
  }

  baseQuery += ' AND v.posti_disponibili - posti_occupati >= ?';
  baseQuery += ' AND v.applicazione_aperte  = ?';

  params.push(data.posti_richiesti, true);

  baseQuery += ' ORDER BY v.data_partenza, v.ora_partenza LIMIT '+ limit + ' OFFSET ' + offset;

  return await queryDatabase(baseQuery, params);
}


const handleGetUserDetails = (data, ws) => {
  const { userId } = data;
  console.log("Getting user details for " + userId);

  const query = 'SELECT * FROM utenti WHERE id_utente =?';

  // Execute query to check user existence
  db.execute(query, [userId], (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    console.log(results);
    // Send back the results
    ws.send(JSON.stringify({ type: 'userDetails', user: results[0] }));
  });
};


function getAutistaReviewDetails(autistaId, ws) {
  const autistaQuery = 'SELECT * FROM autisti WHERE id_autista =?';
  db.execute(autistaQuery, [autistaId], (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    const autista = results[0];
    const reviewQuery = 'SELECT * FROM feedback_autisti_utenti WHERE id_autista =?';
    db.execute(reviewQuery, [autistaId], (err, reviewResults) => {
      if (err) {
        console.error('Database connection error:', err);
        ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
        return;
      }

      const reviews = reviewResults;
      const averageRatingQuery = 'SELECT AVG(voto) as averageRating FROM feedback_autisti_utenti WHERE id_autista =?';
      db.execute(averageRatingQuery, [autistaId], (err, averageRatingResults) => {
        if (err) {
          console.error('Database connection error:', err);
          ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
          return;
        }

        const averageRating = averageRatingResults[0].averageRating;
        if(autista){
          ws.send(JSON.stringify({
            type: 'autistaRVDetails',
            status:'success',
            autista: autista,
            reviews: reviews,
            averageRating: averageRating
          }));
        }
        else{
          ws.send(JSON.stringify({
            type: 'autistaRVDetails',
            status:'noDrivers',
          }));
        }
        
      });
    });
  });


}

function getUserReviewDetails(userId, ws){
  const query = 'SELECT * FROM utenti WHERE id_utente =?';
  db.execute(query, [userId], (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    const user = results[0];
    const reviewQuery = 'SELECT * FROM feedback_utenti_autisti WHERE id_utente =?';
    db.execute(reviewQuery, [userId], (err, reviewResults) => {
      if (err) {
        console.error('Database connection error:', err);
        ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
        return;
      }

      const reviews = reviewResults;
      const averageRatingQuery = 'SELECT AVG(voto) as averageRating FROM feedback_utenti_autisti WHERE id_utente =?';
      db.execute(averageRatingQuery, [userId], (err, averageRatingResults) => {
        if (err) {
          console.error('Database connection error:', err);
          ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
          return;
        }

        const averageRating = averageRatingResults[0].averageRating;
        ws.send(JSON.stringify({
          type: 'userRVDetails',
          status:'success',
          user: user,
          reviews: reviews,
          averageRating: averageRating
        }));
      });
    });
  });
}



const handleAcceptApplication = (applicationId, tripId, ws) => {
    const query = 'SELECT * FROM applicazioni WHERE id =?';
    db.execute(query, [applicationId], (err, results) => {
      if (err) {
        console.error('Database connection error:', err);
        ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
        return;
      }

    const application = results[0];

    const query2 = 'SELECT v.*, cp.nome_citta AS partenza, cd.nome_citta AS destinazione FROM viaggi v INNER JOIN citta cp ON v.id_citta_partenza = cp.id_citta INNER JOIN citta cd ON v.id_citta_destinazione = cd.id_citta WHERE v.id_viaggio =?';
    db.execute(query2, [tripId], (err, results2) => {
      if (err) {
        console.error('Database connection error:', err);
        ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
        return;
      }

    const trip = results2[0];
    console.log(trip)

    if (trip.posti_disponibili - trip.posti_occupati >= application.n_passeggeri) {
      //if all seats are taken close application
      if (trip.posti_disponibili - application.n_passeggeri === 0) {
        const query4 = 'UPDATE viaggi SET applicazione_aperte =? WHERE id_viaggio =?';
        db.execute(query4, [false, tripId], (err, results4) => {
          if (err) {
            console.error('Database connection error:', err);
            ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
            return;
          }
        });
        handleCloseApplications(tripId, ws)
      }

        const query3 = 'UPDATE viaggi SET posti_occupati = posti_occupati +? WHERE id_viaggio =?';
        db.execute(query3, [application.n_passeggeri, tripId], (err, results3) => {
          if (err) {
            console.error('Database connection error:', err);
            ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
            return;
          }
        });


        const query4 = 'UPDATE applicazioni SET stato =? WHERE id =?';
          db.execute(query4, ['accepted', applicationId], (err, results4) => {
            if (err) {
              console.error('Database connection error:', err);
              ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
              return;
            }

            
          });
          


          // Insert a new notification into the notifiche table
          const notificationQuery = 'INSERT INTO notifiche (id_utente, messaggio, stato) VALUES (?,?,?)';
          db.execute(notificationQuery, [application.id_utente, `Your application for the trip from ${trip.partenza} to ${trip.destinazione} for ${application.n_passeggeri} for has been accepted`, 'unread'], (err, results5) => {
            if (err) {
              console.error('Database connection error:', err);
              ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
              return;
            }

            ws.send(JSON.stringify({ status:'success', message: 'Application accepted' }));
          });
      } else {
        ws.send(JSON.stringify({ status: 'failure', message: 'Not enough seats available' }));
      }
    });
  });
};

const handleCloseApplications = (tripId, ws) => {
  const query = 'UPDATE viaggi SET applicazione_aperte =? WHERE id_viaggio =?';
  db.execute(query, [false, tripId], (err, results) => {
    if (err) { 
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    ws.send(JSON.stringify({ status:'success', message: 'Applications closed' }));
  });
};



const getNotifications = (userId, ws) => {
  console.log(userId)
  const query = 'SELECT * FROM notifiche WHERE id_utente =?';
  db.execute(query, [userId], (err, results) => {
    if (err) {
      console.error('Database connection error:', err);
      ws.send(JSON.stringify({ status: 'failure', message: 'Error connecting to database' }));
      return;
    }

    const notifications = results;
    ws.send(JSON.stringify({ type: 'notifications', notifications }));
  });
};