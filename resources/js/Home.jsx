import React, { useState } from 'react';
const Home = () => {
    const [recipient, setRecipient] = useState('');
    const [message, setMessage] = useState('');
    const handleSubmit = (e) => {
        e.preventDefault();
        console.log('Recipient:', recipient);
        console.log('Message:', message);
        // Add your logic to handle the form submission 
    };
    return (
        <div style={{ padding: '20px', maxWidth: '600px', margin: '0 auto' }}>
            <h2>Dashboard</h2>
            <form onSubmit={handleSubmit}>
                <div style={{ marginBottom: '10px' }}>
                    <label htmlFor="recipient">Recipient:</label>
                    <input type="text" id="recipient" value={recipient} onChange={(e) => setRecipient(e.target.value)} style={{ width: '100%', padding: '8px', marginTop: '5px' }} />
                </div>
                <div style={{ marginBottom: '10px' }}>
                    <label htmlFor="message">Message:</label>
                    <textarea id="message" value={message} onChange={(e) => setMessage(e.target.value)} style={{ width: '100%', padding: '8px', marginTop: '5px', height: '150px' }} />
                </div>
                <button type="submit" style={{ padding: '10px 20px' }}>Send</button>
            </form>
        </div>);
};

export default Home;