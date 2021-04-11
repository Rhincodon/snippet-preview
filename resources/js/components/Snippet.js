import React from 'react';

function Snippet(props) {

    const robots_allowed = props.snippetData.robots_allowed;

    if (robots_allowed !== undefined && !robots_allowed) {
        return (
            <div>This site does not allow parsing of its pages</div>
        );
    } else if (robots_allowed) {
        return (
            <div className="mt-4">
                <img className="snippet-image" src={props.snippetData.image_url} />
                <div className="snippet-text p-2">
                    <div>{props.snippetData.title}</div>
                    <div>{props.snippetData.description}</div>
                </div>
            </div>
        );
    } else {
        return false;
    }

}

export default Snippet;
