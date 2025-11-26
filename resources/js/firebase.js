// resources/js/firebase.js
import { initializeApp } from "firebase/app";
import { getDatabase } from "firebase/database";

// Paste your Firebase config from the console
const firebaseConfig = {
  apiKey: "AIzaSyCg8HRgVgbRZgCP3lW7davk5YKU_hvo6yE",
  authDomain: "appointment-system-b9648.firebaseapp.com",
  databaseURL: " https://appointment-system-b9648-default-rtdb.asia-southeast1.firebasedatabase.app/",
  projectId: "appointment-system-b9648",
  storageBucket: "appointment-system-b9648.firebasestorage.app",
  messagingSenderId: "455658576755",
  appId: "1:455658576755:web:dff28be4f858fea7cc4306"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Export database so you can use it elsewhere
export const db = getDatabase(app);
