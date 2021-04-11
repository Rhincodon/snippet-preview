import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import Form from "./Form";
import Snippet from "./Snippet";
import UserLinks from "./UserLinks";

function App() {

    const GET_USER_LINKS_PATH = '/user/links';

    const [snippetData, setSnippetData] = useState({});
    const [links, setLinks] = useState([]);

    useEffect(() => {

        const isGuest = $('input[name="is-guest"]').val() === 'true';
        if (isGuest) {
            return;
        }

        fetch(GET_USER_LINKS_PATH).then(res => res.json()).then(data => {
            setLinks(data);
        });
    }, [snippetData]);

    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-6">
                    <Form setSnippetData={setSnippetData} />
                    <Snippet snippetData={snippetData} />
                    <UserLinks links={links} />
                </div>
            </div>
        </div>
    );
}

export default App;

if (document.getElementById('react-app')) {
    ReactDOM.render(<App />, document.getElementById('react-app'));
}
