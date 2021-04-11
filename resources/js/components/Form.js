import React from 'react';
import { useState, useRef } from "react";

function Form(props) {

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

        fetch('/link/submit', options)
            .then(res => res.json())
            .then(data => {
                if (data.errors) {
                    setErrorMessages(data.errors);
                } else if (data.snippet) {
                    setErrorMessages({});
                    props.setSnippetData(data.snippet);
                }
            });

        event.preventDefault();
    };

    return (
        <form ref={form} onSubmit={onSubmit}>
            <div>{errorMessages.url || errorMessages.limit}</div>
            <input type="text" name="url" maxLength={255} />
            <input type="submit" value="Preview" />
        </form>
    );
}

export default Form;
