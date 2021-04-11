import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import Form from "./Form";
import Snippet from "./Snippet";

function App() {

    const [snippetData, setSnippetData] = useState({});

    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-6">
                    <Form setSnippetData={setSnippetData} />
                    <Snippet snippetData={snippetData} />
                </div>
            </div>
        </div>
    );
}

export default App;

if (document.getElementById('react-app')) {
    ReactDOM.render(<App />, document.getElementById('react-app'));
}
