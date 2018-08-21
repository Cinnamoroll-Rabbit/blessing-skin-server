import Vue from 'vue';
import { queryStringify } from './utils';
import { showAjaxError } from './notify';

const csrfField = document.querySelector('meta[name="csrf-token"]');

const empty = Object.create(null);
/** @type Request */
export const init = {
    credentials: 'same-origin',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfField && csrfField.content
    }
};

export async function walkFetch(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            return response.json();
        } else {
            showAjaxError(await response.text());
        }
    } catch (error) {
        showAjaxError(error);
    }
}

export async function get(url, params = empty) {
    const qs = queryStringify(params);

    return walkFetch(new Request(`${blessing.base_url}${url}${qs && '?' + qs}`, init));
}

export async function post(url, data = empty) {
    return walkFetch(new Request(`${blessing.base_url}${url}`, {
        body: JSON.stringify(data),
        method: 'POST',
        ...init
    }));
}

Vue.use(_Vue => {
    _Vue.prototype.$http = {
        get,
        post,
    };
});