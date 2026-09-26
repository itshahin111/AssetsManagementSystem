import http from './http';

const list = (resource, params = {}) => http.get(`/${resource}`, { params }).then((response) => response.data);
const create = (resource, payload) => http.post(`/${resource}`, payload).then((response) => response.data);
const update = (resource, id, payload) => http.put(`/${resource}/${id}`, payload).then((response) => response.data);
const remove = (resource, id) => http.delete(`/${resource}/${id}`).then((response) => response.data);

export const assetsApi = {
    list: (params) => list('assets', params),
    create,
    update,
    remove,
};
