import React from "react";
import { auth, provider } from "./firebaseConfig";
import { signInWithPopup } from "firebase/auth";
import swal from 'sweetalert';

const Login = () => {
    const onSuccess = (response) => {
        console.log('User token:', response);
        swal('Login Successful', 'Authentication Successful', 'success');
        window.setTimeout(() => {
            location.href = '/home';
        });
    };
    const onFailed = (error) => {
        console.log(error);
        // swal('Login failed', error, 'error');
    };
    const handleLogin = () => {
        signInWithPopup(auth, provider)
            .then(onSuccess)
            .catch(onFailed);
    };

    return (
        <div> <h2>Login with Twitter</h2>
            <button onClick={handleLogin}>Sign in with Twitter</button>
        </div>
    );
};


export default Login;