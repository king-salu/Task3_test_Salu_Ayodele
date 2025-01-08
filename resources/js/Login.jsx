import React from "react";
import TwitterLogin from "react-twitter-auth";

const Login = () => {
    const onSuccess = (response) => {
        response.json().then(body => {
            const token = body.oauth_token;
            console.log('User token:', token);
        });
    };
    const onFailed = (error) => {
        console.log('Login failed:', error);
    };

    return (<div>
        <h2>Login with Twitter</h2>
        <TwitterLogin loginUrl="http://localhost:8000/v1/auth/twitter"
            onFailure={onFailed} onSuccess={onSuccess}
            requestTokenUrl="http://localhost:8000/v1/auth/twitter/reverse"
            consumerKey="A8jjWZ09XlfZWDvtV4g1Ct7tP"
            consumerSecret="OEdhBlHDjNSx8bTcQhRmSSlWNVmNXZZd703jwi236yjkRR0Waj"
        />
    </div>);
};


export default Login;