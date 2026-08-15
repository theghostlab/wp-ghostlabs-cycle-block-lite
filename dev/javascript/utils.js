const debounce = (func, timeout = 300) => {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
}

const htmlEnc = s => s.replace(/&/g, '&amp;')
.replace(/</g, '&lt;')
.replace(/>/g, '&gt;')
.replace(/'/g, '&#39;')
.replace(/"/g, '&#34;');

const truncateString = (str, num) => {

    if (str.length <= num) {
        return str
    }

    return str.slice(0, num) + '...';
}

const slugMask = input => input
    .toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
    .replace(/-{2,}/g, '-')
    .trim();

const slugMaskBlur = input => input
    .toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
    .replace(/-{2,}/g, '-')
    .replace(/^-+|-+$/g, '') // Remove leading and trailing hyphens
    .trim();

const formatDateTime = date => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');  // Months are 0-11, hence +1
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day}-${hours}-${minutes}-${seconds}`;
}

const memoize = (fn, callback) => {
    let cache = {};
    return (...args) => {
        let n = args[0];  // just taking one argument here
        if (n in cache) {

            if( typeof callback === 'function') {
                callback(cache[n])
            }

            return cache[n];

        } else {

            let result = fn(args);
            cache[n] = result;

            if( typeof callback === 'function') {
                callback(result);
            }

            return result;
        }
    }
}

export {
    debounce,
    formatDateTime,
    htmlEnc,
    memoize,
    slugMask,
    slugMaskBlur,
    truncateString,
}