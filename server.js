const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 8080 });

let lobbies = {}; // Ogni lobby è una chiave con un array di giocatori e stato del gioco

wss.on('connection', (ws) => {
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);

            switch (data.type) {
                case 'create_lobby':
                    
                    break;
                
                case 'join_lobby':
                    if (lobbies[data.lobby] && lobbies[data.lobby].players.length < 2) {
                        ws.playerSymbol = 'O';
                        lobbies[data.lobby].players.push(ws);
                        ws.lobby = data.lobby;
                        ws.send(JSON.stringify({ 
                            type: 'lobby_joined', 
                            lobby: data.lobby, 
                            symbol: 'O', 
                            board: lobbies[data.lobby].board, 
                            turn: lobbies[data.lobby].turn
                        }));
                        
                        // Notifica il primo giocatore che l'avversario si è unito
                        lobbies[data.lobby].players[0].send(JSON.stringify({ type: 'player_joined' }));
                    } else {
                        ws.send(JSON.stringify({ type: 'error', message: 'Lobby is full or does not exist' }));
                    }
                    break;

                case 'move':
                    
                    break;

                case 'show_lobby':
                    
                    break;
            }
        } catch (error) {
            ws.send(JSON.stringify({ type: 'error', message: 'Invalid JSON' }));
        }
    });

    ws.on('close', () => {
        
    });
});

console.log('WebSocket server started on ws://localhost:8080');