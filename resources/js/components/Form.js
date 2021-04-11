import React from 'react';
import { useState, useRef } from "react";

function Form(props) {

    const SUBMIT_LINK_PATH = '/link/submit';

    const [errorMessages, setErrorMessages] = useState({});
    const form = useRef(null);

    const onSubmit = event => {
        event.preventDefault();

        const options = {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            body: new FormData(form.current),
        };

        fetch(SUBMIT_LINK_PATH, options)
            .then(res => res.json())
            .then(data => {
                if (data.errors) {
                    setErrorMessages(data.errors);
                } else if (data.snippet) {
                    setErrorMessages({});
                    props.setSnippetData(data.snippet);
                }
            });
    };

    return (
        <form className="d-flex flex-column" ref={form} onSubmit={onSubmit}>
            <div>{errorMessages.url || errorMessages.limit}</div>
            <div className="form-group">
                <input className="form-control" type="url" name="url" required maxLength={255}
                       placeholder="Enter your link here"
                       onFocus={(e) => e.target.placeholder = ''}
                       onBlur={(e) => e.target.placeholder = 'Enter your link here'}
                />
            </div>
            <button className="btn btn-primary form__submit mx-auto" type="submit">Preview</button>
        </form>
    );
}

export default Form;
