import React from 'react';
import ReactDOM from 'react-dom';
import Form from "./Form";

function App() {
    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-8">
                    <Form />
                </div>
            </div>
        </div>
    );
}

export default App;

if (document.getElementById('react-app')) {
    ReactDOM.render(<App />, document.getElementById('react-app'));
}
