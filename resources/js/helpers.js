export function pick(object, properties) {
    return properties.reduce((pickedObject, property) => {
        pickedObject[property] = object[property];
        return pickedObject;
    }, {});
}

export function get(object, path) {
    if (! path) {
        return object;
    }

    if (object === null || typeof object !== 'object') {
        return object;
    }

    const [pathHead, pathTail] = path.split(/\.(.+)/);

    return get(object[pathHead], pathTail);
}
