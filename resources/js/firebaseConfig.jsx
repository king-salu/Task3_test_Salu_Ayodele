import { initializeApp } from "firebase/app";
import { getAuth, TwitterAuthProvider } from 'firebase/auth';

const firebaseConfig = {
    apiKey: process.env.FIREBASE_API_KEY,
    authDomain: process.env.FIREBASE_AUTH_DOMAIN,
    projectId: process.env.FIREBASE_PROJ_ID,
    storageBucket: process.env.FIREBASE_STORAGE_BUCKET,
    messagingSenderId: process.env.FIREBASE_MSG_SENDERID,
    appId: process.env.FIREBASE_APP_ID,
};

console.log(process.env, firebaseConfig);

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new TwitterAuthProvider();
export { auth, provider }