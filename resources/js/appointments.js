import { ref, onValue } from "firebase/database";
import { db } from "./firebase";

const appointmentsRef = ref(db, "appointments");

onValue(appointmentsRef, (snapshot) => {
  console.log("Firebase Data:", snapshot.val());
});
