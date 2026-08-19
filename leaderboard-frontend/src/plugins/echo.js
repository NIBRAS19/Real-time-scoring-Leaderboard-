import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

// Enable detailed logging for debugging
Pusher.logToConsole = true;

const config = {
  broadcaster: "pusher",
  key: "f73zpiqru38gpb1lkpvj", // Your actual Reverb app key
  wsHost: "localhost",
  wsPort: 8080,
  wssPort: 8080,
  forceTLS: false,
  disableStats: true,
  enabledTransports: ["ws"],
  cluster: "mt1",
};

console.log("🔧 Initializing Echo with config:", config);

const echo = new Echo(config);

// Connection event listeners
echo.connector.pusher.connection.bind("state_change", (states) => {
  console.log(`🔄 WebSocket: ${states.previous} → ${states.current}`);
});

echo.connector.pusher.connection.bind("connected", () => {
  console.log("✅ WebSocket Connected! Socket ID:", echo.socketId());
});

echo.connector.pusher.connection.bind("error", (err) => {
  console.error("❌ WebSocket Error:", err);
});

echo.connector.pusher.connection.bind("disconnected", () => {
  console.warn("⚠️ WebSocket Disconnected");
});

export default echo;
