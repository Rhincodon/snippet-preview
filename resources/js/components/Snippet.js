import React from 'react';

function Snippet(props) {

    const data = props.snippetData;

    const isAllFieldsEmpty = obj => {
        return obj.image_url == null && obj.title == null && obj.description == null;
    };

    if ($.isEmptyObject(data)) {
        return false;
    }

    let error = null;

    if (!data.robots_allowed) {
        error = 'This site does not allow parsing of its pages';
    } else if (isAllFieldsEmpty(data)) {
        error = 'There is no data for a snippet';
    }

    if (error) {
        return (
            <div className="mt-4">{error}</div>
        );
    }

    return (
        <div className="mt-4">
            <img className="snippet-image" src={data.image_url} />
            <div className="snippet-text p-2">
                <div>{data.title}</div>
                <div>{data.description}</div>
            </div>
        </div>
    );

}

export default Snippet;
