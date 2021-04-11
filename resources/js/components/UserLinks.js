import React from 'react';

function UserLinks(props) {

    if (!props.links || props.links.length === 0) {
        return false;
    }

    return (
        <div className="mt-4">
            <h3 className="text-center">Submitted Links</h3>
            <ul className="list-group">
                { props.links.map((item) => <li className="list-group-item" key={item.id}>
                    <a href={item.url} target="_blank">{item.url}</a>
                </li>) }
            </ul>
        </div>
    );

}

export default UserLinks;
